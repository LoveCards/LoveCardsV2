<?php

namespace app\api\validate;

use think\Validate;

class Permissions extends Validate
{
    static public $all_scene = [
        'admin' => [
            'create' => [
                'normal' => [
                    'description'
                ],
                'require' => [
                    'name',
                    'slug',
                    'route_name',
                    'method'
                ],
                'nonNull' => false,
                'toNull' => false,
            ],
            'patch' => [
                'normal' => [
                    'name',
                    'slug',
                    'route_name',
                    'method',
                    'description'
                ],
                'require' => [
                    'id'
                ],
                'nonNull' => false,
                'toNull' => false,
            ]
        ],
    ];
    static public $scene_message = [
        'id.require' => '权限ID不能为空',
        'name.require' => '权限名称不能为空',
        'slug.require' => '权限标识不能为空',
        'route_name.require' => '路由标识不能为空',
        'method.require' => 'HTTP方法不能为空',
    ];

    protected $rule = [
        'id' => 'number',
        'name' => 'length:1,100|chsDash',
        'slug' => 'length:1,100|alphaDash|unique:permissions',
        'route_name' => 'max:255',
        'method' => 'in:GET,POST,PUT,PATCH,DELETE,*',
        'description' => 'max:255',
    ];

    protected $message = [
        'id.number' => '权限ID格式错误',
        'id.require' => '权限ID不能为空',

        'name.length' => '权限名称超出范围(1-100)',
        'name.chsDash' => '权限名称只能为汉字、字母、数字下划线及破折号',
        'name.require' => '权限名称不能为空',

        'slug.length' => '权限标识超出范围(1-100)',
        'slug.alphaDash' => '权限标识只能为字母、数字下划线及破折号',
        'slug.unique' => '权限标识已存在',
        'slug.require' => '权限标识不能为空',

        'route_name.max' => '路由标识超出最大长度(255)',
        'route_name.require' => '路由标识不能为空',

        'method.in' => 'HTTP方法只能是: GET,POST,PUT,PATCH,DELETE,*',
        'method.require' => 'HTTP方法不能为空',

        'description.max' => '权限描述超出最大长度(255)',
    ];
}
