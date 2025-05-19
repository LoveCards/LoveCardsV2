<?php

namespace yunarch\app\api\validate;

use think\Validate;

//通用超级分页查询验证类
class Get extends Validate
{
    static $all_scene = [
        'index' => [
            'id'
        ]
    ];

    // protected function arrayOrIntJson($value, $rule, $data = [])
    // {
    //     return RuleUtils::arrayOrIntJson($value);
    // }

    //定义验证规则
    protected $rule =   [
        'id'  => 'require|number',
    ];

    //定义错误信息
    protected $message  =   [
        'id.require' => 'ID不得为空',
        'id.number' => 'ID格式错误',
        'id.arrayOrIntJson' => 'ID格式错误',
    ];
}
