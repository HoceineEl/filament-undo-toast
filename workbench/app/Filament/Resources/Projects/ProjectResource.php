<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Resources\Projects;

use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Workbench\App\Filament\Resources\Projects\Pages\EditProject;
use Workbench\App\Filament\Resources\Projects\Pages\ListProjects;
use Workbench\App\Filament\Resources\Projects\RelationManagers\TagsRelationManager;
use Workbench\App\Models\Project;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([TextInput::make('name')->required()]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('tags_count')->counts('tags')->label('Tags'),
                TextColumn::make('tasks_count')->counts('tasks')->label('Tasks'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getRelations(): array
    {
        return [TagsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjects::route('/'),
            'edit' => EditProject::route('/{record}/edit'),
        ];
    }
}
