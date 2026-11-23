<?php

return [
    'title' => '기능',
    'global_description' => '패널 전체에서 Shoebill 기능을 켜거나 끕니다. 노드와 서버는 각자의 기능 탭에서 이 설정을 덮어쓸 수 있습니다.',
    'overridden' => '노드 :nodes개, 서버 :servers개에서 덮어쓰고 있습니다.',
    'nav_title' => '기능',
    'inherit' => '상속',
    'on' => '켜기',
    'off' => '끄기',
    'saved' => '저장했습니다',
    'features' => [
        'status_page' => [
            'label' => '상태 페이지',
            'description' => '서버별 평균, 가동률, 그래프.',
        ],
        'player_history' => [
            'label' => '플레이어 기록',
            'description' => '게임 쿼리로 확인된 플레이어를 기록합니다.',
        ],
        'crash_alerts' => [
            'label' => '충돌 알림',
            'description' => '서버가 스스로 멈추면 소유자에게 알립니다.',
        ],
    ],
];
