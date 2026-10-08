<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;
use InvalidArgumentException;

final class RelationSnapshot
{
    /**
     * @param  array<int, string>  $relations
     * @return array<string, list<array<string, mixed>>>
     */
    public static function take(Model $record, array $relations): array
    {
        $rows = [];

        foreach ($relations as $name) {
            $relation = self::resolve($record, $name);

            $rows[$name] = self::rows($relation instanceof BelongsToMany ? $relation->newPivotQuery() : clone $relation->getBaseQuery());
        }

        return array_filter($rows);
    }

    /**
     * @param  array<string, list<array<string, mixed>>>  $relations
     */
    public static function restore(Model $record, array $relations): void
    {
        foreach ($relations as $name => $rows) {
            $relation = self::resolve($record, $name);

            if ($relation instanceof BelongsToMany) {
                self::restorePivotRows($relation, $rows);
            } else {
                self::restoreRelatedRows($relation, $rows);
            }
        }
    }

    /**
     * @param  BelongsToMany<Model, Model>  $relation
     * @param  list<array<string, mixed>>  $rows
     */
    protected static function restorePivotRows(BelongsToMany $relation, array $rows): void
    {
        $relatedPivotKey = $relation->getRelatedPivotKeyName();
        $relatedKeys = array_column($rows, $relatedPivotKey);
        $related = $relation->getRelated();
        $relatedKey = $related->qualifyColumn($relation->getRelatedKeyName());

        $linked = $relation->newPivotQuery()->whereIn($relatedPivotKey, $relatedKeys)->pluck($relatedPivotKey)->all();
        $alive = $related->newQueryWithoutScopes()->whereIn($relatedKey, $relatedKeys)->pluck($relatedKey)->all();

        self::insert($relation->newPivotStatement(), array_filter(
            $rows,
            fn (array $row): bool => ! in_array($row[$relatedPivotKey], $linked, false) && in_array($row[$relatedPivotKey], $alive, false),
        ));
    }

    /**
     * @param  HasOneOrMany<Model, Model, mixed>  $relation
     * @param  list<array<string, mixed>>  $rows
     */
    protected static function restoreRelatedRows(HasOneOrMany $relation, array $rows): void
    {
        $related = $relation->getRelated();
        $key = $related->getKeyName();
        $rows = array_filter($rows, fn (array $row): bool => array_key_exists($key, $row));
        $existing = $related->newQueryWithoutScopes()
            ->whereKey(array_column($rows, $key))
            ->pluck($related->getQualifiedKeyName())
            ->all();

        self::insert(
            $related->newQueryWithoutScopes()->toBase(),
            array_filter($rows, fn (array $row): bool => ! in_array($row[$key], $existing, false)),
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    protected static function insert(Builder $query, array $rows): void
    {
        if ($rows !== []) {
            $query->insert(array_values($rows));
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected static function rows(Builder $query): array
    {
        return $query->get()->map(fn (object $row): array => (array) $row)->values()->all();
    }

    /**
     * @return BelongsToMany<Model, Model>|HasOneOrMany<Model, Model, mixed>
     */
    protected static function resolve(Model $record, string $name): BelongsToMany|HasOneOrMany
    {
        $relation = method_exists($record, $name) ? $record->{$name}() : null;

        if ($relation instanceof BelongsToMany || $relation instanceof HasOneOrMany) {
            return $relation;
        }

        throw new InvalidArgumentException(sprintf(
            'Undo Toast can only restore BelongsToMany, HasMany, HasOne, MorphMany and MorphOne relations; [%s::%s] is %s.',
            $record::class,
            $name,
            $relation instanceof Relation ? class_basename($relation) : 'not a relation',
        ));
    }
}
