<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use HoceineEl\UndoToast\Contracts\HasUndoableRelations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model implements HasUndoableRelations
{
    protected $guarded = [];

    public function undoableRelations(): array
    {
        return ['tags', 'tasks'];
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withPivot('role')->withTimestamps();
    }

    /** @return HasMany<Task, $this> */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
