<?php

return [
    'default_code_driver'    => [
        'type'        => 'string',
        'default'     => 'smtp_code',
        'description' => '验证码默认驱动',
    ],
    'default_captcha_driver' => [
        'type'        => 'string',
        'default'     => 'geetest_v4',
        'description' => '人机验证默认驱动',
    ],
    'code_enabled'           => [
        'type'        => 'bool',
        'default'     => true,
        'description' => '验证码功能开关',
    ],
    'captcha_enabled'        => [
        'type'        => 'bool',
        'default'     => true,
        'description' => '人机验证功能开关',
    ],
    'code_channel'           => [
        'type'        => 'string',
        'default'     => 'smtp',
        'description' => '验证码发送渠道（smtp / sms）',
    ],
];
