<?php

namespace app\api\validate;

use think\Validate;

class Cards extends Validate
{
    //定义验证规则
    protected $rule =   [
        'content' => 'require',

        'woName' => 'chsDash|max:36',
        'woContact' => 'max:64',
        'taName' => 'require|chsDash|length:1,36',
        'taContact' => 'max:64',

        'good' => 'number',
        'comments' => 'number',

        'model' => 'in:0,1',
        'top' => 'in:0,1',
        'state' => 'in:0,1',
    ];

    //定义错误信息
    protected $message  =   [
        'content.require' => 'content不得为空',

        'woName.chsDash' => 'woName只能是汉字、字母、数字和下划线_及破折号-',
        'woName.max' => 'woName超出范围(36)',
        'woContact.max' => 'woContact格式非法',

        'taName.require' => 'taName不得为空',
        'taName.chsDash' => 'taName只能是汉字、字母、数字和下划线_及破折号-',
        'taName.max' => 'taName超出范围(36)',
        'taContact.max' => 'taContact格式非法',

        'good.number' => 'good格式非法',
        'comments.number' => 'comments格式非法',
        //'tag.array' => 'tag格式非法',
        'model.in' => 'model格式非法',

        //'time.date' => 'time格式非法',
        //'ip.ip' => 'ip格式非法',
        'top.in' => 'top格式非法',
        'state.in' => 'state格式非法',
    ];
}
