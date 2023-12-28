<?php

namespace app\api\validate;

use think\Validate;
use think\facade\Config;

class Cards extends Validate
{
    //定义验证规则
    protected $rule =   [
        'uid' => 'number',
        'content' => 'require',

        'woName' => 'chsDash|max:36',
        'woContact' => 'max:64',
        'taName' => 'require|chsDash|length:1,36',
        'taContact' => 'max:64',

        'good' => 'number',
        'comments' => 'number',

        'tag' => 'JSON:TAG',
        'img' => 'JSON',

        'model' => 'in:0,1',
        'top' => 'in:0,1',
        'status' => 'in:0,1',
    ];

    //定义错误信息
    protected $message  =   [
        'uid.number' => 'uid格式非法',
        'content.require' => 'content不得为空',

        'woName.chsDash' => 'woName只能是汉字、字母、数字和下划线_及破折号-',
        'woName.max' => 'woName超出范围(36)',
        'woContact.max' => 'woContact超出范围(60)',

        'taName.require' => 'taName不得为空',
        'taName.chsDash' => 'taName只能是汉字、字母、数字和下划线_及破折号-',
        'taName.max' => 'taName超出范围(36)',
        'taContact.max' => 'taContact超出范围(60)',

        'good.number' => 'good格式非法',
        'comments.number' => 'comments格式非法',

        'tag.JSON' => 'tag超过上限或格式非法',
        'img.JSON' => 'img超过上限或格式非法',

        'model.in' => 'model格式非法',
        'top.in' => 'top格式非法',
        'status.in' => 'status格式非法',
    ];

    // 自定义验证规则
    protected function JSON($value, $rule)
    {
        //获取数量限制配置
        if ($rule == 'TAG') {
            $rule = Config::get('lovecards.api.Cards.DefSetCardsTagNum');
        } else {
            //默认数量限制
            $rule = Config::get('lovecards.api.Cards.DefSetCardsImgNum');
        }
        //格式转换
        $value = json_decode($value, true);
        //判断JSON格式
        if (json_last_error() == JSON_ERROR_NONE) {
            //判断图片数量
            if (sizeof($value) <= $rule) {
                //满足数量限制
                return true;
            }
        }

        return false;
    }
}
