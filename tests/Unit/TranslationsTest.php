<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

const TRANSLATION_LOCALES = ['ar', 'cs', 'de', 'en', 'es', 'fa', 'fr', 'he', 'hi', 'id', 'it', 'ja', 'ko', 'nl', 'pl', 'pt', 'pt_BR', 'ru', 'tr', 'uk', 'vi', 'zh_CN', 'zh_TW'];

const TRANSLATION_COUNTS = [0, 1, 2, 3, 5, 11, 21, 22, 25, 101];

const TRANSLATION_REPLACE = ['label' => 'lbl', 'plural_label' => 'plrl', 'action' => 'actn'];

const IDENTICAL_TO_ENGLISH = ['messages.custom'];

/**
 * @return array<string, string>
 */
function translationLines(string $locale): array
{
    return Arr::dot(require dirname(__DIR__, 2)."/resources/lang/{$locale}/undo-toast.php");
}

/**
 * @return list<string>
 */
function translationPlaceholders(string $line): array
{
    preg_match_all('/:([A-Za-z_]+)/', $line, $matches);

    return collect($matches[1])->map(fn (string $name): string => Str::lower($name))->unique()->sort()->values()->all();
}

/**
 * @return list<string>
 */
function pluralKeys(): array
{
    return collect(array_keys(translationLines('en')))
        ->filter(fn (string $key): bool => Str::startsWith($key, ['messages.', 'undone.']))
        ->values()
        ->all();
}

function renderChoice(string $key, int $count, string $locale): string
{
    return trans_choice("undo-toast::undo-toast.{$key}", $count, ['count' => $count, ...TRANSLATION_REPLACE], $locale);
}

it('ships exactly the supported locales', function (): void {
    $directories = collect(glob(dirname(__DIR__, 2).'/resources/lang/*', GLOB_ONLYDIR))->map(fn (string $path): string => basename($path))->sort()->values()->all();

    expect($directories)->toBe(collect(TRANSLATION_LOCALES)->sort()->values()->all());
});

it('has the same keys as English', function (string $locale): void {
    expect(array_keys(translationLines($locale)))->toEqualCanonicalizing(array_keys(translationLines('en')));
})->with(TRANSLATION_LOCALES);

it('uses the same placeholders as English', function (string $locale): void {
    $english = translationLines('en');

    foreach (translationLines($locale) as $key => $line) {
        expect(translationPlaceholders($line))->toBe(translationPlaceholders($english[$key]), "{$locale}: {$key}");
    }
})->with(TRANSLATION_LOCALES);

it('renders every plural message for every count', function (string $locale): void {
    foreach (pluralKeys() as $key) {
        foreach (TRANSLATION_COUNTS as $count) {
            $english = Str::lower(renderChoice($key, $count, 'en'));
            $line = renderChoice($key, $count, $locale);
            $context = "{$locale}: {$key} ({$count}) \"{$line}\"";

            expect($line)->not->toBeEmpty($context)
                ->not->toMatch('/^[\{\[]|\|/', $context)
                ->not->toMatch('/:[a-z_]+/i', $context);

            foreach (['count' => (string) $count, ...TRANSLATION_REPLACE] as $value) {
                if (str_contains($english, $value)) {
                    expect(Str::contains($line, $value, ignoreCase: true))->toBeTrue("{$context} is missing {$value}");
                }
            }
        }
    }
})->with(TRANSLATION_LOCALES);

it('translates every line', function (string $locale): void {
    $english = translationLines('en');

    foreach (translationLines($locale) as $key => $line) {
        expect(trim($line))->not->toBeEmpty("{$locale}: {$key}");

        if (! in_array($key, IDENTICAL_TO_ENGLISH, true)) {
            expect($line)->not->toBe($english[$key], "{$locale}: {$key}");
        }
    }
})->with(array_values(array_diff(TRANSLATION_LOCALES, ['en'])));

it('picks the grammatical plural form', function (string $locale, int $count, string $expected): void {
    expect(renderChoice('messages.delete', $count, $locale))->toBe($expected);
})->with([
    ['en', 1, 'Lbl deleted'],
    ['en', 0, 'Deleted 0 plrl'],
    ['fr', 0, '0 éléments supprimés (plrl)'],
    ['ja', 0, 'plrlを0件削除しました'],
    ['ru', 1, 'Запись удалена (lbl)'],
    ['ru', 2, 'Удалены 2 записи (plrl)'],
    ['ru', 5, 'Удалено 5 записей (plrl)'],
    ['ru', 11, 'Удалено 11 записей (plrl)'],
    ['ru', 21, 'Удалена 21 запись (plrl)'],
    ['ru', 22, 'Удалены 22 записи (plrl)'],
    ['ru', 25, 'Удалено 25 записей (plrl)'],
    ['ru', 101, 'Удалена 101 запись (plrl)'],
    ['uk', 21, 'Видалено 21 запис (plrl)'],
    ['uk', 22, 'Видалено 22 записи (plrl)'],
    ['uk', 11, 'Видалено 11 записів (plrl)'],
    ['pl', 1, 'Usunięto rekord (lbl)'],
    ['pl', 3, 'Usunięto 3 rekordy (plrl)'],
    ['pl', 12, 'Usunięto 12 rekordów (plrl)'],
    ['pl', 21, 'Usunięto 21 rekordów (plrl)'],
    ['pl', 22, 'Usunięto 22 rekordy (plrl)'],
    ['cs', 1, 'Záznam smazán (lbl)'],
    ['cs', 3, 'Smazány 3 záznamy (plrl)'],
    ['cs', 5, 'Smazáno 5 záznamů (plrl)'],
    ['cs', 22, 'Smazáno 22 záznamů (plrl)'],
    ['ar', 0, 'تم حذف 0 من plrl'],
    ['ar', 1, 'تم حذف lbl'],
    ['ar', 2, 'تم حذف 2 من plrl'],
    ['he', 1, 'הרשומה נמחקה (lbl)'],
    ['he', 2, 'נמחקו 2 plrl'],
]);
