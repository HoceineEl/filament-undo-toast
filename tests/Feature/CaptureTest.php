<?php

declare(strict_types=1);

use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use HoceineEl\UndoToast\Enums\UndoableOperation;
use HoceineEl\UndoToast\Livewire\UndoToast;
use HoceineEl\UndoToast\Support\UndoStore;
use HoceineEl\UndoToast\Tests\Fixtures\ArchiveStrategy;
use HoceineEl\UndoToast\Tests\Fixtures\Customer;
use HoceineEl\UndoToast\Tests\Fixtures\CustomerPage;
use HoceineEl\UndoToast\Tests\Fixtures\CustomerResource;
use HoceineEl\UndoToast\Tests\Fixtures\CustomerTable;
use HoceineEl\UndoToast\Tests\Fixtures\Project;
use HoceineEl\UndoToast\Tests\Fixtures\ProjectTagsTable;
use HoceineEl\UndoToast\Tests\Fixtures\Tag;
use HoceineEl\UndoToast\Tests\Fixtures\TagTable;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

it('creates an entry and pushes a toast after a delete', function (): void {
    $record = customer();

    $component = Livewire::test(CustomerTable::class)
        ->callAction(TestAction::make('delete')->table($record))
        ->assertDispatched('undo-toast-push', function (string $name, array $params): bool {
            return strlen($params['token']) === 40
                && $params['message'] === 'Customer deleted'
                && $params['operation'] === 'delete'
                && $params['seconds'] === 8;
        });

    $entry = pushedEntry($component);

    expect($record->fresh()->trashed())->toBeTrue()
        ->and($entry->snapshot->keys)->toBe([$record->id])
        ->and($entry->userId)->toBe((string) auth()->id())
        ->and($entry->panelId)->toBe('admin')
        ->and($entry->snapshot->operation)->toBe(UndoableOperation::Delete);
});

it('describes bulk deletes with the count', function (): void {
    $records = collect([customer('A'), customer('B'), customer('C')]);

    $component = Livewire::test(CustomerTable::class)
        ->selectTableRecords($records->pluck('id')->all())
        ->callAction(TestAction::make('delete')->table()->bulk());

    expect(pushedToasts($component)[0]['message'])->toBe('Deleted 3 customers')
        ->and(pushedEntry($component)->snapshot->keys)->toEqualCanonicalizing($records->pluck('id')->all());
});

it('only keeps the records a partially failed bulk action affected', function (): void {
    $gone = customer('Gone');
    $kept = customer('Keep me');

    $component = Livewire::test(CustomerTable::class)
        ->selectTableRecords([$gone->id, $kept->id])
        ->callAction(TestAction::make('deletePartial')->table()->bulk());

    expect(pushedEntry($component)->snapshot->keys)->toBe([$gone->id])
        ->and(pushedToasts($component)[0]['message'])->toBe('Customer deleted');
});

it('pushes undo for hard deletes, restores, edits, detaches and custom actions', function (): void {
    $trashed = customer('Trashed');
    $trashed->delete();

    $operation = fn (Testable $component): UndoableOperation => pushedEntry($component)->snapshot->operation;

    expect($operation(Livewire::test(TagTable::class)->callAction(TestAction::make('delete')->table(tag()))))
        ->toBe(UndoableOperation::Delete)
        ->and($operation(Livewire::test(CustomerTable::class)->callAction(TestAction::make('restore')->table($trashed))))
        ->toBe(UndoableOperation::Restore)
        ->and($operation(Livewire::test(CustomerTable::class)->callAction(TestAction::make('edit')->table(customer('Edited')), data: ['name' => 'Changed'])))
        ->toBe(UndoableOperation::Edit)
        ->and($operation(Livewire::test(CustomerTable::class)->callAction(TestAction::make('archive')->table(customer('Archived')))))
        ->toBe(UndoableOperation::Custom);

    $project = Project::create(['name' => 'Apollo']);
    $project->tags()->attach(tag('Linked'), ['role' => 'owner']);

    expect($operation(Livewire::test(ProjectTagsTable::class, ['project' => $project])
        ->callAction(TestAction::make('detach')->table($project->tags()->first()))))
        ->toBe(UndoableOperation::Detach);
});

it('never creates an entry for force deletes', function (): void {
    $record = customer();
    $record->delete();

    Livewire::test(CustomerTable::class)
        ->callAction(TestAction::make('forceDelete')->table($record))
        ->assertNotDispatched('undo-toast-push');
});

it('creates nothing for halted or failed actions', function (string $name): void {
    Livewire::test(CustomerTable::class)
        ->callAction(TestAction::make($name)->table(customer()))
        ->assertNotDispatched('undo-toast-push');
})->with(['deleteHalted', 'deleteFailing']);

it('creates nothing for actions without changes or opted out', function (string $name): void {
    Livewire::test(CustomerTable::class)
        ->callAction(TestAction::make($name)->table(customer()))
        ->assertNotDispatched('undo-toast-push');
})->with(['rename', 'deleteNoUndo']);

it('keeps the user after hook working', function (): void {
    Livewire::test(CustomerTable::class)
        ->callAction(TestAction::make('deleteWithAfter')->table(customer()))
        ->assertDispatched('undo-toast-push');

    expect(CustomerTable::$afterRan)->toBeTrue();
});

it('limits undo to the configured operations', function (): void {
    plugin()->operations([UndoableOperation::Edit]);

    Livewire::test(CustomerTable::class)
        ->callAction(TestAction::make('delete')->table(customer()))
        ->assertNotDispatched('undo-toast-push');
});

it('skips excluded models', function (): void {
    plugin()->exceptModels([Customer::class]);

    Livewire::test(CustomerTable::class)
        ->callAction(TestAction::make('delete')->table(customer()))
        ->assertNotDispatched('undo-toast-push');
});

it('skips hard deletes when they are turned off', function (): void {
    plugin()->hardDeletes(false);

    Livewire::test(TagTable::class)
        ->callAction(TestAction::make('delete')->table(tag()))
        ->assertNotDispatched('undo-toast-push');
});

it('does nothing on panels without the plugin', function (): void {
    Filament::setCurrentPanel('other');

    Livewire::test(CustomerTable::class)
        ->callAction(TestAction::make('delete')->table(customer()))
        ->assertNotDispatched('undo-toast-push');
});

it('flashes the toast so it survives a redirect', function (): void {
    $record = customer();

    Livewire::test(CustomerPage::class, ['customerId' => $record->id])
        ->callAction('delete')
        ->assertRedirect('/admin/customers');

    expect(flashedToasts())->toHaveCount(1)
        ->and(app(UndoStore::class)->find(flashedToasts()[0]['token']))->not->toBeNull();
});

it('uses the configured duration', function (): void {
    plugin()->duration(15);

    Livewire::test(CustomerTable::class)
        ->callAction(TestAction::make('delete')->table(customer()))
        ->assertDispatched('undo-toast-push', fn (string $name, array $params): bool => $params['seconds'] === 15);
});

it('skips excluded resources', function (): void {
    plugin()->except([CustomerResource::class]);

    Livewire::test(CustomerTable::class)
        ->callAction(TestAction::make('delete')->table(customer()))
        ->assertNotDispatched('undo-toast-push');
});

it('uses custom strategies before the built in ones', function (): void {
    plugin()->strategy(ArchiveStrategy::class);
    $record = customer('Acme');

    $component = Livewire::test(CustomerTable::class)
        ->callAction(TestAction::make('rename')->table($record))
        ->assertDispatched('undo-toast-push', fn (string $name, array $params): bool => $params['message'] === 'Renamed');

    app(UndoToast::class)->undo(pushedToasts($component)[0]['token']);

    expect($record->fresh()->name)->toBe('Acme');
});

it('captures bulk restores', function (): void {
    $records = collect([customer('A'), customer('B')])->each->delete();

    $component = Livewire::test(CustomerTable::class)
        ->selectTableRecords($records->pluck('id')->all())
        ->callAction(TestAction::make('restore')->table()->bulk());

    expect(pushedToasts($component)[0]['message'])->toBe('Restored 2 customers');
});

it('round trips an edit through undo', function (): void {
    $record = customer('Acme', ['city' => 'Rabat']);

    $component = Livewire::test(CustomerTable::class)
        ->callAction(TestAction::make('edit')->table($record), data: ['name' => 'Acme Ltd', 'city' => 'Fes']);

    expect($record->fresh()->name)->toBe('Acme Ltd')
        ->and(app(UndoToast::class)->undo(pushedToasts($component)[0]['token'])['message'])->toBe('Changes to the customer undone')
        ->and($record->fresh()->only(['name', 'city']))->toBe(['name' => 'Acme', 'city' => 'Rabat']);
});

it('round trips a bulk hard delete through undo', function (): void {
    $tags = collect([tag('One', ['secret' => 'a']), tag('Two', ['secret' => 'b'])]);

    $component = Livewire::test(TagTable::class)
        ->selectTableRecords($tags->pluck('id')->all())
        ->callAction(TestAction::make('delete')->table()->bulk());

    expect(Tag::count())->toBe(0);

    app(UndoToast::class)->undo(pushedToasts($component)[0]['token']);

    expect(Tag::orderBy('id')->pluck('secret', 'id')->all())->toBe($tags->pluck('secret', 'id')->all());
});
