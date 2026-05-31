<?php

return [
    'active_theme' => [
        'type'        => 'string',
        'default'     => 'default',
        'description' => '当前活跃主题',
    ],
    'theme_config' => [
        'type'        => 'json',
        'default'     => '{}',
        'description' => '当前主题配置值',
    ],
];
