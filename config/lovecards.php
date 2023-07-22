<?php

return [
    'template_directory' => env('lovecards.template_directory', 'index'),
    'api' => [
        'Cards' => [
            //默认卡片状态ON/OFF:0/1
            'DefSetCardsState' => env('lovecards.DefSetCardsState', 0),
            //默认添加卡片上传图片个数
            'DefSetCardsImgNum' => env('lovecards.DefSetCardsImgNum', 9),
            //默认添加卡片标签个数
            'DefSetCardsTagNum' => env('lovecards.DefSetCardsTagNum', 3)
        ],
        'CardsComments' => [
            //默认评论状态ON/OFF:0/1
            'DefSetCardsCommentsState' => env('lovecards.DefSetCardsCommentsState', 0),
            //默认添加评论上传图片个数
            'DefCardsSetCommentsImgNum' => env('lovecards.DefCardsSetCommentsImgNum', 3)
        ],
        'Upload' => [
            //最大上传图片大小 单位:M
            'DefSetCardsImgSize' => env('lovecards.DefSetCardsImgSize', 2)
        ]
    ]
];
