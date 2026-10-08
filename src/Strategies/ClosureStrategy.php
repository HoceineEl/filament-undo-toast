<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Strategies;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use HoceineEl\UndoToast\Contracts\UndoStrategy;
use HoceineEl\UndoToast\Enums\UndoableOperation;
use HoceineEl\UndoToast\Enums\UndoFailure;
use HoceineEl\UndoToast\Strategies\Concerns\InteractsWithActionRecords;
use HoceineEl\UndoToast\Support\Snapshot;
use HoceineEl\UndoToast\Support\Undoable;
use HoceineEl\UndoToast\Support\UndoFailed;
use Illuminate\Database\Eloquent\Model;
use Laravel\SerializableClosure\SerializableClosure;

class ClosureStrategy implements UndoStrategy
{
    use InteractsWithActionRecords;

    public function supports(Action $action): bool
    {
        return Undoable::of($action)?->callback instanceof Closure
            && ! $action instanceof ForceDeleteAction
            && ! $action instanceof ForceDeleteBulkAction;
    }

    public function snapshot(Action $action): ?Snapshot
    {
        $undoable = Undoable::of($action);

        if (! $undoable?->callback) {
            return null;
        }

        return $this->makeSnapshot($action, UndoableOperation::Custom, $this->records($action), [
            'callback' => new SerializableClosure($undoable->callback),
            'message' => $undoable->message !== null ? (string) $action->evaluate($undoable->message) : null,
            'action' => (string) $action->getLabel(),
        ]);
    }

    public function undo(Snapshot $snapshot): void
    {
        $callback = $snapshot->data['callback'] ?? null;

        throw_unless($callback instanceof SerializableClosure, UndoFailed::because(UndoFailure::Error));

        $records = $this->query($snapshot)->get();

        throw_if($records->isEmpty(), UndoFailed::because(UndoFailure::Missing));

        $records->each(fn (Model $record): mixed => app()->call($callback->getClosure(), ['record' => $record]));
    }

    public function describe(Snapshot $snapshot): string
    {
        if (filled($snapshot->data['message'] ?? null)) {
            return (string) $snapshot->data['message'];
        }

        return $snapshot->operation->describe($snapshot->count(), [
            ...$snapshot->labels(),
            'action' => (string) ($snapshot->data['action'] ?? ''),
        ]);
    }
}
