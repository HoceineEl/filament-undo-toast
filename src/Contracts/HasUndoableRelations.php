<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Contracts;

interface HasUndoableRelations
{
    /**
     * @return array<int, string>
     */
    public function undoableRelations(): array;
}
