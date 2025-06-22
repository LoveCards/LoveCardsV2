<?php

namespace yunarch\app\api\validate;

use yunarch\app\api\utils\Json as ApiUtilsJson;

//验证工具类
class RuleUtils
{
    // 通用密码验证规则
    static public function password($value)
    {
        // 密码包含大写字母、小写字母、数字或特殊字符中的至少一个
        if (!preg_match('/[A-Z]|[a-z]|\d|[@#$%^&+=!]/', $value)) {
            return false;
        }
        return true;
    }
    // 通用JSON->Array验证规则
    static public function arrayJson($value)
    {
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return ApiUtilsJson::jsonTypePass($decoded, 'array');
        } else {
            return false;
        }
    }
    // 通用JSON->Array||Integer验证规则
    static public function arrayOrIntJson($value)
    {
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return (ApiUtilsJson::jsonTypePass($decoded, 'array') || ApiUtilsJson::jsonTypePass($decoded, 'Integer'));
        } else {
            return false;
        }
    }

    // 验证数组长度
    static public function checkArrayLength($value, $rule)
    {
        $length = count($value);
        if ($length <= $rule) {
            return true;
        }
        return false;
    }
}
