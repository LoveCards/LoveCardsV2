<?php

namespace app\api\validate;

use think\Validate;

class CardsComments extends Validate
{
    //定义验证规则
    protected $rule =   [
        'name'  => 'require|length:3,12',
        'content'   => 'require|length:1,1024',
        'state'   => 'number|between:0,1',
    ];

    //定义错误信息
    protected $message  =   [
        'name.require' => '昵称不得为空',
        'name.length'     => '昵称超出范围(3-12)',

        'content.require' => '内容不得为空',
        'content.length'     => '内容超出范围(1-1024)',

        'state.number' => '状态格式非法',
        'state.between'     => '状态不存在',
    ];
}
