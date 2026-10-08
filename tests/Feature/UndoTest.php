<?php

declare(strict_types=1);

use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use HoceineEl\UndoToast\Livewire\UndoToast;
use HoceineEl\UndoToast\Support\UndoEntry;
use HoceineEl\UndoToast\Tests\Fixtures\Customer;
use HoceineEl\UndoToast\Tests\Fixtures\CustomerPage;
use HoceineEl\UndoToast\Tests\Fixtures\CustomerTable;
use HoceineEl\UndoToast\Tests\Fixtures\TagTable;
use HoceineEl\UndoToast\Tests\Fixtures\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function deleteCustomer(?Customer $record = null): string
{
    $component = Livewire::test(CustomerTable::class)
        ->callAction(TestAction::make('delete')->table($record ?? customer()));

    return pushedToasts($component)[0]['token'];
}

it('restores the record and announces it', function (): void {
    $record = customer();
    $token = deleteCustomer($record);

    $component = Livewire::test(UndoToast::class);
    $result = $component->instance()->undo($token);

    expect($result)->toMatchArray(['ok' => true, 'message' => 'Customer restored'])
        ->and($record->fresh()->trashed())->toBeFalse();

    $component->call('undo', deleteCustomer())
        ->assertDispatched('undo-toast-undone')
        ->assertNotified('Customer restored');
});

it('undoes only once', function (): void {
    $token = deleteCustomer();
    $toast = Livewire::test(UndoToast::class)->instance();

    expect($toast->undo($token)['ok'])->toBeTrue()
        ->and($toast->undo($token)['ok'])->toBeFalse();
});

it('refuses another user', function (): void {
    $record = customer();
    $token = deleteCustomer($record);
    $this->actingAs(User::create(['name' => 'Eve', 'email' => 'eve@example.com', 'password' => 'x']));

    Livewire::test(UndoToast::class)
        ->call('undo', $token)
        ->assertNotified();

    expect($record->fresh()->trashed())->toBeTrue();
});

it('refuses another panel', function (): void {
    $token = deleteCustomer();
    Filament::setCurrentPanel('other');

    expect(app(UndoToast::class)->undo($token)['ok'])->toBeFalse();
});

it('refuses expired entries', function (): void {
    $record = customer();
    $token = deleteCustomer($record);
    $this->travel(39)->seconds();

    expect(Livewire::test(UndoToast::class)->instance()->undo($token)['ok'])->toBeFalse()
        ->and($record->fresh()->trashed())->toBeTrue();
});

it('respects the authorization callback', function (): void {
    plugin()->authorizeUndoUsing(fn (UndoEntry $entry): bool => false);
    $record = customer();
    $token = deleteCustomer($record);

    expect(Livewire::test(UndoToast::class)->instance()->undo($token)['ok'])->toBeFalse()
        ->and($record->fresh()->trashed())->toBeTrue();
});

it('reports failures without throwing', function (): void {
    $record = tag();
    $component = Livewire::test(TagTable::class)
        ->callAction(TestAction::make('delete')->table($record));
    DB::table('tags')->insert(['id' => $record->id, 'name' => 'Squatter']);

    $result = Livewire::test(UndoToast::class)->instance()->undo(pushedToasts($component)[0]['token']);

    expect($result['ok'])->toBeFalse()
        ->and($result['message'])->toBe('A record with the same key already exists.');
});

it('offers the record url after undo when configured', function (): void {
    plugin()->recordUrlUsing(fn (Customer $record): string => "/admin/customers/{$record->id}/edit");
    $record = customer();
    $token = deleteCustomer($record);

    Livewire::test(UndoToast::class)->call('undo', $token);

    $notification = collect(session('filament.claimed_notifications'))->last();

    expect($notification['actions'][0]['url'])->toBe("/admin/customers/{$record->id}/edit");
});

it('mounts with toasts flashed before a redirect for this user only', function (): void {
    Livewire::test(CustomerPage::class, ['customerId' => customer()->id])->callAction('delete');
    $token = flashedToasts()[0]['token'];

    expect(Livewire::test(UndoToast::class)->instance()->getConfig()['pending'][0]['token'])->toBe($token);

    $this->actingAs(User::create(['name' => 'Eve', 'email' => 'eve@example.com', 'password' => 'x']));

    expect(Livewire::test(UndoToast::class)->instance()->getConfig()['pending'])->toBeEmpty();
});
