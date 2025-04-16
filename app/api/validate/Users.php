<?php

namespace app\api\validate;

use think\Validate;
use yunarch\app\api\facade\ValidateUtils as ApiValidateUtils;

class Users extends Validate
{
    protected function password($value)
    {
        return ApiValidateUtils::rulePassword($value);
    }
    protected function arrayJson($value)
    {
        return ApiValidateUtils::ruleArrayJson($value);
    }

    //定义验证规则
    protected $rule =   [
        'id' => 'number',
        'number' => 'length:3,20|alphaDash|unique:users',
        'username' => 'length:3,12|chsDash|unique:users',
        'password' => 'length:5,36|password',

        //'avatar' => 'length:3,254|url',
        'email' => 'length:3,254|email|unique:users',
        'phone' => 'mobile|unique:users',

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
        'email.length'     => '邮箱超出范围(3-254)',

        'phone.require' => '手机号不得为空',
        'phone.mobile' => '手机号格式不正确',
        'phone.unique' => '手机号已存在',

        'username.require' => '用户名不得为空',
        'username.length'     => '用户名超出范围(3-12)',
        'username.unique' => '用户名已存在',
        'username.chsDash' => '用户名只能为汉字、字母、数字下划线及破折号',

        'password.require' => '密码不得为空',
        'password.length'     => '密码超出范围(5-36)',
        'password.password' => '密码只能为大写、小写、数字或特殊字符',

        'roles_id.arrayJson' => '权限组格式错误',
    ];

    //场景-规则
    static public $all_scene = [
        'edit' => ['id', 'avatar', 'number', 'roles_id', 'email', 'phone', 'username', 'password', 'status'],
        'register' => ['email', 'phone', 'username', 'password'],
        'login' => ['email', 'phone', 'username', 'password'],
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
