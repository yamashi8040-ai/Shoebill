<?php

return [
    'title' => '状态',
    'averages' => '平均值',
    'period' => '时间段',
    'periods' => ['today' => '今天', 'week' => '最近 7 天', 'month' => '最近 30 天', 'all' => '全部'],
    'avg_players' => '平均人数',
    'peak_players' => '最高人数',
    'avg_cpu' => '平均 CPU',
    'avg_memory' => '平均内存',
    'cpu' => 'CPU',
    'memory' => '内存',
    'players' => '玩家',
    'last_24h' => '最近 24 小时（每小时平均）',
    'online_now' => '当前在线：:count / :max',
    'no_names' => '该游戏不提供玩家名称。',
    'empty' => '暂无数据。服务器运行时每分钟记录一次。',
    'protocol' => '通过 :protocol 获取人数',
    'no_players' => '该游戏不支持人数查询，仅记录 CPU 和内存。',
    'protocols' => ['none' => '无', 'minecraft' => 'Minecraft: Java Edition', 'bedrock' => 'Minecraft: Bedrock Edition', 'source' => 'Steam / Source (A2S)'],
    'egg_field' => '玩家查询方式',
    'egg_field_help' => '状态页统计人数的方式。留空则根据 Egg 的标签和功能自动判断。',
];
