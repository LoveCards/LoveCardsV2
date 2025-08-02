<?php

return [
    'System' => [
        'ThemeDirectory' => env('master.SystemThemeDirectory', 'index'),
        'VisitorMode' => env('master.SystemVisitorMode', true)
    ],
    'Upload' => [
        //最大上传图片大小 单位:M
        'UserImageSize' => env('master.UploadUserImageSize', 2),
        'UserImageExt' => env('master.UploadUserImageExt', 'jpg,png,gif,webp')
    ],
    'UserAuth' => [
        'Captcha' => env('master.UserAuthCaptcha', true)
    ],
    'Cards' => [
        'Approve' => env('master.CardsApprove', true),
        'PictureLimit' => env('master.CardsPictureLimit', 9),
        'TagLimit' => env('master.CardsTagLimit', 3)
    ],
    'Comments' => [
        'Approve' => env('master.CommentsApprove', true),
        'PictureLimit' => env('master.CommentsPictureLimit', 9)
    ],
    'Geetest' => [
        'Status' => env('master.GeetestStatus', false),
        'Id' => env('master.GeetestId', ''),
        'Key' => env('master.GeetestKey', '')
    ]
];
