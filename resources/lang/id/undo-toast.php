<?php

declare(strict_types=1);

return [
    'messages' => [
        'delete' => '{1} :Label dihapus|[0,*] :count :plural_label dihapus',
        'restore' => '{1} :Label dipulihkan|[0,*] :count :plural_label dipulihkan',
        'edit' => '{1} :Label diperbarui|[0,*] :count :plural_label diperbarui',
        'detach' => '{1} :Label dilepaskan|[0,*] :count :plural_label dilepaskan',
        'custom' => '{1} :Action: :label|[0,*] :Action: :count :plural_label',
    ],

    'undone' => [
        'delete' => '{1} :Label dipulihkan|[0,*] :count :plural_label dipulihkan',
        'restore' => '{1} :Label dihapus lagi|[0,*] :count :plural_label dihapus lagi',
        'edit' => '{1} Perubahan pada :label dibatalkan|[0,*] Perubahan pada :count :plural_label dibatalkan',
        'detach' => '{1} :Label dilampirkan lagi|[0,*] :count :plural_label dilampirkan lagi',
        'custom' => '{1} Dibatalkan|[0,*] Dibatalkan untuk :count :plural_label',
    ],

    'actions' => [
        'undo_last' => 'Batalkan tindakan terakhir',
        'undo' => 'Batalkan',
        'dismiss' => 'Tutup',
        'open' => 'Buka',
    ],

    'states' => [
        'undoing' => 'Membatalkan…',
        'countdown' => 'Anda dapat membatalkannya dalam :seconds detik.',
    ],

    'failures' => [
        'title' => 'Tidak dapat dibatalkan',
        'unavailable' => 'Pembatalan tidak tersedia di sini.',
        'forbidden' => 'Anda tidak diizinkan membatalkan perubahan ini.',
        'expired' => 'Waktu pembatalan sudah habis atau sudah digunakan.',
        'missing' => 'Data tersebut sudah tidak ada.',
        'conflict' => 'Data dengan kunci yang sama sudah ada.',
        'changed_since' => 'Seseorang telah mengubah data ini, jadi data dibiarkan apa adanya.',
        'error' => 'Terjadi kesalahan saat membatalkan. Tidak ada yang diubah.',
    ],
];
