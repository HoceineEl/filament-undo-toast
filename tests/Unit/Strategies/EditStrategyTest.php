<?php

declare(strict_types=1);

use Filament\Actions\EditAction;
use HoceineEl\UndoToast\Strategies\EditStrategy;
use HoceineEl\UndoToast\Support\UndoFailed;
use HoceineEl\UndoToast\Tests\Fixtures\Customer;

it('reverts only the columns the edit changed', function (): void {
    $record = customer('Acme', ['city' => 'Rabat', 'settings' => ['theme' => 'dark']]);
    $strategy = new EditStrategy;

    $snapshot = $strategy->snapshot(EditAction::make()->record($record));
    $record->update(['name' => 'Acme Ltd', 'settings' => ['theme' => 'light']]);
    $snapshot = $strategy->confirm($snapshot);

    expect(array_keys($snapshot->data['before']))->toEqualCanonicalizing(['name', 'settings']);

    Customer::whereKey($record->id)->update(['city' => 'Fes']);
    $strategy->undo($snapshot);

    $fresh = Customer::find($record->id);
    expect($fresh->name)->toBe('Acme')
        ->and($fresh->settings)->toBe(['theme' => 'dark'])
        ->and($fresh->city)->toBe('Fes');
});

it('confirms nothing when nothing changed', function (): void {
    $strategy = new EditStrategy;
    $snapshot = $strategy->snapshot(EditAction::make()->record(customer()));

    expect($strategy->confirm($snapshot))->toBeNull();
});

it('leaves columns someone else changed since', function (): void {
    $record = customer('Acme');
    $strategy = new EditStrategy;
    $snapshot = $strategy->snapshot(EditAction::make()->record($record));
    $record->update(['name' => 'Acme Ltd']);
    $snapshot = $strategy->confirm($snapshot);
    Customer::whereKey($record->id)->update(['name' => 'Someone else']);

    $strategy->undo($snapshot);
})->throws(UndoFailed::class);
