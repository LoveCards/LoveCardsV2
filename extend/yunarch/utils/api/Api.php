<?php

namespace yunarch\utils\api;

class Api
{
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
