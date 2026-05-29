<?php

return [
    'default'       => [
        'type'        => 'string',
        'default'     => 'sender_smtp',
        'description' => '全局默认发送渠道',
    ],
    'default_smtp'  => [
        'type'        => 'string',
        'default'     => 'sender_smtp',
        'description' => 'SMTP 渠道默认 Driver',
    ],
    'default_sms'   => [
        'type'        => 'string',
        'default'     => 'sender_aliyun_sms',
        'description' => 'SMS 渠道默认 Driver',
    ],
];
