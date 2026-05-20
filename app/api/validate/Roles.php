<?php

namespace app\api\validate;

use think\Validate;

class Roles extends Validate
{
    static public $all_scene = [
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
        'update' => [
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
                'permission_hashes'
            ],
            'nonNull' => false,
            'toNull' => false,
        ]
    ];
    static public $scene_message = [
        'id.require' => '角色ID不能为空',
        'name.require' => '角色名称不能为空',
        'slug.require' => '角色标识不能为空',
        'permission_hashes.require' => '权限hash集不能为空',
    ];

    protected $rule = [
        'id' => 'number',
        'name' => 'length:1,50|chsDash',
        'slug' => 'length:1,50|alphaDash|unique:roles',
        'description' => 'max:255',
        'permission_hashes' => 'arrayJson',
    ];

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

        'permission_hashes.arrayJson' => '权限hash集格式错误',
        'permission_hashes.require' => '权限hash集不能为空',
    ];
}
