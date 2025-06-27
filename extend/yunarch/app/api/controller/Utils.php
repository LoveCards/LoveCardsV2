<?php

namespace yunarch\app\api\controller;

class Utils
{

    /**!!!!!!!临时的只有User还在用
     * 控制层参数过滤器(按照验证类标准参数过滤传入参数常用于-非GET请求"验证参数格式和空值管理")
     * 
     * @param array $inputParams 通过 Request::param() 获取的传入参数数组
     * @param array $standardParams 标准参数数组
     * @param array $mustParams 控制参数数组(说明：如果键存在于此数组中且值为空字符串，则设置为 false)
     * @param array $nullableParams 可为空的参数数组(说明：如果键存在于此数组中且值为空字符串，则替换为 NULL)
     * @return array 返回一个新的数组，仅包含符合条件的元素
     */
    static public function filterParams(array $inputParams, array $standardParams, array $mustParams = [], array $nullableParams = []): array
    {
        // 将控制参数数组转换为键值对，提高查找效率
        $mustParams = array_flip($mustParams);
        // 将可为空参数数组转换为键值对，提高查找效率
        $nullableParams = array_flip($nullableParams);

        // 初始化返回数组
        $filteredParams = [];

        // 遍历标准参数数组
        foreach ($standardParams as $key) {
            // 检查传入参数数组中是否存在该键
            if (array_key_exists($key, $inputParams)) {
                // 检查控制参数数组中该键是否为空
                if (!isset($mustParams[$key]) || $inputParams[$key] !== '') {
                    // 检查传入参数是否为空，为空则赋值为 ''
                    $filteredParams[$key] = $inputParams[$key] !== '' ? $inputParams[$key] : '';
                } elseif (isset($mustParams[$key])) {
                    $filteredParams[$key] = false; // 如果控制参数存在且值为空，则设置为 false
                }

                // 检查可为空参数数组
                if (isset($nullableParams[$key]) && $inputParams[$key] === '') {
                    $filteredParams[$key] = NULL; // 如果可为空参数且值为空字符串，则替换为 NULL
                }
            }
        }

        return $filteredParams;
    }
}
