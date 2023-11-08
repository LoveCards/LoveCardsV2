<?php

namespace app\api\validate;

use think\Validate;

class Tags extends Validate
{
    //定义验证规则
    protected $rule =   [
        'aid'  => 'require|number',
        'name'  => 'require|length:1,8',
        'tip'   => 'length:1,64',
        'status'   => 'in:0,1',
    ];

    //定义错误信息
    protected $message  =   [
        'aid.require' => '应用ID不得为空',
        'aid.number'     => '应用ID格式错误',

        'name.require' => '标签名不得为空',
        'name.length'     => '标签名超出范围(1-8)',

        'tip.length'     => '内容超出范围(1-64)',

        'status.in'     => '状态不存在',
    ];
}
