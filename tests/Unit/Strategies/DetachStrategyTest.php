<?php

declare(strict_types=1);

use HoceineEl\UndoToast\Strategies\DetachStrategy;
use HoceineEl\UndoToast\Tests\Fixtures\Project;
use HoceineEl\UndoToast\Tests\Fixtures\ProjectTagsTable;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

it('re-attaches detached records with their pivot data', function (): void {
    $project = Project::create(['name' => 'Apollo']);
    $urgent = tag('Urgent');
    $project->tags()->attach($urgent, ['role' => 'owner']);
    $pivotBefore = (array) DB::table('project_tag')->first();

    $component = Livewire::test(ProjectTagsTable::class, ['project' => $project])->instance();
    $action = $component->getTable()->getAction('detach')->record($project->tags()->first());
    $strategy = new DetachStrategy;

    expect($strategy->supports($action))->toBeTrue();

    $snapshot = $strategy->snapshot($action);
    $project->tags()->detach($urgent);
    $snapshot = $strategy->confirm($snapshot);

    expect($snapshot->keys)->toBe([$urgent->id]);

    $strategy->undo($snapshot);

    expect((array) DB::table('project_tag')->first())->toBe($pivotBefore);
});
