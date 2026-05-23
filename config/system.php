<?php
return [
    // ─── 系统标识 ───
    'app_name'     => 'LoveCards',
    'homepage'     => 'https://lovecards.cn',
    'version'      => '2.4.1',
    'build'        => 21,
    'github'       => 'https://github.com/LoveCards/LoveCardsV2',
    'qgroup'       => 'https://jq.qq.com/?_wv=1027&k=qM8f2RMg',

    // ─── 环境要求 ───
    'php_min'      => '7.2.5',
    'php_max'      => '8.0.99',
    'mysql_min'    => '5.7',
    'mysql_max'    => '9999',

    // ─── 系统角色锚点 ───
    'system_roles' => [
        'root'  => 1,
        'admin' => 2,
        'user'  => 3,
        'guest' => 4,
    ],
    'default_role' => 'user',
    'guest_role'   => 'guest',
    'admin_roles'  => ['root', 'admin'],
];
