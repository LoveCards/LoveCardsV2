<?php

namespace yunarch\app\api\validate;

use yunarch\app\api\utils\Json as ApiUtilsJson;

//验证工具类
class Utils
{
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
            return ApiUtilsJson::jsonTypePass($decoded, 'array');
        } else {
            return false;
        }
    }
}
