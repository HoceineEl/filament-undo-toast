<?php

declare(strict_types=1);

return [
    'messages' => [
        'delete' => '{1} :label حذف شد|[0,*] :count مورد از :plural_label حذف شد',
        'restore' => '{1} :label بازگردانی شد|[0,*] :count مورد از :plural_label بازگردانی شد',
        'edit' => '{1} :label به‌روزرسانی شد|[0,*] :count مورد از :plural_label به‌روزرسانی شد',
        'detach' => '{1} :label جدا شد|[0,*] :count مورد از :plural_label جدا شد',
        'custom' => '{1} :Action: :label|[0,*] :Action: :count مورد از :plural_label',
    ],

    'undone' => [
        'delete' => '{1} :label بازگردانی شد|[0,*] :count مورد از :plural_label بازگردانی شد',
        'restore' => '{1} :label دوباره حذف شد|[0,*] :count مورد از :plural_label دوباره حذف شد',
        'edit' => '{1} تغییرات :label واگرد شد|[0,*] تغییرات :count مورد از :plural_label واگرد شد',
        'detach' => '{1} :label دوباره افزوده شد|[0,*] :count مورد از :plural_label دوباره افزوده شد',
        'custom' => '{1} واگرد انجام شد|[0,*] واگرد برای :count مورد از :plural_label انجام شد',
    ],

    'actions' => [
        'undo_last' => 'واگرد آخرین کار',
        'undo' => 'واگرد',
        'dismiss' => 'بستن',
        'open' => 'باز کردن',
    ],

    'states' => [
        'undoing' => 'در حال واگرد…',
        'countdown' => 'تا :seconds ثانیه می‌توانید واگرد کنید.',
    ],

    'failures' => [
        'title' => 'واگرد انجام نشد',
        'unavailable' => 'واگرد در اینجا در دسترس نیست.',
        'forbidden' => 'اجازهٔ واگرد این تغییر را ندارید.',
        'expired' => 'مهلت واگرد تمام شده یا قبلاً انجام شده است.',
        'missing' => 'این رکوردها دیگر وجود ندارند.',
        'conflict' => 'رکوردی با همین کلید از قبل وجود دارد.',
        'changed_since' => 'شخص دیگری در این فاصله این رکورد را تغییر داده است، بنابراین دست‌نخورده ماند.',
        'error' => 'هنگام واگرد خطایی رخ داد. هیچ چیزی تغییر نکرد.',
    ],
];
