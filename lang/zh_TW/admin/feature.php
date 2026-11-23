<?php

return [
    'title' => '功能',
    'global_description' => '為整個面板開啟或關閉 Shoebill 功能。節點與伺服器可在各自的「功能」分頁中覆寫此設定。',
    'overridden' => '已在 :nodes 個節點與 :servers 台伺服器上覆寫。',
    'nav_title' => '功能',
    'inherit' => '繼承',
    'on' => '開',
    'off' => '關',
    'saved' => '已儲存',
    'features' => [
        'status_page' => [
            'label' => '狀態頁',
            'description' => '每台伺服器的平均值、運作率和圖表。',
        ],
        'player_history' => [
            'label' => '玩家歷史',
            'description' => '記錄遊戲查詢發現的玩家。',
        ],
        'crash_alerts' => [
            'label' => '當機通知',
            'description' => '伺服器自行停止時通知擁有者。',
        ],
    ],
];
