<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Tests\Fixtures;

use Filament\Panel;
use Filament\PanelProvider;
use HoceineEl\UndoToast\UndoToastPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->default()
            ->plugin(UndoToastPlugin::make());
    }
}
