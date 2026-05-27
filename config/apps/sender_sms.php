<?php

return [
    'driver' => [
        'type'        => 'string',
        'default'     => 'aliyun_sms',
        'description' => '驱动类型',
    ],
    'access_key' => [
        'type'        => 'string',
        'default'     => '',
        'description' => 'AccessKey',
    ],
    'secret_key' => [
        'type'        => 'string',
        'default'     => '',
        'description' => 'SecretKey',
    ],
    'sign_name' => [
        'type'        => 'string',
        'default'     => '',
        'description' => '短信签名',
    ],
    'template_code' => [
        'type'        => 'string',
        'default'     => '',
        'description' => '模板编码',
    ],
];
