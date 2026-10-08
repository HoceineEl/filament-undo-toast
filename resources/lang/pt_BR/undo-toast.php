<?php

declare(strict_types=1);

return [
    'messages' => [
        'delete' => '{1} Registro excluído (:label)|[0,*] :count registros excluídos (:plural_label)',
        'restore' => '{1} Registro restaurado (:label)|[0,*] :count registros restaurados (:plural_label)',
        'edit' => '{1} Registro atualizado (:label)|[0,*] :count registros atualizados (:plural_label)',
        'detach' => '{1} Registro desvinculado (:label)|[0,*] :count registros desvinculados (:plural_label)',
        'custom' => '{1} :Action: :label|[0,*] :Action: :count :plural_label',
    ],

    'undone' => [
        'delete' => '{1} Registro restaurado (:label)|[0,*] :count registros restaurados (:plural_label)',
        'restore' => '{1} Registro excluído novamente (:label)|[0,*] :count registros excluídos novamente (:plural_label)',
        'edit' => '{1} Alterações desfeitas (:label)|[0,*] Alterações desfeitas em :count :plural_label',
        'detach' => '{1} Registro vinculado novamente (:label)|[0,*] :count registros vinculados novamente (:plural_label)',
        'custom' => '{1} Ação desfeita|[0,*] Ação desfeita em :count :plural_label',
    ],

    'actions' => [
        'undo_last' => 'Desfazer a última ação',
        'undo' => 'Desfazer',
        'dismiss' => 'Fechar',
        'open' => 'Abrir',
    ],

    'states' => [
        'undoing' => 'Desfazendo…',
        'countdown' => 'Você pode desfazer em até :seconds segundos.',
    ],

    'failures' => [
        'title' => 'Não foi possível desfazer',
        'unavailable' => 'Não é possível desfazer aqui.',
        'forbidden' => 'Você não tem permissão para desfazer esta alteração.',
        'expired' => 'O prazo para desfazer acabou ou a ação já foi desfeita.',
        'missing' => 'Os registros não existem mais.',
        'conflict' => 'Já existe um registro com a mesma chave.',
        'changed_since' => 'Alguém alterou este registro nesse meio-tempo, então ele foi mantido como estava.',
        'error' => 'Ocorreu um erro ao desfazer. Nada foi alterado.',
    ],
];
