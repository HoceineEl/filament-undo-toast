<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Contracts;

use Filament\Actions\Action;
use HoceineEl\UndoToast\Support\Snapshot;

interface UndoStrategy
{
    public function supports(Action $action): bool;

    public function snapshot(Action $action): ?Snapshot;

    public function undo(Snapshot $snapshot): void;

    public function describe(Snapshot $snapshot): string;
}
