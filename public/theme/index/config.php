<?php
return [
    //选择格式配置
    'Select' => [
        //推荐页面公告卡开关
        'ThemeNotify' => [
            'Name' => '公告卡开关',
            'Introduction' => '推荐页面公告卡的开关',
            'Default' => env('ThemeConfig.SelectThemeNotify', 1),
            'Element' => [
                0 => false,
                1 => true,
            ]
        ],
        //链接资源CDN开关
        'ThemeLinkCDN' => [
            'Name' => '资源CDN开关',
            'Introduction' => 'HTML中的部分Link资源CDN的开关',
            'Default' => env('ThemeConfig.SelectThemeLinkCDN', 0),
            'Element' => [
                0 => false,
                1 => true,
            ]
        ],
        //默认暗色开关
        'ThemeDark' => [
            'Name' => '默认暗色开关',
            'Introduction' => '主题为默认暗色的开关',
            'Default' => env('ThemeConfig.SelectThemeDark', 1),
            'Element' => [
                0 => false,
                1 => true
            ]
        ],
        //MD主题色配置
        'ThemePrimary' => [
            'Name' => '主题主色',
            'Introduction' => '颜色可参考<a href="https://www.mdui.org/docs/color" target="_blank">MDUI颜色与主题</a>',
            'Default' => env('ThemeConfig.SelectThemePrimary', 4),
            'Element' => [
                0 => 'red',
                1 => 'pink',
                2 => 'purple',
                3 => 'deep-purple',
                4 => 'indigo',
                5 => 'blue',
                6 => 'light-blue',
                7 => 'cyan',
                8 => 'teal',
                9 => 'green',
                10 => 'light-green',
                11 => 'lime',
                12 => 'yellow',
                13 => 'amber',
                14 => 'orange',
                15 => 'deep-orange',
                16 => 'brown',
                17 => 'grey',
                18 => 'blue-grey'
            ]
        ],
        //MD主题色深度配置
        'ThemePrimaryDepth' => [
            'Name' => '主色深度',
            'Introduction' => '颜色可参考<a href="https://www.mdui.org/docs/color" target="_blank">MDUI颜色与主题</a>，关闭则为默认深度',
            'Default' => env('ThemeConfig.SelectThemePrimaryDepth', 0),
            'Element' => [
                0 => false,
                1 => 50,
                2 => 100,
                3 => 200,
                4 => 300,
                5 => 400,
                6 => 500,
                7 => 600,
                8 => 700,
                9 => 800,
                10 => 900,
            ]
        ],
        //MD强调色配置
        'ThemeAccent' => [
            'Name' => '主题强调色',
            'Introduction' => '颜色可参考<a href="https://www.mdui.org/docs/color" target="_blank">MDUI颜色与主题</a>',
            'Default' => env('ThemeConfig.SelectThemeAccent', 1),
            'Element' => [
                0 => 'red',
                1 => 'pink',
                2 => 'purple',
                3 => 'deep-purple',
                4 => 'indigo',
                5 => 'blue',
                6 => 'light-blue',
                7 => 'cyan',
                8 => 'teal',
                9 => 'green',
                10 => 'light-green',
                11 => 'lime',
                12 => 'yellow',
                13 => 'amber',
                14 => 'orange',
                15 => 'deep-orange',
            ]
        ],
        //MD强调色深度配置
        'ThemeAccentDepth' => [
            'Name' => '强调色深度',
            'Introduction' => '颜色可参考<a href="https://www.mdui.org/docs/color" target="_blank">MDUI颜色与主题</a>，关闭则为默认深度',
            'Default' => env('ThemeConfig.SelectThemeAccentDepth', 0),
            'Element' => [
                0 => false,
                1 => 'a100',
                2 => 'a200',
                3 => 'a400',
                4 => 'a700',
            ]
        ],
    ],

    //文本格式配置
    'Text' => [
        //统计代码变量
        'ThemeStatistics' => [
            'Name' => '统计代码',
            'Introduction' => '该代码会插入&lt;head&gt;&lt;/head&gt;内',
            'Default' => env('ThemeConfig.TextThemeStatistics', '')
        ],
        //公告卡名字
        'ThemeNotifyTitle' => [
            'Name' => '公告卡标题',
            'Introduction' => '支持HTML代码，请勿插入来源不明的JS代码',
            'Default' => env('ThemeConfig.TextThemeNotifyTitle', '%E7%AB%99%E9%95%BF%E7%9A%84%E4%BE%BF%E7%AD%BE')
        ],
        //公告卡图片URL
        'ThemeNotifyUrl' => [
            'Name' => '公告卡图片URL',
            'Introduction' => '请填写一张图片的URL地址,以“http(s)://”开头',
            'Default' => env('ThemeConfig.TextThemeNotifyUrl', 'https%3A%2F%2Fimg1.imgtp.com%2F2023%2F05%2F05%2FrsGxwTe7.gif')
        ],
        //公告卡内容
        'ThemeNotifyContent' => [
            'Name' => '公告卡内容',
            'Introduction' => '支持HTML代码，请勿插入来源不明的JS代码',
            'Default' => env('ThemeConfig.TextThemeNotifyContent', '%E6%AC%A2%E8%BF%8E%E6%9D%A5%E5%88%B0LoveCardsV2%E7%9A%84%E9%A2%86%E5%9F%9F%EF%BC%8C%E5%9C%A8%E8%BF%99%E9%87%8C%E6%82%A8%E5%8F%AF%E4%BB%A5%E7%95%85%E6%89%80%E6%AC%B2%E8%A8%80%EF%BC%81')
        ],
    ],

    //鉴权
    'PageAuth' => [
        '/user/' => [
            'CookieUtokenCheck'
        ]
    ],

    //分配数据
    'PageAssignData' => [
        //页面
        '/index/index' => [
            'HotCardList',
            'TagList',
            'MyInfo',
        ],
        '/user/' => [
            'MyInfo',
        ],
        '/cards/index' => [
            'CommonCardList',
            'TagList',
            'MyInfo',
        ],
        '/cards/search' => [
            'SearchCardList',
            'TagList',
            'MyInfo',
        ],
        '/cards/tag' => [
            'TagCardList',
            'TagList',
            'MyInfo',
        ],
        '/cards/card' => [
            'Card',
            'TagList',
            'MyInfo',
        ],
        '/cards/add' => [
            'TagList',
            'MyInfo',
        ]
    ]
];
