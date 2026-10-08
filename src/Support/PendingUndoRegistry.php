<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Support;

use Filament\Actions\Action;
use HoceineEl\UndoToast\Contracts\UndoStrategy;
use WeakMap;

class PendingUndoRegistry
{
    /** @var WeakMap<Action, array{UndoStrategy, Snapshot}> */
    protected WeakMap $pending;

    public function __construct()
    {
        $this->pending = new WeakMap;
    }

    public function put(Action $action, UndoStrategy $strategy, Snapshot $snapshot): void
    {
        $this->pending[$action] = [$strategy, $snapshot];
    }

    /**
     * @return array{UndoStrategy, Snapshot}|null
     */
    public function pull(Action $action): ?array
    {
        $pending = $this->pending[$action] ?? null;

        unset($this->pending[$action]);

        return $pending;
    }
}
