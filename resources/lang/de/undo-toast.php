<?php

declare(strict_types=1);

return [
    'messages' => [
        'delete' => '{1} :Label gelöscht|[0,*] :count :plural_label gelöscht',
        'restore' => '{1} :Label wiederhergestellt|[0,*] :count :plural_label wiederhergestellt',
        'edit' => '{1} :Label aktualisiert|[0,*] :count :plural_label aktualisiert',
        'detach' => '{1} :Label getrennt|[0,*] :count :plural_label getrennt',
        'custom' => '{1} :Action: :label|[0,*] :Action: :count :plural_label',
    ],

    'undone' => [
        'delete' => '{1} :Label wiederhergestellt|[0,*] :count :plural_label wiederhergestellt',
        'restore' => '{1} :Label erneut gelöscht|[0,*] :count :plural_label erneut gelöscht',
        'edit' => '{1} :Label: Änderungen rückgängig gemacht|[0,*] :count :plural_label: Änderungen rückgängig gemacht',
        'detach' => '{1} :Label erneut verknüpft|[0,*] :count :plural_label erneut verknüpft',
        'custom' => '{1} Rückgängig gemacht|[0,*] Für :count :plural_label rückgängig gemacht',
    ],

    'actions' => [
        'undo_last' => 'Letzte Aktion rückgängig machen',
        'undo' => 'Rückgängig',
        'dismiss' => 'Schließen',
        'open' => 'Öffnen',
    ],

    'states' => [
        'undoing' => 'Wird rückgängig gemacht…',
        'countdown' => 'Sie können dies :seconds Sekunden lang rückgängig machen.',
    ],

    'failures' => [
        'title' => 'Rückgängigmachen fehlgeschlagen',
        'unavailable' => 'Rückgängigmachen ist hier nicht verfügbar.',
        'forbidden' => 'Sie dürfen diese Änderung nicht rückgängig machen.',
        'expired' => 'Die Frist zum Rückgängigmachen ist abgelaufen, oder es wurde bereits rückgängig gemacht.',
        'missing' => 'Die Datensätze sind nicht mehr vorhanden.',
        'conflict' => 'Ein Datensatz mit demselben Schlüssel ist bereits vorhanden.',
        'changed_since' => 'Jemand hat diesen Datensatz inzwischen geändert, daher wurde er unverändert gelassen.',
        'error' => 'Beim Rückgängigmachen ist ein Fehler aufgetreten. Es wurde nichts geändert.',
    ],
];
