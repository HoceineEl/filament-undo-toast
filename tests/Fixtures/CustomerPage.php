<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Tests\Fixtures;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Livewire\Component;

class CustomerPage extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public int $customerId;

    public function deleteAction(): Action
    {
        return DeleteAction::make()
            ->record(fn (): ?Customer => Customer::find($this->customerId))
            ->successRedirectUrl('/admin/customers');
    }

    public function render(): string
    {
        return '<div>{{ $this->deleteAction }}</div>';
    }
}
