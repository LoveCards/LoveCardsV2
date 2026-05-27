<?php

return [
    'driver' => [
        'type'        => 'string',
        'default'     => 'smtp',
        'description' => '驱动类型',
    ],
    'host' => [
        'type'        => 'string',
        'default'     => 'smtp.qq.com',
        'description' => 'SMTP 服务器',
    ],
    'port' => [
        'type'        => 'int',
        'default'     => 465,
        'description' => '端口',
    ],
    'addr' => [
        'type'        => 'string',
        'default'     => '',
        'description' => '发件邮箱',
    ],
    'pass' => [
        'type'        => 'string',
        'default'     => '',
        'description' => '邮箱密码/授权码',
    ],
    'name' => [
        'type'        => 'string',
        'default'     => '',
        'description' => '发件人名称',
    ],
    'security' => [
        'type'        => 'string',
        'default'     => 'ssl',
        'description' => '加密方式',
    ],
];
