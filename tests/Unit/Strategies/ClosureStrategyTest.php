<?php

declare(strict_types=1);

use Filament\Actions\Action;
use Filament\Actions\ForceDeleteAction;
use HoceineEl\UndoToast\Enums\UndoableOperation;
use HoceineEl\UndoToast\Strategies\ClosureStrategy;
use HoceineEl\UndoToast\Support\Snapshot;
use HoceineEl\UndoToast\Tests\Fixtures\Customer;

it('runs the undoable closure for each record after a round trip through serialisation', function (): void {
    $record = customer();
    $record->archive();
    $strategy = new ClosureStrategy;
    $action = Action::make('archive')->label('Archive')->record($record)
        ->undoable(fn (Customer $record) => $record->unarchive());

    expect($strategy->supports($action))->toBeTrue()
        ->and($strategy->supports(Action::make('plain')->record($record)))->toBeFalse();

    $snapshot = unserialize(serialize($strategy->snapshot($action)));

    expect($snapshot)->toBeInstanceOf(Snapshot::class)
        ->and($snapshot->operation)->toBe(UndoableOperation::Custom)
        ->and($strategy->describe($snapshot))->toBe('Archive: customer');

    $strategy->undo($snapshot);

    expect($record->fresh()->archived_at)->toBeNull();
});

it('uses a custom message when given', function (): void {
    $strategy = new ClosureStrategy;
    $action = Action::make('archive')->record(customer())
        ->undoable(fn (Customer $record) => $record->unarchive(), 'Customer archived');

    expect($strategy->describe($strategy->snapshot($action)))->toBe('Customer archived');
});

it('never offers undo for force deletes', function (): void {
    $action = ForceDeleteAction::make()->record(customer())->undoable(fn () => null);

    expect((new ClosureStrategy)->supports($action))->toBeFalse();
});
