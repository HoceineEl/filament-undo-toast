<?php

declare(strict_types=1);

use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use HoceineEl\UndoToast\Enums\UndoableOperation;
use HoceineEl\UndoToast\Strategies\SoftDeleteStrategy;
use HoceineEl\UndoToast\Tests\Fixtures\Customer;

it('supports delete actions on soft deletable models only', function (): void {
    $strategy = new SoftDeleteStrategy;

    expect($strategy->supports(DeleteAction::make()->record(customer())))->toBeTrue()
        ->and($strategy->supports(DeleteAction::make()->record(tag())))->toBeFalse()
        ->and($strategy->supports(ForceDeleteAction::make()->record(customer())))->toBeFalse();
});

it('snapshots keys and restores them', function (): void {
    $record = customer();
    $strategy = new SoftDeleteStrategy;

    $snapshot = $strategy->snapshot(DeleteAction::make()->record($record));
    $record->delete();
    $snapshot = $strategy->confirm($snapshot);

    expect($snapshot->operation)->toBe(UndoableOperation::Delete)
        ->and($snapshot->keys)->toBe([$record->getKey()])
        ->and($strategy->describe($snapshot))->toBe('Customer deleted');

    $strategy->undo($snapshot);

    expect(Customer::find($record->getKey()))->not->toBeNull();
});

it('confirms nothing when the record was not deleted', function (): void {
    $strategy = new SoftDeleteStrategy;
    $snapshot = $strategy->snapshot(DeleteAction::make()->record(customer()));

    expect($strategy->confirm($snapshot))->toBeNull();
});
