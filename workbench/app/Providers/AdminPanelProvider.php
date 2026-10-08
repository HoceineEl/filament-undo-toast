<?php

declare(strict_types=1);

namespace Workbench\App\Providers;

use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use HoceineEl\UndoToast\UndoToastPlugin;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Workbench\App\Filament\Resources\Customers\CustomerResource;
use Workbench\App\Filament\Resources\Projects\ProjectResource;
use Workbench\App\Filament\Resources\Tags\TagResource;
use Workbench\App\Http\Middleware\SetLocale;
use Workbench\App\Models\Customer;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->default()
            ->colors(['primary' => Color::Indigo])
            ->resources([CustomerResource::class, ProjectResource::class, TagResource::class])
            ->pages([Dashboard::class])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->middleware([SetLocale::class], isPersistent: true)
            ->plugin(
                UndoToastPlugin::make()
                    ->recordUrlUsing(fn (Model $record): ?string => $record instanceof Customer
                        ? CustomerResource::getUrl('edit', ['record' => $record])
                        : null),
            );
    }
}
