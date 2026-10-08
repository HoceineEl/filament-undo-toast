<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Resources\Projects\Pages;

use Filament\Resources\Pages\EditRecord;
use Workbench\App\Filament\Resources\Projects\ProjectResource;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;
}
