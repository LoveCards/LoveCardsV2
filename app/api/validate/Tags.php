<?php

namespace app\api\validate;

use think\Validate;

class Tags extends Validate
{
    //参数过滤场景
    static public $all_scene = [
        'user' => [
            'post' => [
                'normal' => false,
                'require' => false,
                'nonNull' => false,
                'toNull' => false,
            ]
        ],
        'admin' => [
            'post' => [
                'normal' => false,
                'require' => [
                    'name',
                ],
                'nonNull' => false,
                'toNull' => false,
            ],
            'patch' => [
                'normal' => [
                    'aid',
                    'user_id',
                    'name',
                    'status',
                ],
                'require' => [
                    'id',
                ],
                'nonNull' => false,
                'toNull' => false,
            ]
        ],
    ];
    static public $scene_message = [
        'aid.require' => '应用ID不得为空',
        'name.require' => '标签名不得为空',
    ];

    //定义验证规则
    protected $rule =   [
        'id'  => 'number',
        'aid'  => 'number',
        'uid'  => 'number',
        'name'  => 'chsDash|length:1,255',
        'status'   => 'number',
    ];

    //定义错误信息
    protected $message  =   [
        'aid.number'     => '应用ID格式错误',
        'user_id.number'     => '用户ID格式错误',

        'name.length'     => '标签名超出范围(1-255)',
        'name.chsDash' => '用户名只能为汉字、字母、数字下划线及破折号',

        'status.in'     => '状态不存在',
    ];
}
