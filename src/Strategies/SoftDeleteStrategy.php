<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Strategies;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use HoceineEl\UndoToast\Contracts\ConfirmsOutcome;
use HoceineEl\UndoToast\Contracts\UndoStrategy;
use HoceineEl\UndoToast\Enums\UndoableOperation;
use HoceineEl\UndoToast\Enums\UndoFailure;
use HoceineEl\UndoToast\Strategies\Concerns\InteractsWithActionRecords;
use HoceineEl\UndoToast\Support\Snapshot;
use HoceineEl\UndoToast\Support\UndoFailed;
use Illuminate\Database\Eloquent\Model;

class SoftDeleteStrategy implements ConfirmsOutcome, UndoStrategy
{
    use InteractsWithActionRecords;

    public function supports(Action $action): bool
    {
        return ($action instanceof DeleteAction || $action instanceof DeleteBulkAction)
            && $this->usesSoftDeletes($this->modelOf($action));
    }

    public function snapshot(Action $action): ?Snapshot
    {
        $records = $this->records($action)->reject(fn (Model $record): bool => (bool) $record->getAttribute($this->deletedAtColumn($record)));

        return $this->makeSnapshot($action, UndoableOperation::Delete, $records);
    }

    public function confirm(Snapshot $snapshot): ?Snapshot
    {
        $keys = $this->keysIn($this->trashedQuery($snapshot, true));

        return $keys ? $snapshot->with($keys) : null;
    }

    public function undo(Snapshot $snapshot): void
    {
        $records = $this->trashedQuery($snapshot, true)->get();

        throw_if($records->isEmpty(), UndoFailed::because(UndoFailure::Missing));

        $records->each(fn (Model $record): mixed => method_exists($record, 'restore') ? $record->restore() : null);
    }
}
