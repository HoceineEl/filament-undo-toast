<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Tests\Fixtures;

use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectTagsTable extends TableComponent
{
    public Project $project;

    public function table(Table $table): Table
    {
        return $table
            ->relationship(fn () => $this->project->tags())
            ->inverseRelationship('projects')
            ->columns([TextColumn::make('name')])
            ->recordActions([DetachAction::make()])
            ->toolbarActions([DetachBulkAction::make()]);
    }
}
