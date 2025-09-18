<?php

namespace yunarch\utils\src;

use think\Validate;

//验证规则工具类
class ValidateRuleExtend
{
    // 加载验证规则到全局
    public function maker()
    {
        Validate::maker(function ($validate) {
            $validate->extend('password', function ($value) {
                return $this->password($value);
            });
            $validate->extend('arrayJson', function ($value) {
                return $this->arrayJson($value);
            });
            $validate->extend('arrayOrIntJson', function ($value) {
                return $this->arrayOrIntJson($value);
            });
            $validate->extend('checkArrayLength', function ($value, $rule) {
                return $this->arrayOrIntJson($value, $rule);
            });
            $validate->extend('nonNull', function ($value) {
                return $this->nonNull($value);
            });
        });
    }

    // 通用密码验证规则
    public function password($value)
    {
        // 密码包含大写字母、小写字母、数字或特殊字符中的至少一个
        if (!preg_match('/[A-Z]|[a-z]|\d|[@#$%^&+=!]/', $value)) {
            return false;
        }
        return true;
    }
    // 通用JSON->Array验证规则
    public function arrayJson($value)
    {
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $this->jsonTypePass($decoded, 'array');
        } else {
            return false;
        }
    }
    // 通用JSON->Array||Integer验证规则
    public function arrayOrIntJson($value)
    {
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return ($this->jsonTypePass($decoded, 'array') || $this->jsonTypePass($decoded, 'Integer'));
        } else {
            return false;
        }
    }

    // 验证数组长度
    public function checkArrayLength($value, $rule)
    {
        $length = count($value);
        if ($length <= $rule) {
            return true;
        }
        return false;
    }

    // 验证数组长度
    public function nonNull($value)
    {
        if (!empty($value)) {
            return true;
        }
        return false;
    }

    // json类型验证方法
    private function jsonTypePass($value, $rule)
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
}
