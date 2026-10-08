<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use HoceineEl\UndoToast\Enums\ToastPosition;
use HoceineEl\UndoToast\Livewire\UndoToast;
use HoceineEl\UndoToast\UndoToastPlugin;
use Livewire\Livewire;

it('registers the plugin with its id', function (): void {
    expect(UndoToastPlugin::make()->getId())->toBe('undo-toast')
        ->and(UndoToastPlugin::get())->toBeInstanceOf(UndoToastPlugin::class);
});

it('renders the toast region at the end of the body for panels with the plugin', function (): void {
    Filament::getPanel('admin')->boot();

    expect((string) FilamentView::renderHook(PanelsRenderHook::BODY_END))->toContain('undoToast(');

    Filament::setCurrentPanel('other');
    Filament::getPanel('other')->boot();

    expect((string) FilamentView::renderHook(PanelsRenderHook::BODY_END))->not->toContain('undoToast(');
});

it('renders an accessible live region with the configured options', function (): void {
    plugin()->position(ToastPosition::BottomEnd)->maxVisible(2)->keyBindings(['mod+shift+z'])->duration(6);

    $component = Livewire::test(UndoToast::class)
        ->assertSee('role="status"', false)
        ->assertSee('aria-live="polite"', false)
        ->assertSee('ut-position-bottom-end', false)
        ->assertSee('undoToast(', false);

    expect($component->instance()->getConfig())->toMatchArray([
        'duration' => 6,
        'maxVisible' => 2,
        'keyBindings' => ['mod+shift+z'],
    ]);
});

it('can turn the keyboard shortcut off', function (): void {
    plugin()->keyBindings([]);

    expect(Livewire::test(UndoToast::class)->instance()->getConfig()['keyBindings'])->toBe([]);
});
