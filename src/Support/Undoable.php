<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Support;

use Closure;
use Filament\Actions\Action;

final class Undoable
{
    public const VIEW_DATA_KEY = 'undoToastUndoable';

    public function __construct(
        public readonly bool $enabled,
        public readonly ?Closure $callback = null,
        public readonly string|Closure|null $message = null,
    ) {}

    public static function of(Action $action): ?self
    {
        /** @var array<int, mixed> $viewData */
        $viewData = (fn (): array => $this->viewData)->call($action);

        foreach (array_reverse($viewData) as $data) {
            if (is_array($data) && ($data[self::VIEW_DATA_KEY] ?? null) instanceof self) {
                return $data[self::VIEW_DATA_KEY];
            }
        }

        return null;
    }
}
