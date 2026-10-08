<?php

declare(strict_types=1);

return [
    'messages' => [
        'delete' => '{1} :labelを削除しました|[0,*] :plural_labelを:count件削除しました',
        'restore' => '{1} :labelを復元しました|[0,*] :plural_labelを:count件復元しました',
        'edit' => '{1} :labelを更新しました|[0,*] :plural_labelを:count件更新しました',
        'detach' => '{1} :labelの紐付けを解除しました|[0,*] :plural_label :count件の紐付けを解除しました',
        'custom' => '{1} :Action：:label|[0,*] :Action：:plural_label :count件',
    ],

    'undone' => [
        'delete' => '{1} :labelを復元しました|[0,*] :plural_labelを:count件復元しました',
        'restore' => '{1} :labelを再度削除しました|[0,*] :plural_labelを:count件再度削除しました',
        'edit' => '{1} :labelの変更を元に戻しました|[0,*] :plural_label :count件の変更を元に戻しました',
        'detach' => '{1} :labelを再度紐付けました|[0,*] :plural_labelを:count件再度紐付けました',
        'custom' => '{1} 元に戻しました|[0,*] :plural_label :count件を元に戻しました',
    ],

    'actions' => [
        'undo_last' => '直前の操作を元に戻す',
        'undo' => '元に戻す',
        'dismiss' => '閉じる',
        'open' => '開く',
    ],

    'states' => [
        'undoing' => '元に戻しています…',
        'countdown' => ':seconds秒以内なら元に戻せます。',
    ],

    'failures' => [
        'title' => '元に戻せませんでした',
        'unavailable' => 'ここでは元に戻す操作を利用できません。',
        'forbidden' => 'この変更を元に戻す権限がありません。',
        'expired' => '元に戻せる期限が過ぎたか、すでに元に戻されています。',
        'missing' => '対象のレコードはすでに存在しません。',
        'conflict' => '同じキーのレコードがすでに存在します。',
        'changed_since' => 'このレコードはその後ほかのユーザーが変更したため、そのままにしました。',
        'error' => '元に戻す途中でエラーが発生しました。変更は行われていません。',
    ],
];
