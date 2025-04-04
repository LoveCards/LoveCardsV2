<?php

namespace yunarch\app\api\utils;

//api通用函数类
class Common
{
    /**
     * 判断输入是否是有效的 JSON 格式字符串，并返回解析后的数组
     *
     * @param mixed $input 输入的值（通常是一个字符串）
     * @return array|null 如果输入是有效的 JSON 格式字符串，返回解析后的数组；否则返回 null
     */
    function isJson($input)
    {
        // 检查输入是否是字符串
        // JSON 格式字符串必须是字符串类型
        if (!is_string($input)) {
            return null; // 如果输入不是字符串，直接返回 null
        }

        // 尝试将输入解析为 JSON
        // json_decode 会尝试将 JSON 字符串解析为 PHP 值
        // 第二个参数设置为 true，表示将 JSON 对象解析为关联数组
        $decoded = json_decode($input, true);

        // 检查 JSON 解析是否成功
        // json_last_error() 返回最近一次 JSON 操作的错误代码
        // 如果返回 JSON_ERROR_NONE，说明解析成功
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded; // 返回解析后的数组
        } else {
            return null; // 如果解析失败，返回 null
        }
    }


    /**
     * BatchOrSingle 函数用于判断输入是一个单独的数字还是一个JSON格式的数组字符串。
     * 如果是数字，直接返回该数字；如果是有效的JSON数组字符串，解析后返回数组；否则返回false。
     *
     * @param mixed $id 输入的值，可能是数字、JSON格式的字符串或其他类型。
     *
     * @return mixed 如果输入是数字，返回该数字；如果是有效的JSON数组字符串，返回解析后的数组；否则返回false。
     *
     * 示例用法：
     * echo BatchOrSingle(123); // 输出：123
     * print_r(BatchOrSingle('[1,2,3]')); // 输出：Array ( [0] => 1 [1] => 2 [2] => 3 )
     * var_dump(BatchOrSingle('hello')); // 输出：bool(false)
     * var_dump(BatchOrSingle(null)); // 输出：bool(false)
     */
    function BatchOrSingle($id)
    {
        // 如果输入是数字，直接返回该数字
        if (is_numeric($id)) {
            return $id;
        }
        // 如果输入是字符串，尝试解析为JSON
        elseif (is_string($id)) {
            $decoded = json_decode($id, true);
            // 检查是否成功解析为数组
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        // 如果既不是数字也不是有效的JSON字符串，返回false
        return false;
    }
}
