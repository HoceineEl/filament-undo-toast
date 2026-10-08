<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Contracts;

use HoceineEl\UndoToast\Support\Snapshot;

interface ConfirmsOutcome
{
    /**
     * Narrow the snapshot to the records the action really changed, or null when it changed none.
     */
    public function confirm(Snapshot $snapshot): ?Snapshot;
}
