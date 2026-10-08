<?php

declare(strict_types=1);

use Filament\Actions\Action;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use HoceineEl\UndoToast\Enums\UndoableOperation;
use HoceineEl\UndoToast\Tests\Fixtures\CustomerTable;
use Livewire\Livewire;

it('replaces Filament success notification with the toast', function (): void {
    Livewire::test(CustomerTable::class)
        ->callAction(TestAction::make('delete')->table(customer()))
        ->assertDispatched('undo-toast-push')
        ->assertNotNotified('Deleted');
});

it('replaces the notification of bulk actions too', function (): void {
    $records = collect([customer('A'), customer('B')]);

    Livewire::test(CustomerTable::class)
        ->selectTableRecords($records->pluck('id')->all())
        ->callAction(TestAction::make('delete')->table()->bulk())
        ->assertDispatched('undo-toast-push')
        ->assertNotNotified('Deleted');
});

it('keeps Filament notification when replacement is turned off', function (): void {
    plugin()->replaceSuccessNotification(false);

    Livewire::test(CustomerTable::class)
        ->callAction(TestAction::make('delete')->table(customer()))
        ->assertDispatched('undo-toast-push')
        ->assertNotified('Deleted');
});

it('evaluates the replacement condition per action', function (): void {
    plugin()->replaceSuccessNotification(fn (Action $action): bool => $action->getName() !== 'delete');

    Livewire::test(CustomerTable::class)
        ->callAction(TestAction::make('delete')->table(customer()))
        ->assertNotified('Deleted');

    Livewire::test(CustomerTable::class)
        ->callAction(TestAction::make('deleteWithAfter')->table(customer('B')))
        ->assertNotNotified('Deleted');
});

it('respects notifications the developer customised', function (string $name, string $title): void {
    Livewire::test(CustomerTable::class)
        ->callAction(TestAction::make($name)->table(customer()))
        ->assertDispatched('undo-toast-push')
        ->assertNotified($title);
})->with([
    'custom title' => ['deleteCustomTitle', 'Customer archived for good'],
    'custom notification' => ['deleteCustomNotification', 'Gone for now'],
]);

it('keeps a notification the developer turned off turned off', function (): void {
    Livewire::test(CustomerTable::class)
        ->callAction(TestAction::make('deleteSilently')->table(customer()))
        ->assertDispatched('undo-toast-push')
        ->assertNotNotified();
});

it('keeps Filament notification when no undo entry is created', function (): void {
    $record = customer('Acme');

    Livewire::test(CustomerTable::class)
        ->callAction(TestAction::make('edit')->table($record), data: ['name' => 'Acme'])
        ->assertNotDispatched('undo-toast-push')
        ->assertNotified('Saved');
});

it('keeps Filament notification for actions without undo', function (callable $setUp, string $action): void {
    $setUp();

    Livewire::test(CustomerTable::class)
        ->callAction(TestAction::make($action)->table(customer()))
        ->assertNotDispatched('undo-toast-push')
        ->assertNotified('Deleted');
})->with([
    'opted out' => [fn () => null, 'deleteNoUndo'],
    'operation filtered' => [fn () => plugin()->operations([UndoableOperation::Edit]), 'delete'],
    'panel without plugin' => [fn () => Filament::setCurrentPanel('other'), 'delete'],
]);

it('keeps the failure notification of a partially failed bulk action', function (): void {
    $gone = customer('Gone');
    $kept = customer('Keep me');

    Livewire::test(CustomerTable::class)
        ->selectTableRecords([$gone->id, $kept->id])
        ->callAction(TestAction::make('deletePartial')->table()->bulk())
        ->assertDispatched('undo-toast-push')
        ->assertNotified();
});
