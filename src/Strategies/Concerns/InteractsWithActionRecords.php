<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Strategies\Concerns;

use Filament\Actions\Action;
use HoceineEl\UndoToast\Enums\UndoableOperation;
use HoceineEl\UndoToast\Support\ModelLabels;
use HoceineEl\UndoToast\Support\Snapshot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

trait InteractsWithActionRecords
{
    /**
     * @return Collection<int, Model>
     */
    protected function records(Action $action): Collection
    {
        if ($action->isBulk()) {
            return collect($action->getSelectedRecordsQuery()->get()->all());
        }

        $record = $action->getRecord();

        return collect($record instanceof Model ? [$record] : []);
    }

    /**
     * @param  Collection<int, Model>  $records
     * @param  array<string, mixed>  $data
     */
    protected function makeSnapshot(Action $action, UndoableOperation $operation, Collection $records, array $data = []): ?Snapshot
    {
        $model = $records->first();

        if (! $model instanceof Model) {
            return null;
        }

        $labels = ModelLabels::for($action, $model::class);

        return new Snapshot(
            operation: $operation,
            modelClass: $model::class,
            keys: $records->map(fn (Model $record): int|string => $record->getKey())->values()->all(),
            data: $data,
            modelLabel: $labels['label'],
            pluralModelLabel: $labels['plural_label'],
        );
    }

    protected function modelOf(Action $action): ?Model
    {
        $class = $action->getModel();

        return ($class && is_subclass_of($class, Model::class)) ? new $class : null;
    }

    protected function usesSoftDeletes(?Model $model): bool
    {
        return $model !== null && in_array(SoftDeletes::class, class_uses_recursive($model), true);
    }

    protected function deletedAtColumn(Model $model): string
    {
        return method_exists($model, 'getDeletedAtColumn') ? $model->getDeletedAtColumn() : 'deleted_at';
    }

    /**
     * @return Builder<Model>
     */
    protected function trashedQuery(Snapshot $snapshot, bool $trashed): Builder
    {
        $query = $this->query($snapshot);
        $column = $query->getModel()->qualifyColumn($this->deletedAtColumn($query->getModel()));

        return $trashed ? $query->whereNotNull($column) : $query->whereNull($column);
    }

    /**
     * @return Builder<Model>
     */
    protected function query(Snapshot $snapshot): Builder
    {
        /** @var Model $model */
        $model = new $snapshot->modelClass;

        return $model->newQueryWithoutScopes()->whereKey($snapshot->keys);
    }

    /**
     * @param  Builder<Model>  $query
     * @return list<int|string>
     */
    protected function keysIn(Builder $query): array
    {
        return $query->pluck($query->getModel()->getQualifiedKeyName())->all();
    }

    public function describe(Snapshot $snapshot): string
    {
        return $snapshot->operation->describe($snapshot->count(), $snapshot->labels());
    }
}
