<?php

namespace yunarch\app\api\utils;

//JSON函数类
class Json
{
    //  json类型验证
    static function jsonTypePass($value, $rule)
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
