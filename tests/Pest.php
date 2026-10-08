<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use HoceineEl\UndoToast\Support\UndoEntry;
use HoceineEl\UndoToast\Support\UndoStore;
use HoceineEl\UndoToast\Tests\Fixtures\Customer;
use HoceineEl\UndoToast\Tests\Fixtures\CustomerTable;
use HoceineEl\UndoToast\Tests\Fixtures\Tag;
use HoceineEl\UndoToast\Tests\TestCase;
use HoceineEl\UndoToast\UndoToastPlugin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;

uses(TestCase::class, RefreshDatabase::class)->in(__DIR__);

uses()->afterEach(function (): void {
    CustomerTable::$afterRan = false;
})->in('Feature');

function customer(string $name = 'Acme', array $attributes = []): Customer
{
    return Customer::create(['name' => $name, 'email' => strtolower($name).'@example.com', ...$attributes]);
}

function tag(string $name = 'Urgent', array $attributes = []): Tag
{
    return Tag::create(['name' => $name, ...$attributes]);
}

function plugin(): UndoToastPlugin
{
    /** @var UndoToastPlugin $plugin */
    $plugin = Filament::getPanel('admin')->getPlugin('undo-toast');

    return $plugin;
}

/**
 * @return list<array{token: string, message: string, operation: string, seconds: int}>
 */
function pushedToasts(Testable $component): array
{
    return collect(data_get($component->effects, 'dispatches'))
        ->where('name', 'undo-toast-push')
        ->pluck('params')
        ->values()
        ->all();
}

function pushedEntry(Testable $component): ?UndoEntry
{
    $toast = collect(pushedToasts($component))->last();

    return $toast ? app(UndoStore::class)->find($toast['token']) : null;
}

/**
 * @return list<array{token: string, message: string, operation: string, seconds: int}>
 */
function flashedToasts(): array
{
    return session('undo-toast.pending', []);
}
