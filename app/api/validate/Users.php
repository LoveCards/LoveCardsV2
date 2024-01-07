<?php

namespace app\api\validate;

use think\Validate;
use think\facade\Config;

class Users extends Validate
{

    // 自定义验证规则，检查密码格式
    protected function password($value)
    {
        // 密码包含大写字母、小写字母、数字或特殊字符中的至少一个
        if (!preg_match('/[A-Z]|[a-z]|\d|[@#$%^&+=!]/', $value)) {
            return false;
        }
        return true;
    }

    //定义验证规则
    protected $rule =   [
        'id' => 'number',
        'email' => 'email|unique:users',
        'phone' => 'mobile|unique:users',
        'username' => 'length:3,12|chsDash|unique:users',
        'password' => 'length:5,36|password',
        'status' => 'number',
    ];

    //定义错误信息
    protected $message  =   [
        'id.require' => 'id不得为空',
        'id.number' => 'id格式错误',

        'status.require' => '状态不得为空',
        'status.number' => '状态格式错误',

        'number.require' => '账号不得为空',
        'number.unique' => '账号已存在',

        'email.require' => '邮箱不得为空',
        'email.email' => '邮箱格式不正确',
        'email.unique' => '邮箱已存在',

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

    ];

    //登入场景
    protected function sceneLogin()
    {
        return $this->only(['email', 'phone', 'username', 'password'])
            ->remove('email', 'unique')
            ->remove('phone', 'unique')
            ->remove('username', 'unique');
    }
    //注册场景
    protected function sceneRegister()
    {
        return $this->only(['email', 'phone', 'username', 'password'])
            ->append('require');
    }
    //编辑场景
    protected function sceneEdit()
    {
        return $this->only(['id', 'email', 'phone', 'username', 'password', 'status'])
            ->append('id', 'require');
    }
}
