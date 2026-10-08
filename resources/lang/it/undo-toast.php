<?php

declare(strict_types=1);

return [
    'messages' => [
        'delete' => '{1} Record eliminato (:label)|[0,*] :count record eliminati (:plural_label)',
        'restore' => '{1} Record ripristinato (:label)|[0,*] :count record ripristinati (:plural_label)',
        'edit' => '{1} Record aggiornato (:label)|[0,*] :count record aggiornati (:plural_label)',
        'detach' => '{1} Record scollegato (:label)|[0,*] :count record scollegati (:plural_label)',
        'custom' => '{1} :Action: :label|[0,*] :Action: :count :plural_label',
    ],

    'undone' => [
        'delete' => '{1} Record ripristinato (:label)|[0,*] :count record ripristinati (:plural_label)',
        'restore' => '{1} Record eliminato di nuovo (:label)|[0,*] :count record eliminati di nuovo (:plural_label)',
        'edit' => '{1} Modifiche annullate (:label)|[0,*] Modifiche annullate su :count :plural_label',
        'detach' => '{1} Record ricollegato (:label)|[0,*] :count record ricollegati (:plural_label)',
        'custom' => '{1} Azione annullata|[0,*] Azione annullata per :count :plural_label',
    ],

    'actions' => [
        'undo_last' => 'Annulla l’ultima azione',
        'undo' => 'Annulla',
        'dismiss' => 'Chiudi',
        'open' => 'Apri',
    ],

    'states' => [
        'undoing' => 'Annullamento…',
        'countdown' => 'Puoi annullare entro :seconds secondi.',
    ],

    'failures' => [
        'title' => 'Impossibile annullare',
        'unavailable' => 'L’annullamento non è disponibile qui.',
        'forbidden' => 'Non puoi annullare questa modifica.',
        'expired' => 'Il tempo per annullare è scaduto o l’azione è già stata annullata.',
        'missing' => 'I record non esistono più.',
        'conflict' => 'Esiste già un record con la stessa chiave.',
        'changed_since' => 'Qualcuno ha modificato questo record nel frattempo, quindi è stato lasciato invariato.',
        'error' => 'Si è verificato un errore durante l’annullamento. Non è stato modificato nulla.',
    ],
];
