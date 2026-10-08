<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Strategies;

use Filament\Actions\Action;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use HoceineEl\UndoToast\Contracts\ConfirmsOutcome;
use HoceineEl\UndoToast\Contracts\UndoStrategy;
use HoceineEl\UndoToast\Enums\UndoableOperation;
use HoceineEl\UndoToast\Enums\UndoFailure;
use HoceineEl\UndoToast\Strategies\Concerns\InteractsWithActionRecords;
use HoceineEl\UndoToast\Support\Snapshot;
use HoceineEl\UndoToast\Support\UndoFailed;
use Illuminate\Database\Eloquent\Model;

class RestoreStrategy implements ConfirmsOutcome, UndoStrategy
{
    use InteractsWithActionRecords;

    public function supports(Action $action): bool
    {
        return ($action instanceof RestoreAction || $action instanceof RestoreBulkAction)
            && $this->usesSoftDeletes($this->modelOf($action));
    }

    public function snapshot(Action $action): ?Snapshot
    {
        $records = $this->records($action)->filter(fn (Model $record): bool => (bool) $record->getAttribute($this->deletedAtColumn($record)));

        return $this->makeSnapshot($action, UndoableOperation::Restore, $records);
    }

    public function confirm(Snapshot $snapshot): ?Snapshot
    {
        $keys = $this->keysIn($this->trashedQuery($snapshot, false));

        return $keys ? $snapshot->with($keys) : null;
    }

    public function undo(Snapshot $snapshot): void
    {
        $records = $this->trashedQuery($snapshot, false)->get();

        throw_if($records->isEmpty(), UndoFailed::because(UndoFailure::Missing));

        $records->each(fn (Model $record) => $record->delete());
    }
}
