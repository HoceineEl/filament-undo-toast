<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Enums;

use Filament\Support\Icons\Heroicon;

enum UndoableOperation: string
{
    case Delete = 'delete';

    case Restore = 'restore';

    case Edit = 'edit';

    case Detach = 'detach';

    case Custom = 'custom';

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Delete => Heroicon::OutlinedTrash,
            self::Restore => Heroicon::OutlinedArrowPath,
            self::Edit => Heroicon::OutlinedPencilSquare,
            self::Detach => Heroicon::OutlinedLinkSlash,
            self::Custom => Heroicon::OutlinedArrowUturnLeft,
        };
    }

    /**
     * @param  array<string, string>  $replace
     */
    public function describe(int $count, array $replace = []): string
    {
        return trans_choice("undo-toast::undo-toast.messages.{$this->value}", $count, ['count' => $count, ...$replace]);
    }

    /**
     * @param  array<string, string>  $replace
     */
    public function describeUndone(int $count, array $replace = []): string
    {
        return trans_choice("undo-toast::undo-toast.undone.{$this->value}", $count, ['count' => $count, ...$replace]);
    }
}
