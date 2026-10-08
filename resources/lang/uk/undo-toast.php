<?php

declare(strict_types=1);

return [
    'messages' => [
        'delete' => 'Видалено :count запис (:plural_label)|Видалено :count записи (:plural_label)|Видалено :count записів (:plural_label)|{1} Запис видалено (:label)',
        'restore' => 'Відновлено :count запис (:plural_label)|Відновлено :count записи (:plural_label)|Відновлено :count записів (:plural_label)|{1} Запис відновлено (:label)',
        'edit' => 'Оновлено :count запис (:plural_label)|Оновлено :count записи (:plural_label)|Оновлено :count записів (:plural_label)|{1} Запис оновлено (:label)',
        'detach' => 'Від’єднано :count запис (:plural_label)|Від’єднано :count записи (:plural_label)|Від’єднано :count записів (:plural_label)|{1} Запис від’єднано (:label)',
        'custom' => ':Action: :count запис (:plural_label)|:Action: :count записи (:plural_label)|:Action: :count записів (:plural_label)|{1} :Action: :label',
    ],

    'undone' => [
        'delete' => 'Відновлено :count запис (:plural_label)|Відновлено :count записи (:plural_label)|Відновлено :count записів (:plural_label)|{1} Запис відновлено (:label)',
        'restore' => 'Знову видалено :count запис (:plural_label)|Знову видалено :count записи (:plural_label)|Знову видалено :count записів (:plural_label)|{1} Запис знову видалено (:label)',
        'edit' => 'Скасовано зміни в :count записі (:plural_label)|Скасовано зміни в :count записах (:plural_label)|Скасовано зміни в :count записах (:plural_label)|{1} Зміни скасовано (:label)',
        'detach' => 'Знову прикріплено :count запис (:plural_label)|Знову прикріплено :count записи (:plural_label)|Знову прикріплено :count записів (:plural_label)|{1} Запис знову прикріплено (:label)',
        'custom' => 'Скасовано для :count запису (:plural_label)|Скасовано для :count записів (:plural_label)|Скасовано для :count записів (:plural_label)|{1} Дію скасовано',
    ],

    'actions' => [
        'undo_last' => 'Скасувати останню дію',
        'undo' => 'Скасувати',
        'dismiss' => 'Закрити',
        'open' => 'Відкрити',
    ],

    'states' => [
        'undoing' => 'Скасування…',
        'countdown' => 'Залишилося секунд для скасування: :seconds.',
    ],

    'failures' => [
        'title' => 'Не вдалося скасувати',
        'unavailable' => 'Скасування тут недоступне.',
        'forbidden' => 'У вас немає прав скасувати цю зміну.',
        'expired' => 'Час на скасування минув, або дію вже скасовано.',
        'missing' => 'Цих записів більше немає.',
        'conflict' => 'Запис із таким самим ключем уже існує.',
        'changed_since' => 'Хтось уже змінив цей запис, тому його залишено без змін.',
        'error' => 'Під час скасування сталася помилка. Нічого не змінено.',
    ],
];
