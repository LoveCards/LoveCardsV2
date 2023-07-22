<?php

namespace app\api\validate;

use think\Validate;

class CardsTag extends Validate
{
    //定义验证规则
    protected $rule =   [
        'name'  => 'require|length:1,8',
        'tip'   => 'length:1,1024',
        'status'   => 'in:0,1',
    ];

    //定义错误信息
    protected $message  =   [
        'name.require' => '昵称不得为空',
        'name.length'     => '昵称超出范围(1-8)',

        'tip.length'     => '内容超出范围(1-64)',

        'status.in'     => '状态不存在',
    ];
}
