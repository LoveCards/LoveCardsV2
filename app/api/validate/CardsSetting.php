<?php

namespace app\api\validate;

use think\Validate;

class CardsSetting extends Validate
{
    //定义验证规则
    protected $rule =   [
        'DefSetCardsImgNum'  => 'require|number',
        'DefSetCardsTagNum'  => 'require|number',
        'DefSetCardsStatus'  => 'require|number|in:0,1',
        'DefSetCardsImgSize'  => 'require|number',
        'DefSetCardsCommentsStatus'  => 'require|number|in:0,1'
    ];

    //定义错误信息
    protected $message  =   [
        'DefSetCardsImgNum.require' => '图片上传最大数不得为空',
        'DefSetCardsImgNum.number' => '图片上传最大数格式非法',

        'DefSetCardsTagNum.require' => '图片上传最大数不得为空',
        'DefSetCardsTagNum.number' => '图片上传最大数格式非法',

        'DefSetCardsImgSize.require' => '图片大小限制不得为空',
        'DefSetCardsImgSize.number' => '图片大小限制格式非法',

        'DefSetCardsStatus.require' => '昵称不得为空',
        'DefSetCardsStatus.number' => '卡片审核格式非法',
        'DefSetCardsStatus.in'     => '卡片审核状态不存在',

        'DefSetCardsCommentsStatus.require' => '昵称不得为空',
        'DefSetCardsCommentsStatus.number' => '评论审核格式非法',
        'DefSetCardsCommentsStatus.in'     => '评论审核状态不存在',
    ];
}
