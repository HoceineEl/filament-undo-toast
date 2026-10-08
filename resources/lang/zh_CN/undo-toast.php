<?php

declare(strict_types=1);

return [
    'messages' => [
        'delete' => '{1} 已删除:label|[0,*] 已删除 :count 个:plural_label',
        'restore' => '{1} 已恢复:label|[0,*] 已恢复 :count 个:plural_label',
        'edit' => '{1} 已更新:label|[0,*] 已更新 :count 个:plural_label',
        'detach' => '{1} 已分离:label|[0,*] 已分离 :count 个:plural_label',
        'custom' => '{1} :Action：:label|[0,*] :Action：:count 个:plural_label',
    ],

    'undone' => [
        'delete' => '{1} 已恢复:label|[0,*] 已恢复 :count 个:plural_label',
        'restore' => '{1} 已再次删除:label|[0,*] 已再次删除 :count 个:plural_label',
        'edit' => '{1} 已撤销对:label的更改|[0,*] 已撤销对 :count 个:plural_label的更改',
        'detach' => '{1} 已重新附加:label|[0,*] 已重新附加 :count 个:plural_label',
        'custom' => '{1} 已撤销|[0,*] 已撤销对 :count 个:plural_label的操作',
    ],

    'actions' => [
        'undo_last' => '撤销上一步操作',
        'undo' => '撤销',
        'dismiss' => '关闭',
        'open' => '打开',
    ],

    'states' => [
        'undoing' => '正在撤销…',
        'countdown' => '您可以在 :seconds 秒内撤销。',
    ],

    'failures' => [
        'title' => '无法撤销',
        'unavailable' => '此处不支持撤销。',
        'forbidden' => '您无权撤销此更改。',
        'expired' => '撤销时限已过，或已撤销过。',
        'missing' => '这些记录已不存在。',
        'conflict' => '已存在相同键的记录。',
        'changed_since' => '此记录已被他人修改，因此保持不变。',
        'error' => '撤销时出错，未做任何更改。',
    ],
];
