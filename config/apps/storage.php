<?php

return [
    'default'              => [
        'type'        => 'string',
        'default'     => 'local',
        'description' => '默认存储驱动',
    ],
    'direct_upload_expire' => [
        'type'        => 'int',
        'default'     => 3600,
        'description' => '直链上传过期时间(秒)',
    ],
    'rate_limit_max'       => [
        'type'        => 'int',
        'default'     => 10,
        'description' => '速率限制-最大请求数',
    ],
    'rate_limit_window'    => [
        'type'        => 'int',
        'default'     => 60,
        'description' => '速率限制-时间窗口(秒)',
    ],
];
