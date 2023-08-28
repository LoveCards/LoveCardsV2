<?php

namespace app\common;

use think\Facade;
use think\Response;

class Export extends Facade
{
    /**
     * @description: API格式输出方法
     * @return {*}
     * @Author: github.com/zhiguai
     * @Date: 2022-12-29 18:57:16
     * @LastEditTime: Do not edit
     * @LastEditors: github.com/zhiguai
     * @param {*} $data
     * @param {string} $msg
     * @param {int} $code
     * @param {string} $type
     */
    protected static function mObjectEasyCreate($data, string $msg = '', int $code = 200, string $type = 'json'): Response
    {
        $result = [
            //状态码
            'ec' => $code,
            //消息
            'msg' => $msg,
            //数据
            'data' => $data
        ];

        //返回API接口
        return Response::create($result, $type);
    }
}
