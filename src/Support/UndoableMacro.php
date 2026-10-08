<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Support;

use Closure;
use Filament\Actions\Action;

final class UndoableMacro
{
    public function undoable(): Closure
    {
        return function (bool|Closure $callback = true, string|Closure|null $message = null): Action {
            /** @var Action $action */
            $action = $this;

            return $action->viewData([
                Undoable::VIEW_DATA_KEY => $callback instanceof Closure
                    ? new Undoable(true, $callback, $message)
                    : new Undoable($callback),
            ]);
        };
    }
}
