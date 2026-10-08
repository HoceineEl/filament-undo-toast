<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Support;

use HoceineEl\UndoToast\Enums\UndoableOperation;
use Illuminate\Database\Eloquent\Model;

final class Snapshot
{
    /**
     * @param  class-string<Model>  $modelClass
     * @param  list<int|string>  $keys
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public readonly UndoableOperation $operation,
        public readonly string $modelClass,
        public readonly array $keys,
        public readonly array $data = [],
        public readonly string $modelLabel = '',
        public readonly string $pluralModelLabel = '',
    ) {}

    public function count(): int
    {
        return count($this->keys);
    }

    /**
     * @param  list<int|string>  $keys
     * @param  array<string, mixed>|null  $data
     */
    public function with(array $keys, ?array $data = null): self
    {
        return new self($this->operation, $this->modelClass, array_values($keys), $data ?? $this->data, $this->modelLabel, $this->pluralModelLabel);
    }

    /**
     * @return array<string, string>
     */
    public function labels(): array
    {
        return ['label' => $this->modelLabel, 'plural_label' => $this->pluralModelLabel];
    }
}
