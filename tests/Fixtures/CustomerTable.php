<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Tests\Fixtures;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerTable extends TableComponent
{
    public static bool $afterRan = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(Customer::query())
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withoutGlobalScopes([SoftDeletingScope::class]))
            ->modelLabel('customer')
            ->pluralModelLabel('customers')
            ->columns([TextColumn::make('name')])
            ->recordActions([
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
                EditAction::make()->schema([
                    TextInput::make('name'),
                    TextInput::make('email'),
                    TextInput::make('city'),
                ]),
                Action::make('archive')
                    ->action(fn (Customer $record) => $record->archive())
                    ->undoable(fn (Customer $record) => $record->unarchive()),
                Action::make('rename')
                    ->action(fn (Customer $record) => $record->update(['name' => 'Renamed'])),
                DeleteAction::make('deleteWithAfter')
                    ->after(function (): void {
                        static::$afterRan = true;
                    }),
                DeleteAction::make('deleteHalted')
                    ->before(fn (DeleteAction $action) => $action->halt()),
                DeleteAction::make('deleteFailing')
                    ->using(fn (): bool => false),
                DeleteAction::make('deleteNoUndo')
                    ->undoable(false),
                DeleteAction::make('deleteCustomTitle')
                    ->successNotificationTitle('Customer archived for good'),
                DeleteAction::make('deleteCustomNotification')
                    ->successNotification(fn (Notification $notification): Notification => $notification->title('Gone for now')),
                DeleteAction::make('deleteSilently')
                    ->successNotification(null),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
                RestoreBulkAction::make(),
                ForceDeleteBulkAction::make(),
                DeleteBulkAction::make('deletePartial')
                    ->using(function (Collection $records, DeleteBulkAction $action): void {
                        $records->each(fn (Customer $record) => str_starts_with($record->name, 'Keep')
                            ? $action->reportBulkProcessingFailure()
                            : $record->delete());
                    }),
            ]);
    }
}
