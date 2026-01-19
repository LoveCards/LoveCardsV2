<?php

namespace app\api\validate;

use think\Validate;

class RolePermissions extends Validate
{
    //参数过滤场景
    static public $all_scene = [
        'admin' => [
            'add' => [
                'normal' => false,
                'require' => [
                    'role_id',
                    'permission_id'
                ],
                'nonNull' => false,
                'toNull' => false,
            ],
            'remove' => [
                'normal' => false,
                'require' => [
                    'role_id',
                    'permission_id'
                ],
                'nonNull' => false,
                'toNull' => false,
            ],
            'batchAdd' => [
                'normal' => false,
                'require' => [
                    'role_id',
                    'permission_ids'
                ],
                'nonNull' => false,
                'toNull' => false,
            ],
            'batchRemove' => [
                'normal' => false,
                'require' => [
                    'role_id',
                    'permission_ids'
                ],
                'nonNull' => false,
                'toNull' => false,
            ]
        ],
    ];
    static public $scene_message = [
        'role_id.require' => '角色ID不能为空',
        'permission_id.require' => '权限ID不能为空',
        'permission_ids.require' => '权限ID集不能为空',
    ];

    //定义验证规则
    protected $rule = [
        'role_id' => 'number',
        'permission_id' => 'number',
        'permission_ids' => 'arrayJson',
    ];

    //定义错误信息
    protected $message = [
        'role_id.number' => '角色ID格式错误',
        'role_id.require' => '角色ID不能为空',

        'permission_id.number' => '权限ID格式错误',
        'permission_id.require' => '权限ID不能为空',

        'permission_ids.arrayJson' => '权限ID集格式错误',
        'permission_ids.require' => '权限ID集不能为空',
    ];
}

