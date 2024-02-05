<?php
return [
    'System' => [
        'VisitorMode' => env('master.SystemVisitorMode', true),
    ],
    'Upload' => [
        //最大上传图片大小 单位:M
        'UserImageSize' => env('master.UploadUserImageSize', 2),
        'UserImageExt' => env('master.UploadUserImageExt', 'jpg,png,gif,webp'),
    ],
    'UserAuth' => [
        'Captcha' => env('master.UserAuthCaptcha', false),
    ]
];
