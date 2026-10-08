<?php

declare(strict_types=1);

return [
    'messages' => [
        'delete' => '{1} Élément supprimé (:label)|[0,*] :count éléments supprimés (:plural_label)',
        'restore' => '{1} Élément restauré (:label)|[0,*] :count éléments restaurés (:plural_label)',
        'edit' => '{1} Élément modifié (:label)|[0,*] :count éléments modifiés (:plural_label)',
        'detach' => '{1} Élément détaché (:label)|[0,*] :count éléments détachés (:plural_label)',
        'custom' => '{1} :Action : :label|[0,*] :Action : :count :plural_label',
    ],

    'undone' => [
        'delete' => '{1} Élément restauré (:label)|[0,*] :count éléments restaurés (:plural_label)',
        'restore' => '{1} Élément de nouveau supprimé (:label)|[0,*] :count éléments de nouveau supprimés (:plural_label)',
        'edit' => '{1} Modifications annulées (:label)|[0,*] Modifications annulées sur :count :plural_label',
        'detach' => '{1} Élément de nouveau attaché (:label)|[0,*] :count éléments de nouveau attachés (:plural_label)',
        'custom' => '{1} Action annulée|[0,*] Action annulée pour :count :plural_label',
    ],

    'actions' => [
        'undo_last' => 'Annuler la dernière action',
        'undo' => 'Annuler',
        'dismiss' => 'Fermer',
        'open' => 'Ouvrir',
    ],

    'states' => [
        'undoing' => 'Annulation…',
        'countdown' => 'Vous pouvez annuler pendant :seconds secondes.',
    ],

    'failures' => [
        'title' => 'Annulation impossible',
        'unavailable' => 'L’annulation n’est pas disponible ici.',
        'forbidden' => 'Vous ne pouvez pas annuler cette modification.',
        'expired' => 'Le délai d’annulation est dépassé ou l’annulation a déjà été faite.',
        'missing' => 'Les enregistrements à rétablir n’existent plus.',
        'conflict' => 'Un enregistrement avec la même clé existe déjà.',
        'changed_since' => 'Quelqu’un a modifié cet enregistrement entre-temps ; il a été laissé tel quel.',
        'error' => 'Une erreur est survenue pendant l’annulation. Rien n’a été modifié.',
    ],
];
