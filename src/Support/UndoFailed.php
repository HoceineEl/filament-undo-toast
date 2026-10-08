<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Support;

use HoceineEl\UndoToast\Enums\UndoFailure;
use RuntimeException;

class UndoFailed extends RuntimeException
{
    public function __construct(public readonly UndoFailure $reason)
    {
        parent::__construct($reason->getMessage());
    }

    public static function because(UndoFailure $reason): self
    {
        return new self($reason);
    }
}
