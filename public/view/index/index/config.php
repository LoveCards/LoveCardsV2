<?php
$Config = [
    'Select' => [
        //链接资源CDN开关
        'ThemeLinkCDN' => [
            0 => env('ThemeConfig.SelectThemeLinkCDN', 2),
            1 => false,
            2 => true
        ],
        //默认暗色开关
        'ThemeDark' => [
            0 => env('ThemeConfig.SelectThemeDark', 2),
            1 => false,
            2 => true
        ],
        //MD主题色配置
        'ThemePrimary' => [
            0 => env('ThemeConfig.SelectThemePrimary', 5),
            1 => 'red',
            2 => 'pink',
            3 => 'purple',
            4 => 'deep-purple',
            5 => 'indigo',
            6 => 'blue',
            7 => 'light-blue',
            8 => 'cyan',
            9 => 'teal',
            10 => 'green',
            11 => 'light-green',
            12 => 'lime',
            13 => 'yellow',
            14 => 'amber',
            15 => 'orange',
            16 => 'deep-orange',
            17 => 'brown',
            18 => 'grey',
            19 => 'blue-grey'
        ],
        //MD强调色配置
        'ThemeAccent' => [
            0 => env('ThemeConfig.SelectThemeAccent', 2),
            1 => 'red',
            2 => 'pink',
            3 => 'purple',
            4 => 'deep-purple',
            5 => 'indigo',
            6 => 'blue',
            7 => 'light-blue',
            8 => 'teal',
            9 => 'green',
            10 => 'light-green',
            11 => 'lime',
            12 => 'yellow',
            13 => 'amber',
            14 => 'orange',
            15 => 'deep-orange',
            16 => 'blue-grey'
        ],
    ]
];
