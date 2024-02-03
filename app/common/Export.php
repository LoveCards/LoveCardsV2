<?php

namespace app\common;

use think\Facade;
use think\Response;

class Export extends Facade
{

    public static function setHeader($object, $context = null)
    {
        $data = array(
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With, X-Token',
            'Access-Control-Expose-Headers' => 'Token',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS, PATCH',
            'Cache-control' => 'no-cache,must-revalidate'
        );
        //检查中间件的校验结果并设置header新的Token
        if ($context != null) {
            if ($context['token'] != null) {
                $data['Token'] = $context['token'];
            }
        }
        return $object->header($data);
    }

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
    public static function Create($data, int $code = 200, string $error = null, string $type = 'json'): Response
    {
        $context = request()->JwtData;
        if ($error != null) {
            $data ?: $data = [];
            $response = self::setHeader(Response::create(['error' => $error, 'detail' => $data], $type)->code($code), $context);
        } else {
            if ($data != null) {
                $response = self::setHeader(Response::create($data, $type)->code($code), $context);
            } else {
                $response = self::setHeader(Response::create([], $type)->code($code), $context);
            }
        }

        //返回API接口
        return $response;
    }

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
    public static function mObjectEasyCreate($data, string $msg = '', int $code = 200, string $type = 'json'): Response
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
