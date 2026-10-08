<?php

declare(strict_types=1);

return [
    'messages' => [
        'delete' => '{1} 已刪除:label|[0,*] 已刪除 :count 筆:plural_label',
        'restore' => '{1} 已還原:label|[0,*] 已還原 :count 筆:plural_label',
        'edit' => '{1} 已更新:label|[0,*] 已更新 :count 筆:plural_label',
        'detach' => '{1} 已解除附加:label|[0,*] 已解除附加 :count 筆:plural_label',
        'custom' => '{1} :Action：:label|[0,*] :Action：:count 筆:plural_label',
    ],

    'undone' => [
        'delete' => '{1} 已還原:label|[0,*] 已還原 :count 筆:plural_label',
        'restore' => '{1} 已再次刪除:label|[0,*] 已再次刪除 :count 筆:plural_label',
        'edit' => '{1} 已復原對:label的變更|[0,*] 已復原對 :count 筆:plural_label的變更',
        'detach' => '{1} 已重新附加:label|[0,*] 已重新附加 :count 筆:plural_label',
        'custom' => '{1} 已復原|[0,*] 已復原對 :count 筆:plural_label的操作',
    ],

    'actions' => [
        'undo_last' => '復原上一個動作',
        'undo' => '復原',
        'dismiss' => '關閉',
        'open' => '開啟',
    ],

    'states' => [
        'undoing' => '正在復原…',
        'countdown' => '您可以在 :seconds 秒內復原。',
    ],

    'failures' => [
        'title' => '無法復原',
        'unavailable' => '此處不支援復原。',
        'forbidden' => '您沒有權限復原此變更。',
        'expired' => '復原期限已過，或已經復原過。',
        'missing' => '這些資料已不存在。',
        'conflict' => '已有相同鍵值的資料。',
        'changed_since' => '這筆資料已被他人修改，因此維持不變。',
        'error' => '復原時發生錯誤，未做任何變更。',
    ],
];
