<?php

return [
    'title' => 'Features',
    'global_description' => 'Turn Shoebill features on or off for the whole panel. Nodes and servers can override this on their Features tab.',
    'overridden' => 'Overridden on :nodes node(s) and :servers server(s).',
    'nav_title' => 'Features',
    'inherit' => 'Inherit',
    'on' => 'On',
    'off' => 'Off',
    'saved' => 'Saved',
    'features' => [
        'status_page' => [
            'label' => 'Status page',
            'description' => 'Averages, uptime and the chart for each server.',
        ],
        'player_history' => [
            'label' => 'Player history',
            'description' => 'Records players seen by the game query.',
        ],
        'crash_alerts' => [
            'label' => 'Crash alerts',
            'description' => 'Notifies the owner when a server stops on its own.',
        ],
    ],
];
