<?php

declare(strict_types=1);

return [
    'messages' => [
        'delete' => '{1} تم حذف :label|[0,*] تم حذف :count من :plural_label',
        'restore' => '{1} تمت استعادة :label|[0,*] تمت استعادة :count من :plural_label',
        'edit' => '{1} تم تعديل :label|[0,*] تم تعديل :count من :plural_label',
        'detach' => '{1} تم فصل :label|[0,*] تم فصل :count من :plural_label',
        'custom' => '{1} :Action: :label|[0,*] :Action: :count من :plural_label',
    ],

    'undone' => [
        'delete' => '{1} تمت استعادة :label|[0,*] تمت استعادة :count من :plural_label',
        'restore' => '{1} أُعيد حذف :label|[0,*] أُعيد حذف :count من :plural_label',
        'edit' => '{1} أُلغيت تعديلات :label|[0,*] أُلغيت تعديلات :count من :plural_label',
        'detach' => '{1} أُعيد ربط :label|[0,*] أُعيد ربط :count من :plural_label',
        'custom' => '{1} تم التراجع|[0,*] تم التراجع عن :count من :plural_label',
    ],

    'actions' => [
        'undo_last' => 'التراجع عن آخر إجراء',
        'undo' => 'تراجع',
        'dismiss' => 'إغلاق',
        'open' => 'فتح',
    ],

    'states' => [
        'undoing' => 'جارٍ التراجع…',
        'countdown' => 'الثواني المتبقية للتراجع: :seconds.',
    ],

    'failures' => [
        'title' => 'تعذّر التراجع',
        'unavailable' => 'التراجع غير متاح هنا.',
        'forbidden' => 'لا تملك صلاحية التراجع عن هذا التغيير.',
        'expired' => 'انتهت مهلة التراجع أو استُخدمت من قبل.',
        'missing' => 'لم تعد هذه السجلات موجودة، فتعذّر التراجع.',
        'conflict' => 'يوجد سجل آخر بالمعرّف نفسه، فلم نستعد السجل.',
        'changed_since' => 'عدّل شخص آخر هذا السجل في هذه الأثناء، فتركناه كما هو.',
        'error' => 'حدث خطأ أثناء التراجع، ولم يتغيّر شيء.',
    ],
];
