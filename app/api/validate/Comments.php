<?php

namespace app\api\validate;

use think\Validate;

class Comments extends Validate
{
    //定义验证规则
    protected $rule =   [
        'aid'  => 'require|number',
        'name'  => 'require|length:1,12',
        'content'   => 'require|length:1,1024',
        'status'   => 'number|in:0,1',
    ];

    //定义错误信息
    protected $message  =   [
        'aid.require' => '应用ID不得为空',
        'aid.number'     => '应用ID格式错误',

        'name.require' => '昵称不得为空',
        'name.length'     => '昵称超出范围(1-12)',

        'content.require' => '内容不得为空',
        'content.length'     => '内容超出范围(1-1024)',

        'status.number' => '状态格式非法',
        'status.in'     => '状态不存在',
    ];

}
