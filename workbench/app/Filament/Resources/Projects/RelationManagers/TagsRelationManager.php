<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Resources\Projects\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TagsRelationManager extends RelationManager
{
    protected static string $relationship = 'tags';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('role'),
            ])
            ->headerActions([
                AttachAction::make()->schema(fn (AttachAction $action): array => [
                    $action->getRecordSelect(),
                    TextInput::make('role'),
                ]),
            ])
            ->recordActions([DetachAction::make()])
            ->toolbarActions([DetachBulkAction::make()]);
    }
}
