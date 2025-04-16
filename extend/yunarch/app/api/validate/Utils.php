<?php

namespace yunarch\app\api\validate;

use think\Validate;

//验证工具类
class Utils
{

    //  json类型验证
    function jsonTypePass($value, $rule)
    {
        //可以解析为数组
        if ($rule === 'array' && !is_array($value)) {
            return false;
        }
        //其他
        if ($rule === 'integer' && !is_int($value)) {
            return false;
        }
        if ($rule === 'string' && !is_string($value)) {
            return false;
        }
        if ($rule === 'bool' && !is_bool($value)) {
            return false;
        }
        return true;
    }

    // 自定义验证规则
    function rulePassword($value)
    {
        // 密码包含大写字母、小写字母、数字或特殊字符中的至少一个
        if (!preg_match('/[A-Z]|[a-z]|\d|[@#$%^&+=!]/', $value)) {
            return false;
        }
        return true;
    }
    function ruleArrayJson($value)
    {
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $this->jsonTypePass($decoded, 'array');
        } else {
            return false;
        }
    }
}
