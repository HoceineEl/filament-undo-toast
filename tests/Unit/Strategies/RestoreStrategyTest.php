<?php

declare(strict_types=1);

use Filament\Actions\RestoreAction;
use HoceineEl\UndoToast\Enums\UndoableOperation;
use HoceineEl\UndoToast\Strategies\RestoreStrategy;
use HoceineEl\UndoToast\Tests\Fixtures\Customer;

it('soft deletes restored records again', function (): void {
    $record = customer();
    $record->delete();
    $strategy = new RestoreStrategy;
    $action = RestoreAction::make()->record($record);

    expect($strategy->supports($action))->toBeTrue();

    $snapshot = $strategy->snapshot($action);
    $record->restore();
    $snapshot = $strategy->confirm($snapshot);

    expect($snapshot->operation)->toBe(UndoableOperation::Restore);

    $strategy->undo($snapshot);

    expect(Customer::withTrashed()->find($record->id)->trashed())->toBeTrue();
});
