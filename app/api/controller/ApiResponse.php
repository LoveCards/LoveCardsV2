<?php

namespace app\api\controller;

use think\Response;

class ApiResponse
{
    public const CODE_SUCCESS = 200; // OK 请求成功
    public const CODE_CREATED = 201; // Created 响应头里应带 Location: /新资源永久路径
    public const CODE_ACCEPTED = 202; // Accepted 请求已接受，等待处理
    public const CODE_NO_CONTENT = 204; // No Content 请求成功，但不返回任何内容

    public const CODE_BAD_REQUEST = 400; // Bad Request 请求参数错误
    public const CODE_UNAUTHORIZED = 401; // Unauthorized 需要身份验证
    public const CODE_FORBIDDEN = 403; // Forbidden 禁止访问
    //public const CODE_NOT_FOUND = 404; // Not Found 资源未找到
    //public const CODE_METHOD_NOT_ALLOWED = 405; // Method Not Allowed 请求方法不被允许
    //public const CODE_NOT_ACCEPTABLE = 406; // Not Acceptable 请求的格式不被支持
    public const CODE_CONFLICT = 409; // Conflict 请求冲突，例如资源已存在

    public const CODE_ERROR = 500; // Internal Server Error 服务器内部错误
    //public const CODE_NOT_IMPLEMENTED = 501; // Not Implemented 服务器不支持请求的功能
    //public const CODE_BAD_GATEWAY = 502; // Bad Gateway 无效响应
    public const CODE_UNAVAILABLE = 503; // Service Unavailable 服务器当前无法处理请求
    //public const CODE_GATEWAY_TIMEOUT = 504; // Gateway Timeout 网关超时

    public static function setHeader($object)
    {
        $data = array(
            //'Access-Control-Allow-Credentials' => 'true', // 允许携带cookie (暂时用不到)
            'Access-Control-Allow-Origin' => '*', // 允许所有来源
            'Access-Control-Allow-Headers' => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With, X-Token', // 允许的请求头
            //'Access-Control-Expose-Headers' => 'Token', // 允许客户端获取的响应头
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, PATCH, OPTIONS', // 允许的HTTP方法
            //'Cache-control' => 'no-cache,must-revalidate' // 禁止缓存
        );
        return $object->header($data);
    }

    /**
     * 200
     *
     * @param mixed $data
     * @return object
     */
    public static function createSuccess($data = []): object
    {
        $result = Response::create($data, 'json')->code(self::CODE_SUCCESS);
        $result = self::setHeader($result);
        return $result;
    }

    /**
     * 201
     *
     * @return object
     */
    public static function createCreated(): object
    {
        $result = Response::create('', 'json')->code(self::CODE_CREATED);
        $result = self::setHeader($result);
        return $result;
    }

    public static function createNoCntent(): object
    {
        $result = Response::create('', 'json')->code(self::CODE_NO_CONTENT);
        $result = self::setHeader($result);
        return $result;
    }

    /**
     * 400
     *
     * @param string $error 错误提示
     * @param array $detail 详细信息
     * @return object
     */
    public static function createBadRequest($error = '', $detail = []): object
    {
        $data = ['error' => $error, 'detail' => $detail];
        $result = Response::create($data, 'json')->code(self::CODE_BAD_REQUEST);
        $result = self::setHeader($result);
        return $result;
    }

    /**
     * 401
     *
     * @param string $error 错误提示
     * @param array $detail 详细信息
     * @return object
     */
    public static function createUnauthorized($error = '', $detail = []): object
    {
        $data = ['error' => $error, 'detail' => $detail];
        $result = Response::create($data, 'json')->code(self::CODE_UNAUTHORIZED);
        $result = self::setHeader($result);
        return $result;
    }

    public static function createError($error = '', $detail = []): object
    {
        $data = ['error' => $error, 'detail' => $detail];
        $result = Response::create($data, 'json')->code(self::CODE_ERROR);
        $result = self::setHeader($result);
        return $result;
    }
}
