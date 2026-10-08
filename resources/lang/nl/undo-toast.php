<?php

declare(strict_types=1);

return [
    'messages' => [
        'delete' => '{1} :Label verwijderd|[0,*] :count :plural_label verwijderd',
        'restore' => '{1} :Label hersteld|[0,*] :count :plural_label hersteld',
        'edit' => '{1} :Label bijgewerkt|[0,*] :count :plural_label bijgewerkt',
        'detach' => '{1} :Label ontkoppeld|[0,*] :count :plural_label ontkoppeld',
        'custom' => '{1} :Action: :label|[0,*] :Action: :count :plural_label',
    ],

    'undone' => [
        'delete' => '{1} :Label hersteld|[0,*] :count :plural_label hersteld',
        'restore' => '{1} :Label opnieuw verwijderd|[0,*] :count :plural_label opnieuw verwijderd',
        'edit' => '{1} :Label: wijzigingen ongedaan gemaakt|[0,*] :count :plural_label: wijzigingen ongedaan gemaakt',
        'detach' => '{1} :Label opnieuw gekoppeld|[0,*] :count :plural_label opnieuw gekoppeld',
        'custom' => '{1} Ongedaan gemaakt|[0,*] Ongedaan gemaakt voor :count :plural_label',
    ],

    'actions' => [
        'undo_last' => 'Laatste actie ongedaan maken',
        'undo' => 'Ongedaan maken',
        'dismiss' => 'Sluiten',
        'open' => 'Openen',
    ],

    'states' => [
        'undoing' => 'Bezig met ongedaan maken…',
        'countdown' => 'Je kunt dit nog :seconds seconden ongedaan maken.',
    ],

    'failures' => [
        'title' => 'Ongedaan maken mislukt',
        'unavailable' => 'Ongedaan maken is hier niet beschikbaar.',
        'forbidden' => 'Je mag deze wijziging niet ongedaan maken.',
        'expired' => 'Dit kan niet meer ongedaan worden gemaakt, of het is al ongedaan gemaakt.',
        'missing' => 'De records bestaan niet meer.',
        'conflict' => 'Er bestaat al een record met dezelfde sleutel.',
        'changed_since' => 'Iemand heeft dit record intussen gewijzigd, dus het is gelaten zoals het was.',
        'error' => 'Er ging iets mis bij het ongedaan maken. Er is niets gewijzigd.',
    ],
];
