<?php

namespace yunarch\app\api\validate;

use think\Validate;

//通用
class Common extends Validate
{
    static $all_scene = [
        'default' => [
            'ids',
            'method'
        ]
    ];

    protected function arrayJson($value)
    {
        return RuleUtils::arrayJson($value);
    }

    //定义验证规则
    protected $rule =   [
        'ids'  => 'arrayJson',
        'method'  => 'alpha',
    ];

    //定义错误信息
    protected $message  =   [
        'ids.arrayJson' => 'ID集格式错误',
        'method.alpha' => '方法格式错误',
    ];
}
