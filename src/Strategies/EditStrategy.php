<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Strategies;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use HoceineEl\UndoToast\Contracts\ConfirmsOutcome;
use HoceineEl\UndoToast\Contracts\UndoStrategy;
use HoceineEl\UndoToast\Enums\UndoableOperation;
use HoceineEl\UndoToast\Enums\UndoFailure;
use HoceineEl\UndoToast\Strategies\Concerns\InteractsWithActionRecords;
use HoceineEl\UndoToast\Support\Snapshot;
use HoceineEl\UndoToast\Support\UndoFailed;
use Illuminate\Database\Eloquent\Model;

class EditStrategy implements ConfirmsOutcome, UndoStrategy
{
    use InteractsWithActionRecords;

    public function supports(Action $action): bool
    {
        return $action instanceof EditAction && $action->getRecord() instanceof Model;
    }

    public function snapshot(Action $action): ?Snapshot
    {
        $records = $this->records($action);
        $snapshot = $this->makeSnapshot($action, UndoableOperation::Edit, $records);

        if (! $snapshot) {
            return null;
        }

        return $snapshot->with($snapshot->keys, ['row' => $this->row($snapshot)]);
    }

    public function confirm(Snapshot $snapshot): ?Snapshot
    {
        /** @var array<string, mixed> $before */
        $before = $snapshot->data['row'] ?? [];
        $after = $this->row($snapshot);
        $ignored = $this->newModel($snapshot)->getUpdatedAtColumn();

        $changed = array_filter(
            array_keys($before),
            fn (string $column): bool => $column !== $ignored
                && array_key_exists($column, $after)
                && ! $this->same($before[$column], $after[$column]),
        );

        if ($changed === []) {
            return null;
        }

        $changed = array_flip($changed);

        return $snapshot->with($snapshot->keys, [
            'before' => array_intersect_key($before, $changed),
            'after' => array_intersect_key($after, $changed),
        ]);
    }

    public function undo(Snapshot $snapshot): void
    {
        $record = $this->query($snapshot)->first();

        throw_unless($record instanceof Model, UndoFailed::because(UndoFailure::Missing));

        /** @var array<string, mixed> $before */
        $before = $snapshot->data['before'];
        /** @var array<string, mixed> $after */
        $after = $snapshot->data['after'];
        $current = $record->getAttributes();

        $revert = array_filter(
            $before,
            fn (string $column): bool => $this->same($current[$column] ?? null, $after[$column] ?? null),
            ARRAY_FILTER_USE_KEY,
        );

        throw_if($revert === [], UndoFailed::because(UndoFailure::ChangedSince));

        $record->setRawAttributes([...$current, ...$revert]);
        $record->save();
    }

    /**
     * @return array<string, mixed>
     */
    protected function row(Snapshot $snapshot): array
    {
        return (array) $this->query($snapshot)->toBase()->first();
    }

    protected function newModel(Snapshot $snapshot): Model
    {
        return new $snapshot->modelClass;
    }

    protected function same(mixed $a, mixed $b): bool
    {
        return ($a === null || $b === null) ? $a === $b : (string) $a === (string) $b;
    }
}
