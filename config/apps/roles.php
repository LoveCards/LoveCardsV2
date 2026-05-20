<?php

return [
    // 系统角色定义（不可删除、不可修改 slug、ID 固定）
    'system_roles' => [
        'root'  => 1,
        'admin' => 2,
        'user'  => 3,
        'guest' => 4,
    ],

    // 业务引用（代码中用 slug，底层自动转 ID）
    'default_role' => 'user',
    'guest_role'   => 'guest',
    'admin_roles'  => ['root', 'admin'],
];
