<?php

namespace app\api\validate;

use think\Validate;
use think\facade\Config;

use yunarch\app\api\validate\RuleUtils;

class Cards extends Validate
{
    protected function arrayJson($value)
    {
        return RuleUtils::arrayJson($value);
    }
    //验证图片个数
    protected function picturesLength($value)
    {
        $config = Config::get('lovecards.api.Cards');
        $decoded = json_decode($value, true);
        return RuleUtils::checkArrayLength($decoded, $config['DefSetCardsImgNum']);
    }
    //验证标签个数
    protected function tagsLength($value)
    {
        $config = Config::get('lovecards.api.Cards');
        $decoded = json_decode($value, true);
        return RuleUtils::checkArrayLength($decoded, $config['DefSetCardsTagNum']);
    }

    //参数过滤场景
    static public $all_scene = [
        'user' => [
            'post' => [
                'data',
                'cover',
                'content',
                'pictures',
            ],
            'patch' => [
                'id',
                'data',
                'cover',
                'content',
                'pictures',
            ],
        ],
        'admin' => [
            'patch' => [
                'id',
                'is_top',
                'status',
                'user_id',
                'data',
                'cover',
                'content',
                'tags',
                'goods',
                'views',
                'comments',
                'pictures',
            ]
        ],
    ];

    //定义验证规则
    protected $rule =   [
        //数据库
        'id' => 'number',
        'is_top' => 'number',
        'status' => 'number',
        'user_id' => 'number',
        'data' => 'arrayJson',
        'cover' => 'url|max:2083',
        'content' => 'max:5000',
        'tags' => 'arrayJson|tagsLength',
        'goods' => 'number',
        'views' => 'number',
        'comments' => 'number',
        'post_ip' => 'ip|max:39',
        'created_at' => 'date',
        'updated_at' => 'date',
        'deleted_at' => 'date',

        //前端
        'pictures' => 'arrayJson|picturesLength',
    ];

    //定义错误信息
    protected $message  =   [
        'id.number' => 'ID格式错误',

        'is_top.number' => '置顶状态格式错误',

        'status.number' => '状态格式错误',

        'user_id.number' => '用户ID格式错误',

        'data.arrayJson' => '自定义字段格式错误',

        'cover.url' => '封面图片格式不正确',
        'cover.max' => '封面图片地址过长',

        //'content.require' => '内容不得为空',

        'tags.arrayJson' => '标签格式错误',
        'tags.tagsLength' => '标签个数超出上限',

        'goods.number' => '商品ID格式错误',

        'views.number' => '浏览量格式错误',

        'comments.number' => '评论数格式错误',

        'post_ip.ip' => 'IP地址格式不正确',
        'post_ip.max' => 'IP地址过长',

        'pictures.arrayJson' => '图集格式错误',
        'pictures.picturesLength' => '图片个数超出上限',
    ];
}
