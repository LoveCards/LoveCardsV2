<?php
return [
    'theme_directory' => env('lovecards.theme_directory', 'index'),
    'api' => [
        'Cards' => [
            //默认卡片状态ON/OFF:0/1
            'DefSetCardsStatus' => env('lovecards.DefSetCardsStatus', 0),
            //默认添加卡片上传图片个数
            'DefSetCardsImgNum' => env('lovecards.DefSetCardsImgNum', 9),
            //默认添加卡片标签个数
            'DefSetCardsTagNum' => env('lovecards.DefSetCardsTagNum', 3)
        ],
        'CardsComments' => [
            //默认评论状态ON/OFF:0/1
            'DefSetCardsCommentsStatus' => env('lovecards.DefSetCardsCommentsStatus', 0),
            //默认添加评论上传图片个数
            'DefCardsSetCommentsImgNum' => env('lovecards.DefCardsSetCommentsImgNum', 3)
        ],
        'Upload' => [
            //最大上传图片大小 单位:M
            'DefSetCardsImgSize' => env('lovecards.DefSetCardsImgSize', 2)
        ]
    ],
    'class' => [
        'geetest' => [
            //默认全局验证ON/OFF:1/0
            'DefSetValidatesStatus' => env('lovecards.DefSetValidatesStatus', ),
            //极验ID
            'DefSetGeetestId' => env('lovecards.DefSetGeetestId', ''),
            //极验Key
            'DefSetGeetestKey' => env('lovecards.DefSetGeetestKey', '')
        ]
    ]
];
