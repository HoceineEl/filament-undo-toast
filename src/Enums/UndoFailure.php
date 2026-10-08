<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Enums;

enum UndoFailure: string
{
    case Unavailable = 'unavailable';

    case Forbidden = 'forbidden';

    case Expired = 'expired';

    case Missing = 'missing';

    case Conflict = 'conflict';

    case ChangedSince = 'changed_since';

    case Error = 'error';

    public function getMessage(): string
    {
        return __("undo-toast::undo-toast.failures.{$this->value}");
    }
}
