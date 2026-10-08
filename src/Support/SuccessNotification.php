<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Support;

use Filament\Actions\Action;

final class SuccessNotification
{
    public static function suppress(Action $action): void
    {
        if (! self::isCustomised($action)) {
            $action->successNotification(null);
        }
    }

    public static function isCustomised(Action $action): bool
    {
        if (self::read($action, 'successNotification') !== null || self::read($action, 'isSuccessNotificationDisabled')) {
            return true;
        }

        $stock = app($action::class, ['name' => $action->getName()]);
        (fn (): mixed => $this->setUp())->call($stock);

        return self::read($action, 'successNotificationTitle') !== self::read($stock, 'successNotificationTitle');
    }

    protected static function read(Action $action, string $property): mixed
    {
        return (fn (): mixed => $this->{$property})->call($action);
    }
}
