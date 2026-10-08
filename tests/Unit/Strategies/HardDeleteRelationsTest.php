<?php

declare(strict_types=1);

use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use HoceineEl\UndoToast\Livewire\UndoToast;
use HoceineEl\UndoToast\Strategies\HardDeleteStrategy;
use HoceineEl\UndoToast\Support\Snapshot;
use HoceineEl\UndoToast\Tests\Fixtures\Comment;
use HoceineEl\UndoToast\Tests\Fixtures\Project;
use HoceineEl\UndoToast\Tests\Fixtures\ProjectTable;
use HoceineEl\UndoToast\Tests\Fixtures\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

function projectWithRelations(string $name = 'Apollo'): Project
{
    $project = Project::create(['name' => $name]);
    $project->tags()->attach(tag("{$name} urgent"), ['role' => 'owner']);
    $project->tags()->attach(tag("{$name} later"), ['role' => 'viewer']);
    $project->tasks()->createMany([['title' => 'Plan', 'done' => true], ['title' => 'Launch']]);
    $project->comments()->create(['body' => 'Looks good']);

    return $project;
}

/**
 * @return array<string, list<array<string, mixed>>>
 */
function relationRows(Project $project): array
{
    return [
        'project_tag' => DB::table('project_tag')->where('project_id', $project->id)->orderBy('tag_id')->get()->map(fn (object $row): array => (array) $row)->all(),
        'tasks' => DB::table('tasks')->where('project_id', $project->id)->orderBy('id')->get()->map(fn (object $row): array => (array) $row)->all(),
        'comments' => DB::table('comments')->where('commentable_id', $project->id)->orderBy('id')->get()->map(fn (object $row): array => (array) $row)->all(),
    ];
}

function hardDelete(Project ...$projects): Snapshot
{
    $strategy = new HardDeleteStrategy;
    $snapshot = $strategy->snapshot(DeleteAction::make()->record($projects[0]));

    foreach ($projects as $project) {
        $project->comments()->delete();
        $project->delete();
    }

    return $strategy->confirm($snapshot);
}

it('restores belongs to many pivot rows with their pivot attributes', function (): void {
    $project = projectWithRelations();
    $before = relationRows($project)['project_tag'];

    $snapshot = hardDelete($project);

    expect(DB::table('project_tag')->count())->toBe(0);

    (new HardDeleteStrategy)->undo($snapshot);

    expect(relationRows($project)['project_tag'])->toBe($before)
        ->and(Project::find($project->id)->tags()->pluck('role')->sort()->values()->all())->toBe(['owner', 'viewer']);
});

it('restores has many children with their original keys and columns', function (): void {
    $project = projectWithRelations();
    $before = relationRows($project)['tasks'];

    (new HardDeleteStrategy)->undo(hardDelete($project));

    expect(relationRows($project)['tasks'])->toBe($before);
});

it('restores morph many children configured on the plugin', function (): void {
    plugin()->restoreRelations([Project::class => ['comments']]);
    $project = projectWithRelations();
    $before = relationRows($project)['comments'];

    $snapshot = hardDelete($project);

    expect(Comment::count())->toBe(0);

    (new HardDeleteStrategy)->undo($snapshot);

    expect(relationRows($project)['comments'])->toBe($before)
        ->and(Project::find($project->id)->comments()->first()->body)->toBe('Looks good');
});

it('leaves relations alone unless they are opted in', function (): void {
    $project = projectWithRelations();

    (new HardDeleteStrategy)->undo(hardDelete($project));

    expect(Comment::count())->toBe(0);
});

it('never overwrites rows that exist again and skips links to records that are gone', function (): void {
    $project = projectWithRelations();
    [$plan, $launch] = $project->tasks()->orderBy('id')->get()->all();
    [$urgent, $later] = $project->tags()->orderBy('tags.id')->get()->all();

    $snapshot = hardDelete($project);
    $other = Project::create(['name' => 'Other']);
    DB::table('tasks')->insert(['id' => $plan->id, 'project_id' => $other->id, 'title' => 'Squatter', 'done' => false]);
    $later->delete();

    (new HardDeleteStrategy)->undo($snapshot);

    expect(Task::find($plan->id)->only(['project_id', 'title']))->toBe(['project_id' => $other->id, 'title' => 'Squatter'])
        ->and(Task::find($launch->id)->project_id)->toBe($project->id)
        ->and(Project::find($project->id)->tags()->pluck('tags.id')->all())->toBe([$urgent->id]);
});

it('does not duplicate pivot rows that were not removed', function (): void {
    $project = projectWithRelations();
    $strategy = new HardDeleteStrategy;
    $snapshot = $strategy->snapshot(DeleteAction::make()->record($project));

    Schema::withoutForeignKeyConstraints(fn () => DB::table('projects')->delete($project->id));

    $strategy->undo($strategy->confirm($snapshot));

    expect(DB::table('project_tag')->where('project_id', $project->id)->count())->toBe(2);
});

it('keeps only the relations of parents that were really deleted', function (): void {
    $gone = projectWithRelations('Gone');
    $kept = projectWithRelations('Kept');
    $strategy = new HardDeleteStrategy;
    $snapshot = $strategy->snapshot(DeleteAction::make()->record($gone));
    $snapshot = $snapshot->with([$gone->id, $kept->id], [
        ...$snapshot->data,
        'relations' => $snapshot->data['relations'] + [$kept->id => ['tasks' => []]],
    ]);

    $gone->delete();

    expect(array_keys($strategy->confirm($snapshot)->data['relations']))->toBe([$gone->id]);
});

it('refuses relations it cannot restore', function (): void {
    plugin()->restoreRelations([Project::class => ['owner']]);

    (new HardDeleteStrategy)->snapshot(DeleteAction::make()->record(Project::create(['name' => 'Apollo'])));
})->throws(InvalidArgumentException::class, 'BelongsTo');

it('brings a bulk hard delete back with its relations through the toast', function (): void {
    $projects = [projectWithRelations('One'), projectWithRelations('Two')];
    $before = array_map(relationRows(...), $projects);

    $component = Livewire::test(ProjectTable::class)
        ->selectTableRecords(array_map(fn (Project $project): int => $project->id, $projects))
        ->callAction(TestAction::make('delete')->table()->bulk());

    expect(Project::count())->toBe(0)
        ->and(Task::count())->toBe(0);

    expect(app(UndoToast::class)->undo(pushedToasts($component)[0]['token'])['ok'])->toBeTrue();

    foreach ($projects as $index => $project) {
        expect(array_slice(relationRows($project), 0, 2))->toBe(array_slice($before[$index], 0, 2));
    }
});
