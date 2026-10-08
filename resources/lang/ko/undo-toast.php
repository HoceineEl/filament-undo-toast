<?php

declare(strict_types=1);

return [
    'messages' => [
        'delete' => '{1} :label 삭제 완료|[0,*] :plural_label :count개 삭제 완료',
        'restore' => '{1} :label 복원 완료|[0,*] :plural_label :count개 복원 완료',
        'edit' => '{1} :label 수정 완료|[0,*] :plural_label :count개 수정 완료',
        'detach' => '{1} :label 분리 완료|[0,*] :plural_label :count개 분리 완료',
        'custom' => '{1} :Action: :label|[0,*] :Action: :plural_label :count개',
    ],

    'undone' => [
        'delete' => '{1} :label 복원 완료|[0,*] :plural_label :count개 복원 완료',
        'restore' => '{1} :label 다시 삭제 완료|[0,*] :plural_label :count개 다시 삭제 완료',
        'edit' => '{1} :label 변경 사항 실행 취소 완료|[0,*] :plural_label :count개 변경 사항 실행 취소 완료',
        'detach' => '{1} :label 다시 추가 완료|[0,*] :plural_label :count개 다시 추가 완료',
        'custom' => '{1} 실행 취소 완료|[0,*] :plural_label :count개 실행 취소 완료',
    ],

    'actions' => [
        'undo_last' => '마지막 작업 실행 취소',
        'undo' => '실행 취소',
        'dismiss' => '닫기',
        'open' => '열기',
    ],

    'states' => [
        'undoing' => '실행 취소 중…',
        'countdown' => ':seconds초 동안 실행 취소할 수 있습니다.',
    ],

    'failures' => [
        'title' => '실행 취소 실패',
        'unavailable' => '여기서는 실행 취소를 사용할 수 없습니다.',
        'forbidden' => '이 변경 사항을 실행 취소할 권한이 없습니다.',
        'expired' => '실행 취소 가능 시간이 지났거나 이미 실행 취소되었습니다.',
        'missing' => '대상 레코드가 더 이상 존재하지 않습니다.',
        'conflict' => '같은 키를 가진 레코드가 이미 있습니다.',
        'changed_since' => '그사이 다른 사용자가 이 레코드를 변경해 그대로 두었습니다.',
        'error' => '실행 취소 중 오류가 발생했습니다. 변경된 내용은 없습니다.',
    ],
];
