<?php

declare(strict_types=1);

use Filament\Actions\Testing\TestAction;
use HoceineEl\UndoToast\Tests\Fixtures\CustomerTable;
use HoceineEl\UndoToast\Tests\Fixtures\TagTable;
use Livewire\Livewire;

function deletedTagMessage(int $count = 1): string
{
    $tags = collect(range(1, $count))->map(fn (int $index): mixed => tag("Tag {$index}"));
    $component = Livewire::test(TagTable::class);

    $component = $count === 1
        ? $component->callAction(TestAction::make('delete')->table($tags->first()))
        : $component->selectTableRecords($tags->pluck('id')->all())->callAction(TestAction::make('delete')->table()->bulk());

    return pushedToasts($component)[0]['message'];
}

it('uses the package translation of a model when the label is the default one', function (): void {
    app()->setLocale('ar');
    app('translator')->load('undo-toast', 'undo-toast', 'ar');
    app('translator')->addLines([
        'undo-toast.models.Tag.label' => 'الوسم',
        'undo-toast.models.Tag.plural_label' => 'وسوم',
    ], 'ar', 'undo-toast');

    expect(deletedTagMessage())->toBe('تم حذف الوسم')
        ->and(deletedTagMessage(3))->toBe('تم حذف 3 من وسوم');
});

it('falls back to the app translation of the label', function (): void {
    app()->setLocale('fr');
    app('translator')->addLines(['*.tag' => 'étiquette', '*.tags' => 'étiquettes'], 'fr');

    expect(deletedTagMessage())->toBe('Élément supprimé (étiquette)')
        ->and(deletedTagMessage(2))->toBe('2 éléments supprimés (étiquettes)');
});

it('keeps the default label when nothing is translated', function (): void {
    app()->setLocale('fr');

    expect(deletedTagMessage())->toBe('Élément supprimé (tag)');
});

it('never translates a label the app set itself', function (): void {
    app()->setLocale('fr');
    app('translator')->addLines(['*.client' => 'NOPE'], 'fr');

    $component = Livewire::test(CustomerTable::class)
        ->callAction(TestAction::make('delete')->table(customer()));

    expect(pushedToasts($component)[0]['message'])->toBe('Élément supprimé (customer)');
});
