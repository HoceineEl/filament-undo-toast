<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Tests\Fixtures;

use Filament\Actions\Action;
use HoceineEl\UndoToast\Contracts\UndoStrategy;
use HoceineEl\UndoToast\Enums\UndoableOperation;
use HoceineEl\UndoToast\Support\Snapshot;

class ArchiveStrategy implements UndoStrategy
{
    public function supports(Action $action): bool
    {
        return $action->getName() === 'rename';
    }

    public function snapshot(Action $action): ?Snapshot
    {
        return new Snapshot(UndoableOperation::Custom, Customer::class, [$action->getRecord()->getKey()], ['name' => $action->getRecord()->name]);
    }

    public function undo(Snapshot $snapshot): void
    {
        Customer::whereKey($snapshot->keys)->update(['name' => $snapshot->data['name']]);
    }

    public function describe(Snapshot $snapshot): string
    {
        return 'Renamed';
    }
}
