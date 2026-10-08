<?php

declare(strict_types=1);

return [
    'messages' => [
        'delete' => '{1} Záznam smazán (:label)|Smazány :count záznamy (:plural_label)|Smazáno :count záznamů (:plural_label)',
        'restore' => '{1} Záznam obnoven (:label)|Obnoveny :count záznamy (:plural_label)|Obnoveno :count záznamů (:plural_label)',
        'edit' => '{1} Záznam aktualizován (:label)|Aktualizovány :count záznamy (:plural_label)|Aktualizováno :count záznamů (:plural_label)',
        'detach' => '{1} Záznam odebrán (:label)|Odebrány :count záznamy (:plural_label)|Odebráno :count záznamů (:plural_label)',
        'custom' => '{1} :Action: :label|:Action: :count záznamy (:plural_label)|:Action: :count záznamů (:plural_label)',
    ],

    'undone' => [
        'delete' => '{1} Záznam obnoven (:label)|Obnoveny :count záznamy (:plural_label)|Obnoveno :count záznamů (:plural_label)',
        'restore' => '{1} Záznam znovu smazán (:label)|Znovu smazány :count záznamy (:plural_label)|Znovu smazáno :count záznamů (:plural_label)',
        'edit' => '{1} Změny vráceny zpět (:label)|Vráceny změny u :count záznamů (:plural_label)|Vráceny změny u :count záznamů (:plural_label)',
        'detach' => '{1} Záznam znovu přidán (:label)|Znovu přidány :count záznamy (:plural_label)|Znovu přidáno :count záznamů (:plural_label)',
        'custom' => '{1} Vráceno zpět|Vráceno zpět u :count záznamů (:plural_label)|Vráceno zpět u :count záznamů (:plural_label)',
    ],

    'actions' => [
        'undo_last' => 'Vrátit zpět poslední akci',
        'undo' => 'Vrátit zpět',
        'dismiss' => 'Zavřít',
        'open' => 'Otevřít',
    ],

    'states' => [
        'undoing' => 'Vrací se zpět…',
        'countdown' => 'Zbývá sekund na vrácení: :seconds.',
    ],

    'failures' => [
        'title' => 'Vrácení zpět se nezdařilo',
        'unavailable' => 'Vrácení zpět zde není dostupné.',
        'forbidden' => 'Tuto změnu nemůžete vrátit zpět.',
        'expired' => 'Čas na vrácení vypršel, nebo už byla změna vrácena.',
        'missing' => 'Tyto záznamy už neexistují.',
        'conflict' => 'Záznam se stejným klíčem už existuje.',
        'changed_since' => 'Někdo mezitím tento záznam změnil, proto zůstal beze změny.',
        'error' => 'Při vracení zpět došlo k chybě. Nic se nezměnilo.',
    ],
];
