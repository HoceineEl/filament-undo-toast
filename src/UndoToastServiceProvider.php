<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast;

use Filament\Actions\Action;
use Filament\Actions\Events\ActionCalled;
use Filament\Actions\Events\ActionCalling;
use Filament\Support\Facades\FilamentAsset;
use HoceineEl\UndoToast\Assets\UndoToastComponent;
use HoceineEl\UndoToast\Assets\UndoToastCss;
use HoceineEl\UndoToast\Listeners\CaptureUndoableAction;
use HoceineEl\UndoToast\Livewire\UndoToast;
use HoceineEl\UndoToast\Support\PendingUndoRegistry;
use HoceineEl\UndoToast\Support\UndoableMacro;
use HoceineEl\UndoToast\Support\UndoStore;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class UndoToastServiceProvider extends PackageServiceProvider
{
    public static string $name = 'undo-toast';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews(static::$name)
            ->hasTranslations();
    }

    public function packageRegistered(): void
    {
        $this->app->scoped(PendingUndoRegistry::class);
        $this->app->singleton(UndoStore::class);
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            UndoToastComponent::make('undo-toast', __DIR__.'/../dist/undo-toast.js'),
            UndoToastCss::make('undo-toast', __DIR__.'/../dist/undo-toast.css')->loadedOnRequest(),
        ], 'hoceineel/filament-undo-toast');

        Livewire::component(UndoToast::NAME, UndoToast::class);

        Event::listen(ActionCalling::class, [CaptureUndoableAction::class, 'calling']);
        Event::listen(ActionCalled::class, [CaptureUndoableAction::class, 'called']);

        Action::mixin(new UndoableMacro);
    }
}
