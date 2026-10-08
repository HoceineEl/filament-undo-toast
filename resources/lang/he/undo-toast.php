<?php

declare(strict_types=1);

return [
    'messages' => [
        'delete' => '{1} הרשומה נמחקה (:label)|[0,*] נמחקו :count :plural_label',
        'restore' => '{1} הרשומה שוחזרה (:label)|[0,*] שוחזרו :count :plural_label',
        'edit' => '{1} הרשומה עודכנה (:label)|[0,*] עודכנו :count :plural_label',
        'detach' => '{1} הקישור לרשומה הוסר (:label)|[0,*] הוסר הקישור ל־:count :plural_label',
        'custom' => '{1} :Action: :label|[0,*] :Action: :count :plural_label',
    ],

    'undone' => [
        'delete' => '{1} הרשומה שוחזרה (:label)|[0,*] שוחזרו :count :plural_label',
        'restore' => '{1} הרשומה נמחקה שוב (:label)|[0,*] נמחקו שוב :count :plural_label',
        'edit' => '{1} השינויים בוטלו (:label)|[0,*] בוטלו השינויים ב־:count :plural_label',
        'detach' => '{1} הרשומה קושרה שוב (:label)|[0,*] קושרו שוב :count :plural_label',
        'custom' => '{1} הפעולה בוטלה|[0,*] הפעולה בוטלה עבור :count :plural_label',
    ],

    'actions' => [
        'undo_last' => 'ביטול הפעולה האחרונה',
        'undo' => 'ביטול',
        'dismiss' => 'סגירה',
        'open' => 'פתיחה',
    ],

    'states' => [
        'undoing' => 'הביטול מתבצע…',
        'countdown' => 'שניות שנותרו לביטול: :seconds.',
    ],

    'failures' => [
        'title' => 'לא ניתן לבטל',
        'unavailable' => 'אי אפשר לבטל כאן.',
        'forbidden' => 'אין לך הרשאה לבטל את השינוי הזה.',
        'expired' => 'הזמן לביטול עבר, או שהפעולה כבר בוטלה.',
        'missing' => 'הרשומות כבר לא קיימות.',
        'conflict' => 'כבר קיימת רשומה עם אותו מפתח.',
        'changed_since' => 'מישהו שינה את הרשומה בינתיים, ולכן היא נשארה כפי שהייתה.',
        'error' => 'אירעה שגיאה במהלך הביטול. שום דבר לא השתנה.',
    ],
];
