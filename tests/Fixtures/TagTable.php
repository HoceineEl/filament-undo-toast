<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Tests\Fixtures;

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TagTable extends TableComponent
{
    public function table(Table $table): Table
    {
        return $table
            ->query(Tag::query())
            ->columns([TextColumn::make('name')])
            ->recordActions([
                DeleteAction::make(),
                EditAction::make()->schema([TextInput::make('name')]),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
