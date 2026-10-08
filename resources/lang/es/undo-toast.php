<?php

declare(strict_types=1);

return [
    'messages' => [
        'delete' => '{1} Se borró :count :label|[0,*] Se borraron :count :plural_label',
        'restore' => '{1} Se restauró :count :label|[0,*] Se restauraron :count :plural_label',
        'edit' => '{1} Se actualizó :count :label|[0,*] Se actualizaron :count :plural_label',
        'detach' => '{1} Se desvinculó :count :label|[0,*] Se desvincularon :count :plural_label',
        'custom' => '{1} :Action: :label|[0,*] :Action: :count :plural_label',
    ],

    'undone' => [
        'delete' => '{1} Se restauró :count :label|[0,*] Se restauraron :count :plural_label',
        'restore' => '{1} Se volvió a borrar :count :label|[0,*] Se volvieron a borrar :count :plural_label',
        'edit' => '{1} Se deshicieron los cambios en :count :label|[0,*] Se deshicieron los cambios en :count :plural_label',
        'detach' => '{1} Se volvió a vincular :count :label|[0,*] Se volvieron a vincular :count :plural_label',
        'custom' => '{1} Acción deshecha|[0,*] Acción deshecha en :count :plural_label',
    ],

    'actions' => [
        'undo_last' => 'Deshacer la última acción',
        'undo' => 'Deshacer',
        'dismiss' => 'Cerrar',
        'open' => 'Abrir',
    ],

    'states' => [
        'undoing' => 'Deshaciendo…',
        'countdown' => 'Puede deshacerlo durante :seconds segundos.',
    ],

    'failures' => [
        'title' => 'No se pudo deshacer',
        'unavailable' => 'No se puede deshacer aquí.',
        'forbidden' => 'No tiene permiso para deshacer este cambio.',
        'expired' => 'El plazo para deshacer ha vencido o ya se ha deshecho.',
        'missing' => 'Los registros ya no existen.',
        'conflict' => 'Ya existe un registro con la misma clave.',
        'changed_since' => 'Alguien modificó este registro mientras tanto, así que se dejó como estaba.',
        'error' => 'Se produjo un error al deshacer. No se modificó nada.',
    ],
];
