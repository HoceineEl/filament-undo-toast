<?php

declare(strict_types=1);

return [
    'messages' => [
        'delete' => '{1} Đã xóa :label|[0,*] Đã xóa :count :plural_label',
        'restore' => '{1} Đã khôi phục :label|[0,*] Đã khôi phục :count :plural_label',
        'edit' => '{1} Đã cập nhật :label|[0,*] Đã cập nhật :count :plural_label',
        'detach' => '{1} Đã tách :label|[0,*] Đã tách :count :plural_label',
        'custom' => '{1} :Action: :label|[0,*] :Action: :count :plural_label',
    ],

    'undone' => [
        'delete' => '{1} Đã khôi phục :label|[0,*] Đã khôi phục :count :plural_label',
        'restore' => '{1} Đã xóa lại :label|[0,*] Đã xóa lại :count :plural_label',
        'edit' => '{1} Đã hoàn tác thay đổi của :label|[0,*] Đã hoàn tác thay đổi của :count :plural_label',
        'detach' => '{1} Đã đính kèm lại :label|[0,*] Đã đính kèm lại :count :plural_label',
        'custom' => '{1} Đã hoàn tác|[0,*] Đã hoàn tác cho :count :plural_label',
    ],

    'actions' => [
        'undo_last' => 'Hoàn tác thao tác gần nhất',
        'undo' => 'Hoàn tác',
        'dismiss' => 'Đóng',
        'open' => 'Mở',
    ],

    'states' => [
        'undoing' => 'Đang hoàn tác…',
        'countdown' => 'Bạn có thể hoàn tác trong :seconds giây.',
    ],

    'failures' => [
        'title' => 'Không thể hoàn tác',
        'unavailable' => 'Không hỗ trợ hoàn tác ở đây.',
        'forbidden' => 'Bạn không có quyền hoàn tác thay đổi này.',
        'expired' => 'Đã hết thời gian hoàn tác hoặc thao tác đã được hoàn tác.',
        'missing' => 'Các bản ghi này không còn tồn tại.',
        'conflict' => 'Đã có bản ghi với cùng khóa.',
        'changed_since' => 'Ai đó đã thay đổi bản ghi này trong lúc đó, nên bản ghi được giữ nguyên.',
        'error' => 'Đã xảy ra lỗi khi hoàn tác. Không có gì bị thay đổi.',
    ],
];
