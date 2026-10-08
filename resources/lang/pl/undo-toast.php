<?php

declare(strict_types=1);

return [
    'messages' => [
        'delete' => '{1} Usunięto rekord (:label)|Usunięto :count rekordy (:plural_label)|Usunięto :count rekordów (:plural_label)',
        'restore' => '{1} Przywrócono rekord (:label)|Przywrócono :count rekordy (:plural_label)|Przywrócono :count rekordów (:plural_label)',
        'edit' => '{1} Zaktualizowano rekord (:label)|Zaktualizowano :count rekordy (:plural_label)|Zaktualizowano :count rekordów (:plural_label)',
        'detach' => '{1} Odłączono rekord (:label)|Odłączono :count rekordy (:plural_label)|Odłączono :count rekordów (:plural_label)',
        'custom' => '{1} :Action: :label|:Action: :count rekordy (:plural_label)|:Action: :count rekordów (:plural_label)',
    ],

    'undone' => [
        'delete' => '{1} Przywrócono rekord (:label)|Przywrócono :count rekordy (:plural_label)|Przywrócono :count rekordów (:plural_label)',
        'restore' => '{1} Ponownie usunięto rekord (:label)|Ponownie usunięto :count rekordy (:plural_label)|Ponownie usunięto :count rekordów (:plural_label)',
        'edit' => '{1} Cofnięto zmiany (:label)|Cofnięto zmiany w :count rekordach (:plural_label)|Cofnięto zmiany w :count rekordach (:plural_label)',
        'detach' => '{1} Ponownie dołączono rekord (:label)|Ponownie dołączono :count rekordy (:plural_label)|Ponownie dołączono :count rekordów (:plural_label)',
        'custom' => '{1} Cofnięto|Cofnięto dla :count rekordów (:plural_label)|Cofnięto dla :count rekordów (:plural_label)',
    ],

    'actions' => [
        'undo_last' => 'Cofnij ostatnią czynność',
        'undo' => 'Cofnij',
        'dismiss' => 'Zamknij',
        'open' => 'Otwórz',
    ],

    'states' => [
        'undoing' => 'Cofanie…',
        'countdown' => 'Pozostało sekund na cofnięcie: :seconds.',
    ],

    'failures' => [
        'title' => 'Nie udało się cofnąć',
        'unavailable' => 'Cofanie nie jest tu dostępne.',
        'forbidden' => 'Nie możesz cofnąć tej zmiany.',
        'expired' => 'Czas na cofnięcie minął lub zmiana została już cofnięta.',
        'missing' => 'Te rekordy już nie istnieją.',
        'conflict' => 'Rekord o tym samym kluczu już istnieje.',
        'changed_since' => 'Ktoś w międzyczasie zmienił ten rekord, więc pozostawiono go bez zmian.',
        'error' => 'Podczas cofania wystąpił błąd. Nic nie zostało zmienione.',
    ],
];
