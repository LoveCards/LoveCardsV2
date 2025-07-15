<?php

namespace app\api\validate;

use think\Validate;

class Comments extends Validate
{
    static public $all_scene = [
        'user' => [
            'post' => [
                'normal' => false,
                'require' => [
                    'aid',
                    'pid',
                    'user_id',
                    'comments',
                ],
                'nonNull' => false,
                'toNull' => [
                    'data'
                ],
            ],
        ],
        'admin' => [
            'patch' => [
                'normal' => [
                    'id',
                    'aid',
                    'pid',
                    'is_top',
                    'status',
                    'user_id',
                    'good',
                    'content',
                ],
                'require' => false,
                'nonNull' => false,
                'toNull' => [
                    'data'
                ],
            ]
        ],
    ];
    static public $scene_message = [
        'pid.require' => '项目ID不能为空',
        'aid.require' => '应用ID不能为空',
        'user_id.require' => '用户ID不能为空',
        'content.require' => '评论不能为空',
    ];

    //定义验证规则
    protected $rule =   [
        'id' => 'number',
        'aid' => 'number',
        'pid' => 'number',
        'parent_id' => 'number',
        'is_top' => 'number',
        'status' => 'number',
        'user_id' => 'number',
        'data' => 'arrayJson',
        'content' => 'max:140',
        'good' => 'number',
        'post_ip' => 'ip|max:39',
    ];

    //定义错误信息
    protected $message  =   [

        'id.number'     => 'ID格式错误',

        'aid.number'     => '应用ID格式错误',

        'pid.number'     => '项目ID格式错误',

        'parent_id.number'     => '父级ID格式错误',

        'is_top.number'     => '置顶格式错误',

        'status.number'     => '状态格式错误',

        'user_id.number'     => '用户ID格式错误',

        'data.arrayJson' => '自定义字段格式错误',

        'good.number'     => '喜欢数格式错误',

        'post_ip.ip' => 'IP地址格式不正确',
        'post_ip.max' => 'IP地址过长',
    ];
}
