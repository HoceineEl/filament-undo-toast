<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Strategies;

use Filament\Actions\Action;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use HoceineEl\UndoToast\Contracts\ConfirmsOutcome;
use HoceineEl\UndoToast\Contracts\UndoStrategy;
use HoceineEl\UndoToast\Enums\UndoableOperation;
use HoceineEl\UndoToast\Enums\UndoFailure;
use HoceineEl\UndoToast\Strategies\Concerns\InteractsWithActionRecords;
use HoceineEl\UndoToast\Support\Snapshot;
use HoceineEl\UndoToast\Support\UndoFailed;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DetachStrategy implements ConfirmsOutcome, UndoStrategy
{
    use InteractsWithActionRecords;

    public function supports(Action $action): bool
    {
        return ($action instanceof DetachAction || $action instanceof DetachBulkAction)
            && $action->getTable()?->getRelationship() instanceof BelongsToMany;
    }

    public function snapshot(Action $action): ?Snapshot
    {
        /** @var BelongsToMany<Model, Model> $relationship */
        $relationship = $action->getTable()?->getRelationship();
        $records = $this->records($action);
        $snapshot = $this->makeSnapshot($action, UndoableOperation::Detach, $records);

        if (! $snapshot) {
            return null;
        }

        $relatedKeys = $records->map(fn (Model $record): mixed => $record->getAttribute($relationship->getRelatedKeyName()))->all();
        $parent = $relationship->getParent();

        return $snapshot->with($snapshot->keys, [
            'parent' => $parent::class,
            'parent_key' => $parent->getKey(),
            'relation' => $relationship->getRelationName(),
            'rows' => $this->pivotRows($relationship, $relatedKeys),
        ]);
    }

    public function confirm(Snapshot $snapshot): ?Snapshot
    {
        $relationship = $this->relationship($snapshot);
        $relatedPivotKey = $relationship->getRelatedPivotKeyName();

        /** @var list<array<string, mixed>> $rows */
        $rows = $snapshot->data['rows'];
        $remaining = array_column($this->pivotRows($relationship, array_column($rows, $relatedPivotKey)), $relatedPivotKey);
        $detached = array_values(array_filter($rows, fn (array $row): bool => ! in_array($row[$relatedPivotKey], $remaining, false)));

        if ($detached === []) {
            return null;
        }

        return $snapshot->with(
            array_values(array_unique(array_column($detached, $relatedPivotKey))),
            [...$snapshot->data, 'rows' => $detached],
        );
    }

    public function undo(Snapshot $snapshot): void
    {
        $relationship = $this->relationship($snapshot);
        $foreignPivotKey = $relationship->getForeignPivotKeyName();
        $relatedPivotKey = $relationship->getRelatedPivotKeyName();

        /** @var list<array<string, mixed>> $rows */
        $rows = $snapshot->data['rows'];

        throw_if(
            $this->pivotRows($relationship, array_column($rows, $relatedPivotKey)) !== [],
            UndoFailed::because(UndoFailure::Conflict),
        );

        foreach ($rows as $row) {
            $relationship->attach($row[$relatedPivotKey], array_diff_key($row, array_flip([$foreignPivotKey, $relatedPivotKey])));
        }
    }

    /**
     * @param  BelongsToMany<Model, Model>  $relationship
     * @param  array<int, mixed>  $relatedKeys
     * @return list<array<string, mixed>>
     */
    protected function pivotRows(BelongsToMany $relationship, array $relatedKeys): array
    {
        return $relationship->newPivotStatement()
            ->where($relationship->getForeignPivotKeyName(), $relationship->getParent()->getAttribute($relationship->getParentKeyName()))
            ->whereIn($relationship->getRelatedPivotKeyName(), $relatedKeys)
            ->get()
            ->map(fn (object $row): array => (array) $row)
            ->values()
            ->all();
    }

    /**
     * @return BelongsToMany<Model, Model>
     */
    protected function relationship(Snapshot $snapshot): BelongsToMany
    {
        /** @var class-string<Model> $parentClass */
        $parentClass = $snapshot->data['parent'];
        $parent = (new $parentClass)->newQueryWithoutScopes()->find($snapshot->data['parent_key']);
        $relation = $snapshot->data['relation'];

        throw_unless($parent instanceof Model && is_string($relation) && method_exists($parent, $relation), UndoFailed::because(UndoFailure::Missing));

        $relationship = $parent->{$relation}();

        throw_unless($relationship instanceof BelongsToMany, UndoFailed::because(UndoFailure::Missing));

        return $relationship;
    }
}
