<?php

namespace yunarch\validate;

use think\Validate;
use think\exception\ValidateException;

//通用
class Common extends Validate
{
    //参数过滤场景
    static public $all_scene = [
        // 'example' => [
        //     'normal' => [], //正常字段
        //     'require' => false, //必选字段
        //     'nonNull' => false, //不可为空的字段
        //     'toNull' => false, //空转NULL字段
        // ],
        'SingleOperate' => [
            'normal' => false,
            'require' => ['id'],
            'nonNull' => false,
            'toNull' => false,
        ],
        'BatchOperate' => [
            'normal' => false,
            'require' => [
                'ids',
                'method'
            ],
            'nonNull' => false,
            'toNull' => false,
        ],
    ];
    //场景验证消息
    static public $scene_message = [
        'ids.require' => 'ID集不能为空',
        'method.require' => '方法不能为空',
    ];

    /**
     * 场景参数过滤器
     *
     * @param array $params 输入参数
     * @param array $specifications 场景标准参数
     * @return array
     */
    // static public function sceneFilter($params, $specifications)
    // {
    //     $result = [
    //         'pass' => [],
    //         'require' => [],
    //         'nonNull' => []
    //     ];

    //     // 获取规范参数，并确保它们是数组类型
    //     $normal = is_array($specifications['normal']) ? $specifications['normal'] : [];
    //     $require = is_array($specifications['require']) ? $specifications['require'] : [];
    //     $nonNull = is_array($specifications['nonNull']) ? $specifications['nonNull'] : [];
    //     $toNull = is_array($specifications['toNull']) ? $specifications['toNull'] : [];

    //     // 遍历传入的参数
    //     foreach ($params as $key => $value) {
    //         // 检查字段是否在规范参数中
    //         if (in_array($key, $require) || in_array($key, $nonNull) || in_array($key, $toNull) || in_array($key, $normal)) {
    //             // 处理 require 字段
    //             if (in_array($key, $require)) {
    //                 if (empty($value)) {
    //                     $result['require'][] = $key;
    //                 } else {
    //                     $result['pass'][$key] = $value;
    //                 }
    //             }
    //             // 处理 nonNull 字段
    //             elseif (in_array($key, $nonNull)) {
    //                 if (empty($value)) {
    //                     $result['nonNull'][] = $key;
    //                 } else {
    //                     $result['pass'][$key] = $value;
    //                 }
    //             }
    //             // 处理 toNull 字段
    //             elseif (in_array($key, $toNull)) {
    //                 if (empty($value)) {
    //                     $result['pass'][$key] = NULL;
    //                 } else {
    //                     $result['pass'][$key] = $value;
    //                 }
    //             }
    //             // 处理正常字段
    //             else {
    //                 $result['pass'][$key] = $value;
    //             }
    //         }
    //     }

    //     // 检查 require 字段是否缺失
    //     foreach ($require as $reqKey) {
    //         if (!isset($params[$reqKey])) {
    //             $result['require'][] = $reqKey;
    //         }
    //     }

    //     return $result;
    // }

    /**
     * 验证场景消息
     *
     * @param array $filterParams 
     * @param string $validateClass 
     * @return array
     */
    // static public function sceneMessage(array $filterParams, $validateClass = self::class): array
    // {
    //     $result = [];
    //     // 遍历过滤后的参数，检查是否有必选字段未满足
    //     foreach ($filterParams['require'] as $key) {
    //         if (isset($validateClass::${'scene_message'}[$key . '.require'])) {
    //             $result[$key] = $validateClass::${'scene_message'}[$key . '.require'];
    //         } else {
    //             $result[$key] = '参数 ' . $key . ' 不能为空';
    //         }
    //     }

    //     // 检查不可为空的字段
    //     foreach ($filterParams['nonNull'] as $key) {
    //         if (isset($validateClass::${'scene_message'}[$key . '.nonNull'])) {
    //             $result[$key] = $validateClass::${'scene_message'}[$key . '.nonNull'];
    //         } else {
    //             $result[$key] = '参数 ' . $key . ' 不能为空';
    //         }
    //     }

    //     // 验证不通过
    //     if (!empty($result)) {
    //         throw new ValidateException($result);
    //     };

    //     // 如果没有错误，返回空数组
    //     return $filterParams['pass'];
    // }

    //定义验证规则
    protected $rule =   [
        'id'  => 'number',
        'ids'  => 'arrayJson',
        'method'  => 'alpha',
    ];

    //定义错误信息
    protected $message  =   [
        'ids.arrayJson' => 'ID集格式错误',
        'method.alpha' => '方法格式错误',
    ];
}
