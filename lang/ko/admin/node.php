<?php

return [
    'simulated' => '테스트 모드 (Wings 없음)',
    'simulated_help' => '패널이 이 노드 대신 응답하므로 Wings 없이도 서버를 만들고 포트와 IP를 추가할 수 있습니다. 서버는 실제로 실행되지 않습니다. 실제 Wings를 연결하면 끄세요.',
    'daemon_type' => 'Wings 종류',
    'daemon_type_help' => '이 노드에서 실행되는 데몬입니다. Pterodactyl Wings도 사용할 수 있지만 진단, 설치 로그, 하위 폴더 파일 검색, 상태 알림이 없어 패널이 대신 처리합니다.',
    'pterodactyl' => [
        'no_install_logs' => 'Pterodactyl Wings는 설치 로그를 저장하지 않습니다. 서버 콘솔에서 확인하세요.',
        'no_kept_backups' => 'Pterodactyl Wings는 삭제된 서버의 백업을 삭제할 수 없습니다. 노드의 백업 폴더에서 파일을 직접 삭제하세요.',
    ],
];
