<?php

declare(strict_types=1);

return [
    'messages' => [
        'delete' => '{1} Registo eliminado (:label)|[0,*] :count registos eliminados (:plural_label)',
        'restore' => '{1} Registo restaurado (:label)|[0,*] :count registos restaurados (:plural_label)',
        'edit' => '{1} Registo atualizado (:label)|[0,*] :count registos atualizados (:plural_label)',
        'detach' => '{1} Registo desvinculado (:label)|[0,*] :count registos desvinculados (:plural_label)',
        'custom' => '{1} :Action: :label|[0,*] :Action: :count :plural_label',
    ],

    'undone' => [
        'delete' => '{1} Registo restaurado (:label)|[0,*] :count registos restaurados (:plural_label)',
        'restore' => '{1} Registo eliminado novamente (:label)|[0,*] :count registos eliminados novamente (:plural_label)',
        'edit' => '{1} Alterações desfeitas (:label)|[0,*] Alterações desfeitas em :count :plural_label',
        'detach' => '{1} Registo vinculado novamente (:label)|[0,*] :count registos vinculados novamente (:plural_label)',
        'custom' => '{1} Ação desfeita|[0,*] Ação desfeita em :count :plural_label',
    ],

    'actions' => [
        'undo_last' => 'Desfazer a última ação',
        'undo' => 'Desfazer',
        'dismiss' => 'Fechar',
        'open' => 'Abrir',
    ],

    'states' => [
        'undoing' => 'A desfazer…',
        'countdown' => 'Pode desfazer durante :seconds segundos.',
    ],

    'failures' => [
        'title' => 'Não foi possível desfazer',
        'unavailable' => 'Não é possível desfazer aqui.',
        'forbidden' => 'Não tem permissão para desfazer esta alteração.',
        'expired' => 'O prazo para desfazer terminou ou a ação já foi desfeita.',
        'missing' => 'Os registos já não existem.',
        'conflict' => 'Já existe um registo com a mesma chave.',
        'changed_since' => 'Alguém alterou este registo entretanto, por isso ficou como estava.',
        'error' => 'Ocorreu um erro ao desfazer. Nada foi alterado.',
    ],
];
