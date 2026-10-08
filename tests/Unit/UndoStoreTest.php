<?php

declare(strict_types=1);

use HoceineEl\UndoToast\Enums\UndoableOperation;
use HoceineEl\UndoToast\Strategies\HardDeleteStrategy;
use HoceineEl\UndoToast\Support\Snapshot;
use HoceineEl\UndoToast\Support\UndoEntry;
use HoceineEl\UndoToast\Support\UndoStore;
use HoceineEl\UndoToast\Tests\Fixtures\Tag;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Date;

function entry(int $seconds = 8): UndoEntry
{
    return UndoEntry::make(
        strategy: HardDeleteStrategy::class,
        snapshot: new Snapshot(UndoableOperation::Delete, Tag::class, [1], ['rows' => [1 => ['id' => 1, 'secret' => 'hunter2']]]),
        message: 'Deleted 1 tag',
        seconds: $seconds,
        userId: '1',
        panelId: 'admin',
    );
}

it('stores entries encrypted under a 40 character token', function (): void {
    $entry = entry();
    app(UndoStore::class)->put($entry);

    $raw = Cache::get('undo-toast:'.$entry->token);

    expect($entry->token)->toHaveLength(40)
        ->and($raw)->toBeString()
        ->and($raw)->not->toContain('hunter2')
        ->and(Crypt::decrypt($raw))->toBeInstanceOf(UndoEntry::class);
});

it('finds an entry and claims it only once', function (): void {
    $entry = entry();
    $store = app(UndoStore::class);
    $store->put($entry);

    expect($store->find($entry->token)?->message)->toBe('Deleted 1 tag')
        ->and($store->claim($entry->token)?->token)->toBe($entry->token)
        ->and($store->claim($entry->token))->toBeNull()
        ->and($store->find($entry->token))->toBeNull();
});

it('keeps entries for the window plus a grace period', function (): void {
    $entry = entry(8);
    app(UndoStore::class)->put($entry);

    expect($entry->expiresAt->timestamp)->toBe(now()->addSeconds(38)->timestamp);

    Date::setTestNow(now()->addSeconds(37));
    expect(app(UndoStore::class)->find($entry->token))->not->toBeNull();

    Date::setTestNow(now()->addSeconds(2));
    expect(app(UndoStore::class)->find($entry->token))->toBeNull();
});

it('ignores tampered payloads', function (): void {
    Cache::put('undo-toast:'.str_repeat('a', 40), 'not-encrypted', 60);

    expect(app(UndoStore::class)->find(str_repeat('a', 40)))->toBeNull();
});

it('builds the toast payload with the remaining seconds', function (): void {
    expect(entry(8)->toToast())->toMatchArray(['message' => 'Deleted 1 tag', 'operation' => 'delete', 'seconds' => 8]);
});
