<?php

namespace yunarch\app\api\controller;

class Utils
{
    /**
     * 控制层参数过滤器(按照验证类标准参数过滤传入参数常用于-非GET请求)
     * 
     * @param array $inputParams 通过 Request::param() 获取的传入参数数组
     * @param array $standardParams 标准参数数组
     * @param array $controlParams 控制参数数组
     * @return array 返回一个新的数组，仅包含符合条件的元素
     */
    public function filterParams(array $inputParams, array $standardParams, array $controlParams = []): array
    {
        // 将控制参数数组转换为键值对，提高查找效率
        $controlParams = array_flip($controlParams);

        // 初始化返回数组
        $filteredParams = [];

        // 遍历标准参数数组
        foreach ($standardParams as $key) {
            // 检查传入参数数组中是否存在该键
            if (array_key_exists($key, $inputParams)) {
                // 检查控制参数数组中该键是否为空
                if (!isset($controlParams[$key]) || $inputParams[$key] !== '') {
                    // 检查传入参数是否为空，为空则赋值为 null
                    $filteredParams[$key] = $inputParams[$key] !== '' ? $inputParams[$key] : false;
                }
            }
        }

        return $filteredParams;
    }
}
