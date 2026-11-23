<?php

return [
    'title' => '功能',
    'global_description' => '为整个面板开启或关闭 Shoebill 功能。节点和服务器可以在各自的“功能”标签页中覆盖此设置。',
    'overridden' => '已在 :nodes 个节点和 :servers 台服务器上覆盖。',
    'nav_title' => '功能',
    'inherit' => '继承',
    'on' => '开',
    'off' => '关',
    'saved' => '已保存',
    'features' => [
        'status_page' => [
            'label' => '状态页',
            'description' => '每台服务器的平均值、在线率和图表。',
        ],
        'player_history' => [
            'label' => '玩家历史',
            'description' => '记录游戏查询发现的玩家。',
        ],
        'crash_alerts' => [
            'label' => '崩溃通知',
            'description' => '服务器自行停止时通知所有者。',
        ],
    ],
];
