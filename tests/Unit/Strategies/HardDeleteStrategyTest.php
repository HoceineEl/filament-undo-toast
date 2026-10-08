<?php

declare(strict_types=1);

use Filament\Actions\DeleteAction;
use HoceineEl\UndoToast\Strategies\HardDeleteStrategy;
use HoceineEl\UndoToast\Support\UndoFailed;
use HoceineEl\UndoToast\Tests\Fixtures\Tag;
use Illuminate\Support\Facades\DB;

it('supports delete actions on models without soft deletes', function (): void {
    $strategy = new HardDeleteStrategy;

    expect($strategy->supports(DeleteAction::make()->record(tag())))->toBeTrue()
        ->and($strategy->supports(DeleteAction::make()->record(customer())))->toBeFalse();
});

it('re-inserts the row with its original key and attributes', function (): void {
    tag('First');
    $record = tag('Second', ['secret' => 's3cret', 'meta' => ['a' => 1]]);
    $before = (array) DB::table('tags')->where('id', $record->id)->first();
    $strategy = new HardDeleteStrategy;

    $snapshot = $strategy->snapshot(DeleteAction::make()->record($record));
    $record->delete();
    $snapshot = $strategy->confirm($snapshot);

    $this->travel(5)->minutes();
    $strategy->undo($snapshot);

    expect((array) DB::table('tags')->where('id', $record->id)->first())->toBe($before)
        ->and(Tag::find($record->id)->meta)->toBe(['a' => 1]);
});

it('refuses to overwrite a row that came back with the same key', function (): void {
    $record = tag();
    $strategy = new HardDeleteStrategy;
    $snapshot = $strategy->snapshot(DeleteAction::make()->record($record));
    $record->delete();
    $snapshot = $strategy->confirm($snapshot);
    DB::table('tags')->insert(['id' => $record->id, 'name' => 'Other']);

    $strategy->undo($snapshot);
})->throws(UndoFailed::class);
