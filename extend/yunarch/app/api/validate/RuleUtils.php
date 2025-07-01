<?php

namespace yunarch\app\api\validate;

use think\Validate;

use yunarch\app\api\utils\Json as ApiUtilsJson;

//验证规则工具类
class RuleUtils
{

    function __construct()
    {
        // 自动加载验证规则到全局
        Validate::maker(function ($validate) {
            $validate->extend('password', function ($value) {
                return self::password($value);
            });
            $validate->extend('arrayJson', function ($value) {
                return self::arrayJson($value);
            });
            $validate->extend('arrayOrIntJson', function ($value) {
                return self::arrayOrIntJson($value);
            });
            $validate->extend('checkArrayLength', function ($value, $rule) {
                return self::arrayOrIntJson($value, $rule);
            });
            $validate->extend('nonNull', function ($value) {
                return self::nonNull($value);
            });
        });
    }

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

    // 验证数组长度
    static public function nonNull($value)
    {
        if (!empty($value)) {
            return true;
        }
        return false;
    }
}
