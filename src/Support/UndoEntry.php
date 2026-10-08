<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Support;

use Carbon\CarbonImmutable;
use HoceineEl\UndoToast\Contracts\UndoStrategy;
use Illuminate\Support\Str;

final class UndoEntry
{
    public const GRACE_SECONDS = 30;

    /**
     * @param  class-string<UndoStrategy>  $strategy
     */
    public function __construct(
        public readonly string $token,
        public readonly string $strategy,
        public readonly Snapshot $snapshot,
        public readonly string $message,
        public readonly int $seconds,
        public readonly ?string $userId,
        public readonly ?string $panelId,
        public readonly CarbonImmutable $expiresAt,
    ) {}

    /**
     * @param  class-string<UndoStrategy>  $strategy
     */
    public static function make(string $strategy, Snapshot $snapshot, string $message, int $seconds, ?string $userId, ?string $panelId): self
    {
        return new self(
            token: Str::random(40),
            strategy: $strategy,
            snapshot: $snapshot,
            message: $message,
            seconds: $seconds,
            userId: $userId,
            panelId: $panelId,
            expiresAt: CarbonImmutable::now()->addSeconds($seconds + self::GRACE_SECONDS),
        );
    }

    public function isExpired(): bool
    {
        return CarbonImmutable::now()->greaterThanOrEqualTo($this->expiresAt);
    }

    public function remainingSeconds(): int
    {
        $windowEndsAt = $this->expiresAt->subSeconds(self::GRACE_SECONDS);

        return max(0, (int) ceil(CarbonImmutable::now()->diffInSeconds($windowEndsAt, false)));
    }

    /**
     * @return array{token: string, message: string, operation: string, seconds: int}
     */
    public function toToast(): array
    {
        return [
            'token' => $this->token,
            'message' => $this->message,
            'operation' => $this->snapshot->operation->value,
            'seconds' => $this->remainingSeconds(),
        ];
    }
}
