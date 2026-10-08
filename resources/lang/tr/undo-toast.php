<?php

declare(strict_types=1);

return [
    'messages' => [
        'delete' => '{1} :Label silindi|[0,*] :count kayıt silindi (:plural_label)',
        'restore' => '{1} :Label geri yüklendi|[0,*] :count kayıt geri yüklendi (:plural_label)',
        'edit' => '{1} :Label güncellendi|[0,*] :count kayıt güncellendi (:plural_label)',
        'detach' => '{1} :Label ayrıldı|[0,*] :count kayıt ayrıldı (:plural_label)',
        'custom' => '{1} :Action: :label|[0,*] :Action: :count kayıt (:plural_label)',
    ],

    'undone' => [
        'delete' => '{1} :Label geri yüklendi|[0,*] :count kayıt geri yüklendi (:plural_label)',
        'restore' => '{1} :Label yeniden silindi|[0,*] :count kayıt yeniden silindi (:plural_label)',
        'edit' => '{1} :Label üzerindeki değişiklikler geri alındı|[0,*] :count kayıttaki değişiklikler geri alındı (:plural_label)',
        'detach' => '{1} :Label yeniden iliştirildi|[0,*] :count kayıt yeniden iliştirildi (:plural_label)',
        'custom' => '{1} Geri alındı|[0,*] :count kayıt için geri alındı (:plural_label)',
    ],

    'actions' => [
        'undo_last' => 'Son işlemi geri al',
        'undo' => 'Geri al',
        'dismiss' => 'Kapat',
        'open' => 'Aç',
    ],

    'states' => [
        'undoing' => 'Geri alınıyor…',
        'countdown' => ':seconds saniye içinde geri alabilirsiniz.',
    ],

    'failures' => [
        'title' => 'Geri alınamadı',
        'unavailable' => 'Burada geri alma kullanılamıyor.',
        'forbidden' => 'Bu değişikliği geri alma yetkiniz yok.',
        'expired' => 'Geri alma süresi doldu veya zaten kullanıldı.',
        'missing' => 'Bu kayıtlar artık mevcut değil.',
        'conflict' => 'Aynı anahtara sahip bir kayıt zaten var.',
        'changed_since' => 'Bu kayıt bu arada başka biri tarafından değiştirildi, bu yüzden olduğu gibi bırakıldı.',
        'error' => 'Geri alınırken bir hata oluştu. Hiçbir şey değiştirilmedi.',
    ],
];
