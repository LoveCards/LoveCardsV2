<?php

return [
    'default_smtp' => [
        'type'        => 'string',
        'default'     => 'email',
        'description' => 'SMTP 渠道默认 Driver',
    ],
    'default_sms' => [
        'type'        => 'string',
        'default'     => 'sms',
        'description' => 'SMS 渠道默认 Driver',
    ],
    'default_webhook' => [
        'type'        => 'string',
        'default'     => '',
        'description' => 'Webhook 渠道默认 Driver',
    ],
];
