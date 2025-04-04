<?php

namespace app\api\validate;

use think\Validate;

class Admin extends Validate
{
    //定义验证规则
    protected $rule =   [
        'userName'  => 'require|length:3,12|unique:admin',
        'password'   => 'require|length:5,12',
        'power' => 'require|in:0,1',
    ];

    //定义错误信息
    protected $message  =   [
        'userName.require' => '用户名不得为空',
        'userName.length'     => '用户名超出范围(3-12)',
        'userName.unique' => '用户名已存在',

        'password.require' => '密码不得为空',
        'password.length'     => '密码超出范围(5-12)',

        'power.require' => '权限不得为空',
        'power.in'     => '权限格式非法',
    ];

    //验证场景-登入
    protected function sceneLogin()
    {
        return $this->only(['userName', 'password'])
            ->remove('userName', 'unique');
    }

    //验证场景-添加
    protected function sceneAdd()
    {
        //return $this->only(['userName', 'password', 'power']);
    }

    //验证场景-编辑
    protected function sceneEdit()
    {
        return $this->only(['userName', 'password', 'power'])
            ->remove('password', 'require')
            ->remove('userName', 'unique');
    }
}
