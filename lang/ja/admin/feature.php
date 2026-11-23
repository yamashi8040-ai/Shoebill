<?php

return [
    'title' => '機能',
    'global_description' => 'Shoebill の機能をパネル全体で ON / OFF します。ノードとサーバーは、それぞれの「機能」タブでこの設定を上書きできます。',
    'overridden' => ':nodes 個のノードと :servers 個のサーバーで上書きされています。',
    'nav_title' => '機能',
    'inherit' => '継承',
    'on' => 'ON',
    'off' => 'OFF',
    'saved' => '保存しました',
    'features' => [
        'status_page' => [
            'label' => 'ステータスページ',
            'description' => 'サーバーごとの平均・稼働率・グラフ。',
        ],
        'player_history' => [
            'label' => 'プレイヤー履歴',
            'description' => 'ゲームクエリで確認したプレイヤーを記録します。',
        ],
        'crash_alerts' => [
            'label' => 'クラッシュ通知',
            'description' => 'サーバーが勝手に停止したとき所有者に通知します。',
        ],
    ],
];
