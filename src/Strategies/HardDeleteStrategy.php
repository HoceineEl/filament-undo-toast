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
use HoceineEl\UndoToast\Support\RelationSnapshot;
use HoceineEl\UndoToast\Support\Snapshot;
use HoceineEl\UndoToast\Support\UndoFailed;
use HoceineEl\UndoToast\UndoToastPlugin;
use Illuminate\Database\Eloquent\Model;

class HardDeleteStrategy implements ConfirmsOutcome, UndoStrategy
{
    use InteractsWithActionRecords;

    public function supports(Action $action): bool
    {
        $model = $this->modelOf($action);

        return ($action instanceof DeleteAction || $action instanceof DeleteBulkAction)
            && $model !== null
            && ! $this->usesSoftDeletes($model);
    }

    public function snapshot(Action $action): ?Snapshot
    {
        $records = $this->records($action);
        $model = $records->first();

        if (! $model instanceof Model) {
            return null;
        }

        $rows = $model->newQueryWithoutScopes()
            ->whereKey($records->map(fn (Model $record): int|string => $record->getKey())->all())
            ->toBase()
            ->get()
            ->mapWithKeys(fn (object $row): array => [((array) $row)[$model->getKeyName()] => (array) $row])
            ->all();

        $plugin = UndoToastPlugin::current();
        $relations = $records
            ->mapWithKeys(fn (Model $record): array => [
                $record->getKey() => RelationSnapshot::take($record, $plugin?->getUndoableRelations($record) ?? []),
            ])
            ->filter()
            ->all();

        return $this->makeSnapshot($action, UndoableOperation::Delete, $records, [
            'connection' => $model->getConnectionName(),
            'table' => $model->getTable(),
            'rows' => $rows,
            'relations' => $relations,
        ]);
    }

    public function confirm(Snapshot $snapshot): ?Snapshot
    {
        $remaining = $this->keysIn($this->newModel($snapshot)->newQueryWithoutScopes()->whereKey($snapshot->keys));
        $deleted = array_values(array_filter($snapshot->keys, fn (int|string $key): bool => ! in_array($key, $remaining, false)));

        if ($deleted === []) {
            return null;
        }

        $deletedKeys = array_flip($deleted);

        return $snapshot->with($deleted, [
            ...$snapshot->data,
            'rows' => array_intersect_key($snapshot->data['rows'], $deletedKeys),
            'relations' => array_intersect_key($snapshot->data['relations'] ?? [], $deletedKeys),
        ]);
    }

    public function undo(Snapshot $snapshot): void
    {
        $model = $this->newModel($snapshot);

        throw_if($model->newQueryWithoutScopes()->whereKey($snapshot->keys)->exists(), UndoFailed::because(UndoFailure::Conflict));

        /** @var array<int|string, array<string, mixed>> $rows */
        $rows = $snapshot->data['rows'];
        /** @var array<int|string, array<string, list<array<string, mixed>>>> $relations */
        $relations = $snapshot->data['relations'] ?? [];

        Model::unguarded(function () use ($model, $rows, $relations): void {
            foreach ($rows as $key => $attributes) {
                $record = $model->newInstance();
                $record->setRawAttributes($attributes, true);
                $record->exists = false;
                $record->timestamps = false;
                $record->save();

                RelationSnapshot::restore($record, $relations[$key] ?? []);
            }
        });
    }

    protected function newModel(Snapshot $snapshot): Model
    {
        /** @var Model $model */
        $model = new $snapshot->modelClass;

        return $model
            ->setConnection($snapshot->data['connection'] ?? null)
            ->setTable($snapshot->data['table'] ?? $model->getTable());
    }
}
