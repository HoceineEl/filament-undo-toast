<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Resources\Tags\Pages;

use Filament\Resources\Pages\ManageRecords;
use Workbench\App\Filament\Resources\Tags\TagResource;

class ManageTags extends ManageRecords
{
    protected static string $resource = TagResource::class;
}
