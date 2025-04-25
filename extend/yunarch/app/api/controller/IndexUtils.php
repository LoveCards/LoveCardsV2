<?php

namespace yunarch\app\api\controller;

class IndexUtils
{
    /**
     * 将请求参数中的 JSON 字符串转换为数组
     *
     * 该函数检查指定的参数键是否存在于参数数组中，如果存在且值是有效的 JSON 格式，
     * 则将其解码为数组并替换原值；如果 JSON 格式无效，则移除该参数。
     *
     * @param string $keyName 参数键名，指定需要处理的参数
     * @param array $params 请求参数数组，包含所有需要处理的参数
     * @return array 处理后的参数数组，其中指定键的值可能已被转换或移除
     */
    static public function paramsJsonToArray(string $keyName, array $params): array
    {
        // 检查指定的键是否存在于参数数组中
        if (array_key_exists($keyName, $params)) {
            // 尝试解码 JSON 字符串为数组
            $decoded = json_decode($params[$keyName], true);

            // 检查 JSON 解码是否成功
            if (json_last_error() === JSON_ERROR_NONE) {
                // 如果解码成功，将解码后的数组替换原值
                $params[$keyName] = $decoded;
            } else {
                // 如果解码失败，移除该参数键
                unset($params[$keyName]);
            }
        }

        // 返回处理后的参数数组
        return $params;
    }
}
