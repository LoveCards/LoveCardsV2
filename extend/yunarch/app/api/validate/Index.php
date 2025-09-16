<?php

namespace yunarch\app\api\validate;

use think\Validate;

//通用超级分页查询验证类
class Index extends Validate
{
    //参数过滤场景
    static $all_scene = [
        'Defult' => [
            'normal' => [
                'page',
                'list_rows',
                'search_keys',
                'order_desc',
                'order_key',
            ],
            'require' => false,
            'nonNull' => false,
            'toNull' => [
                'search_value'
            ],
        ]
    ];

    //定义验证规则
    protected $rule =   [
        //页码
        'page'  => 'number',
        //列表长度
        'list_rows'   => 'number|between:1,1000',
        //搜索值
        // 'search_value' => '',
        //搜索字段
        'search_keys' => 'searchKey',
        //排序字段
        'order_desc' => 'stringBool',
        //排序字段
        'order_key' => 'alphaDash',
    ];
    //定义错误信息
    protected $message  =   [
        'page.number' => '页码格式错误',

        'list_rows.number' => '列表长度格式错误',
        'list_rows.between' => '列表长度范围错误(1-1000)',

        'order_desc.stringBool' => '排序方式格式错误',

        'order_key.alphaDash' => '排序字段格式错误',
    ];

    protected function searchKey($value, $rule, $data = [])
    {
        // 指定字段时，搜索值不能为空（问题不能查询空值）
        // if (!isset($data['search_value'])) {
        //     return '搜索内容不能为空';
        // }
        if (!is_array($value)) {
            return '搜索字段格式错误';
        }
        return true;
    }

    protected function stringBool($value, $rule, $data = [])
    {
        if ($value != null) {
            if ($value == 'true' || $value == 'false') {
                return true;
            } else {
                return false;
            }
        }
    }
}
