<?php

return [
    'title' => '狀態',
    'averages' => '平均值',
    'period' => '期間',
    'periods' => ['today' => '今天', 'week' => '最近 7 天', 'month' => '最近 30 天', 'all' => '全部'],
    'avg_players' => '平均人數',
    'peak_players' => '最高人數',
    'avg_cpu' => '平均 CPU',
    'avg_memory' => '平均記憶體',
    'cpu' => 'CPU',
    'memory' => '記憶體',
    'players' => '玩家',
    'last_24h' => '最近 24 小時（每小時平均）',
    'online_now' => '目前在線：:count / :max',
    'no_names' => '此遊戲不提供玩家名稱。',
    'empty' => '尚無資料。伺服器運作時每分鐘記錄一次。',
    'protocol' => '透過 :protocol 取得人數',
    'no_players' => '此遊戲不支援人數查詢，僅記錄 CPU 與記憶體。',
    'protocols' => ['none' => '無', 'minecraft' => 'Minecraft: Java Edition', 'bedrock' => 'Minecraft: Bedrock Edition', 'source' => 'Steam / Source (A2S)'],
    'egg_field' => '玩家查詢方式',
    'egg_field_help' => '狀態頁統計人數的方式。留空則依 Egg 的標籤與功能自動判斷。',
];
