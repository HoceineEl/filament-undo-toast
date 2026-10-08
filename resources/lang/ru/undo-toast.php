<?php

declare(strict_types=1);

return [
    'messages' => [
        'delete' => 'Удалена :count запись (:plural_label)|Удалены :count записи (:plural_label)|Удалено :count записей (:plural_label)|{1} Запись удалена (:label)',
        'restore' => 'Восстановлена :count запись (:plural_label)|Восстановлены :count записи (:plural_label)|Восстановлено :count записей (:plural_label)|{1} Запись восстановлена (:label)',
        'edit' => 'Обновлена :count запись (:plural_label)|Обновлены :count записи (:plural_label)|Обновлено :count записей (:plural_label)|{1} Запись обновлена (:label)',
        'detach' => 'Откреплена :count запись (:plural_label)|Откреплены :count записи (:plural_label)|Откреплено :count записей (:plural_label)|{1} Запись откреплена (:label)',
        'custom' => ':Action: :count запись (:plural_label)|:Action: :count записи (:plural_label)|:Action: :count записей (:plural_label)|{1} :Action: :label',
    ],

    'undone' => [
        'delete' => 'Восстановлена :count запись (:plural_label)|Восстановлены :count записи (:plural_label)|Восстановлено :count записей (:plural_label)|{1} Запись восстановлена (:label)',
        'restore' => 'Снова удалена :count запись (:plural_label)|Снова удалены :count записи (:plural_label)|Снова удалено :count записей (:plural_label)|{1} Запись снова удалена (:label)',
        'edit' => 'Отменены изменения в :count записи (:plural_label)|Отменены изменения в :count записях (:plural_label)|Отменены изменения в :count записях (:plural_label)|{1} Изменения отменены (:label)',
        'detach' => 'Снова прикреплена :count запись (:plural_label)|Снова прикреплены :count записи (:plural_label)|Снова прикреплено :count записей (:plural_label)|{1} Запись снова прикреплена (:label)',
        'custom' => 'Отменено для :count записи (:plural_label)|Отменено для :count записей (:plural_label)|Отменено для :count записей (:plural_label)|{1} Действие отменено',
    ],

    'actions' => [
        'undo_last' => 'Отменить последнее действие',
        'undo' => 'Отменить',
        'dismiss' => 'Закрыть',
        'open' => 'Открыть',
    ],

    'states' => [
        'undoing' => 'Отмена…',
        'countdown' => 'Осталось секунд для отмены: :seconds.',
    ],

    'failures' => [
        'title' => 'Не удалось отменить',
        'unavailable' => 'Отмена здесь недоступна.',
        'forbidden' => 'У вас нет прав на отмену этого изменения.',
        'expired' => 'Время на отмену истекло, или действие уже отменено.',
        'missing' => 'Этих записей больше нет.',
        'conflict' => 'Запись с таким же ключом уже существует.',
        'changed_since' => 'Кто-то уже изменил эту запись, поэтому она оставлена как есть.',
        'error' => 'При отмене произошла ошибка. Ничего не изменено.',
    ],
];
