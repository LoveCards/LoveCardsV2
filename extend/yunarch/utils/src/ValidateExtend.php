<?php

namespace yunarch\utils\src;

use think\exception\ValidateException;

//通用
class ValidateExtend
{
    /**
     * 场景参数过滤器
     *
     * @param array $params 输入参数
     * @param array $specifications 场景标准参数
     * @return array
     */
    public static function sceneFilter(array $params, array $specifications): array
    {
        $result = [
            'pass' => [],
            'require' => [],
            'nonNull' => []
        ];

        // 获取规范参数，并确保它们是数组类型
        $normal = is_array($specifications['normal']) ? $specifications['normal'] : [];
        $require = is_array($specifications['require']) ? $specifications['require'] : [];
        $nonNull = is_array($specifications['nonNull']) ? $specifications['nonNull'] : [];
        $toNull = is_array($specifications['toNull']) ? $specifications['toNull'] : [];

        // 遍历传入的参数
        foreach ($params as $key => $value) {
            // 检查字段是否在规范参数中
            if (in_array($key, $require) || in_array($key, $nonNull) || in_array($key, $toNull) || in_array($key, $normal)) {
                // 处理 require 字段
                if (in_array($key, $require)) {
                    if (empty($value)) {
                        $result['require'][] = $key;
                    } else {
                        $result['pass'][$key] = $value;
                    }
                }
                // 处理 nonNull 字段
                elseif (in_array($key, $nonNull)) {
                    if (empty($value)) {
                        $result['nonNull'][] = $key;
                    } else {
                        $result['pass'][$key] = $value;
                    }
                }
                // 处理 toNull 字段
                elseif (in_array($key, $toNull)) {
                    if (empty($value)) {
                        $result['pass'][$key] = NULL;
                    } else {
                        $result['pass'][$key] = $value;
                    }
                }
                // 处理正常字段
                else {
                    $result['pass'][$key] = $value;
                }
            }
        }

        // 检查 require 字段是否缺失
        foreach ($require as $reqKey) {
            if (!isset($params[$reqKey])) {
                $result['require'][] = $reqKey;
            }
        }

        return $result;
    }

    /**
     * 验证场景消息
     *
     * @param array $filterParams 前端传入参数集
     * @param string $validateClass 
     * @return array
     */
    public static function sceneMessage(array $filterParams, $validateClass): array
    {
        $result = [];
        // 遍历过滤后的参数，检查是否有必选字段未满足
        foreach ($filterParams['require'] as $key) {
            if (isset($validateClass::${'scene_message'}[$key . '.require'])) {
                $result[$key] = $validateClass::${'scene_message'}[$key . '.require'];
            } else {
                $result[$key] = '参数 ' . $key . ' 不能为空';
            }
        }

        // 检查不可为空的字段
        foreach ($filterParams['nonNull'] as $key) {
            if (isset($validateClass::${'scene_message'}[$key . '.nonNull'])) {
                $result[$key] = $validateClass::${'scene_message'}[$key . '.nonNull'];
            } else {
                $result[$key] = '参数 ' . $key . ' 不能为空';
            }
        }

        // 验证不通过
        if (!empty($result)) {
            throw new ValidateException($result);
        };

        // 如果没有错误，返回空数组
        return $filterParams['pass'];
    }

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
    public static function paramsJsonToArray(string $keyName, array $params): array
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
