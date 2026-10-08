<?php

declare(strict_types=1);

return [
    'messages' => [
        'delete' => '{1} :Label deleted|[0,*] Deleted :count :plural_label',
        'restore' => '{1} :Label restored|[0,*] Restored :count :plural_label',
        'edit' => '{1} :Label updated|[0,*] Updated :count :plural_label',
        'detach' => '{1} :Label detached|[0,*] Detached :count :plural_label',
        'custom' => '{1} :Action: :label|[0,*] :Action: :count :plural_label',
    ],

    'undone' => [
        'delete' => '{1} :Label restored|[0,*] Restored :count :plural_label',
        'restore' => '{1} :Label deleted again|[0,*] Deleted :count :plural_label again',
        'edit' => '{1} Changes to the :label undone|[0,*] Changes to :count :plural_label undone',
        'detach' => '{1} :Label attached again|[0,*] Attached :count :plural_label again',
        'custom' => '{1} Undone|[0,*] Undone for :count :plural_label',
    ],

    'actions' => [
        'undo_last' => 'Undo last action',
        'undo' => 'Undo',
        'dismiss' => 'Dismiss',
        'open' => 'Open',
    ],

    'states' => [
        'undoing' => 'Undoing…',
        'countdown' => 'You can undo this for :seconds seconds.',
    ],

    'failures' => [
        'title' => 'Couldn’t undo',
        'unavailable' => 'Undo isn’t available here.',
        'forbidden' => 'You can’t undo this change.',
        'expired' => 'This undo has expired or was already used.',
        'missing' => 'The records are no longer there to undo.',
        'conflict' => 'A record with the same key already exists.',
        'changed_since' => 'Someone changed this record since, so it was left as is.',
        'error' => 'Something went wrong while undoing. Nothing was changed.',
    ],
];
