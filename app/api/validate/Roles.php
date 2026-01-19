<?php

namespace app\api\validate;

use think\Validate;

class Roles extends Validate
{
    //参数过滤场景
    static public $all_scene = [
        'admin' => [
            'create' => [
                'normal' => [
                    'description'
                ],
                'require' => [
                    'name',
                    'slug'
                ],
                'nonNull' => false,
                'toNull' => false,
            ],
            'patch' => [
                'normal' => [
                    'name',
                    'slug',
                    'description'
                ],
                'require' => [
                    'id'
                ],
                'nonNull' => false,
                'toNull' => false,
            ],
            'assignPermissions' => [
                'normal' => false,
                'require' => [
                    'id',
                    'permission_ids'
                ],
                'nonNull' => false,
                'toNull' => false,
            ]
        ],
    ];
    static public $scene_message = [
        'id.require' => '角色ID不能为空',
        'name.require' => '角色名称不能为空',
        'slug.require' => '角色标识不能为空',
        'permission_ids.require' => '权限ID集不能为空',
    ];

    //定义验证规则
    protected $rule = [
        'id' => 'number',
        'name' => 'length:1,50|chsDash',
        'slug' => 'length:1,50|alphaDash|unique:roles',
        'description' => 'max:255',
        'permission_ids' => 'arrayJson',
    ];

    //定义错误信息
    protected $message = [
        'id.number' => '角色ID格式错误',
        'id.require' => '角色ID不能为空',

        'name.length' => '角色名称超出范围(1-50)',
        'name.chsDash' => '角色名称只能为汉字、字母、数字下划线及破折号',
        'name.require' => '角色名称不能为空',

        'slug.length' => '角色标识超出范围(1-50)',
        'slug.alphaDash' => '角色标识只能为字母、数字下划线及破折号',
        'slug.unique' => '角色标识已存在',
        'slug.require' => '角色标识不能为空',

        'description.max' => '角色描述超出最大长度(255)',

        'permission_ids.arrayJson' => '权限ID集格式错误',
        'permission_ids.require' => '权限ID集不能为空',
    ];
}

