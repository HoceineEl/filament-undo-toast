<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Support;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class UndoStore
{
    public const PREFIX = 'undo-toast:';

    public function put(UndoEntry $entry): void
    {
        $this->cache()->put(self::PREFIX.$entry->token, Crypt::encrypt($entry), $entry->expiresAt);
    }

    public function find(string $token): ?UndoEntry
    {
        if (! $this->isWellFormed($token)) {
            return null;
        }

        return $this->decrypt($this->cache()->get(self::PREFIX.$token));
    }

    /**
     * Atomically take the entry so it can be undone once, even under concurrent requests.
     */
    public function claim(string $token): ?UndoEntry
    {
        $entry = $this->find($token);

        if (! $entry || ! $this->cache()->add(self::PREFIX.$token.':claimed', true, $entry->expiresAt)) {
            return null;
        }

        return $this->decrypt($this->cache()->pull(self::PREFIX.$token));
    }

    protected function decrypt(mixed $payload): ?UndoEntry
    {
        if (! is_string($payload)) {
            return null;
        }

        try {
            $entry = Crypt::decrypt($payload);
        } catch (DecryptException) {
            return null;
        }

        return ($entry instanceof UndoEntry && ! $entry->isExpired()) ? $entry : null;
    }

    protected function isWellFormed(string $token): bool
    {
        return (bool) preg_match('/^[A-Za-z0-9]{40}$/', $token);
    }

    protected function cache(): Repository
    {
        return Cache::store(config('undo-toast.store'));
    }
}
