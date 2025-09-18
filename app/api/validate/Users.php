<?php

namespace app\api\validate;

use think\Validate;

class Users extends Validate
{
    //场景-规则
    static public $all_scene = [
        'edit' => ['id', 'avatar', 'number', 'roles_id', 'email', 'phone', 'username', 'password', 'status'],
        'register' => ['email', 'phone', 'username', 'password'],
        'login' => ['email', 'phone', 'username', 'password'],

        'user' => [
            'register' => [
                'normal' => [
                    'email',
                    'phone',
                    'username',
                    'password'
                ],
                'require' => false,
                'nonNull' => false,
                'toNull' => false,
            ],
            'login' => [
                'normal' => [
                    'email',
                    'phone',
                    'username',
                    'password'
                ],
                'require' => false,
                'nonNull' => false,
                'toNull' => false,
            ]
        ],
        'admin' => [
            'patch' => [
                'normal' => [
                    'avatar',
                    'roles_id',
                    'email',
                    'phone',
                    'username',
                    'status'
                ],
                'require' => false,
                'nonNull' => [
                    'id',
                    'password',
                    'number'
                ],
                'toNull' => false,
            ],
        ]
    ];
    static public $scene_message = [
        'number.nonNull' => '账号不得为空'
    ];

    //定义验证规则
    protected $rule =   [
        'id' => 'number',
        'number' => 'length:3,20|alphaDash|unique:users',
        'username' => 'length:3,12|chsDash|unique:users',
        'password' => 'length:5,36|password',

        'avatar' => 'max:255|url',
        'email' => 'max:320|email|unique:users',
        'phone' => 'max:20|mobile|unique:users',

        'status' => 'number',

        'roles_id' => 'arrayJson',
    ];

    //定义错误信息
    protected $message  =   [
        'id.require' => 'ID不得为空',
        'id.number' => 'ID格式错误',

        'status.require' => '状态不得为空',
        'status.number' => '状态格式错误',

        'number.require' => '账号不得为空',
        'number.alphaDash' => '账号只能为字母、数字下划线及破折号',
        'number.length'     => '账号超出范围(3-20)',
        'number.unique' => '账号已存在',

        'email.require' => '邮箱不得为空',
        'email.email' => '邮箱格式不正确',
        'email.unique' => '邮箱已存在',
        'email.max'     => '邮箱超出最大长度',

        'phone.require' => '手机号不得为空',
        'phone.mobile' => '手机号格式不正确',
        'phone.unique' => '手机号已存在',
        'phone.max' => '手机号超出最大长度',

        'username.require' => '用户名不得为空',
        'username.length'     => '用户名超出范围(3-12)',
        'username.unique' => '用户名已存在',
        'username.chsDash' => '用户名只能为汉字、字母、数字下划线及破折号',

        'password.require' => '密码不得为空',
        'password.length'     => '密码超出范围(5-36)',
        'password.password' => '密码只能为大写、小写、数字或特殊字符',

        'roles_id.arrayJson' => '权限组格式错误',
    ];

    //场景-登入
    protected function sceneLogin()
    {
        return $this->only($this::$all_scene['login'])
            ->remove('email', 'unique')
            ->remove('phone', 'unique')
            ->remove('username', 'unique');
    }
    //场景-注册
    protected function sceneRegister()
    {
        return $this->only($this::$all_scene['register'])
            ->append('require');
    }
    //场景-编辑
    protected function sceneEdit()
    {
        return $this->only($this::$all_scene['edit'])
            ->append('id', 'require');
    }
}
