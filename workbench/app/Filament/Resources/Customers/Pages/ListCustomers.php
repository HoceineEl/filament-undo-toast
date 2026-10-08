<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Resources\Customers\Pages;

use Filament\Resources\Pages\ListRecords;
use Workbench\App\Filament\Resources\Customers\CustomerResource;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;
}
