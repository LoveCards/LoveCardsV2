<?php

namespace app\api;

use think\Response;

class ApiResponse
{
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // HTTP 状态码
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    public const HTTP_OK           = 200;
    public const HTTP_CREATED      = 201;
    public const HTTP_ACCEPTED     = 202;
    public const HTTP_NO_CONTENT   = 204;
    public const HTTP_BAD_REQUEST  = 400;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_FORBIDDEN   = 403;
    public const HTTP_NOT_FOUND   = 404;
    public const HTTP_CONFLICT    = 409;
    public const HTTP_TOO_MANY    = 429;
    public const HTTP_ERROR        = 500;
    public const HTTP_UNAVAILABLE = 503;

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // 响应构建
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    public static function setHeader($object)
    {
        $data = [
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Headers' => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With, X-Token',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
        ];
        return $object->header($data);
    }

    public static function createOk($data = []): Response
    {
        $result = Response::create($data, 'json')->code(self::HTTP_OK);
        return self::setHeader($result);
    }

    public static function createCreated(): Response
    {
        $result = Response::create()->code(self::HTTP_CREATED);
        return self::setHeader($result);
    }

    public static function createNoContent(): Response
    {
        $result = Response::create()->code(self::HTTP_NO_CONTENT);
        return self::setHeader($result);
    }

    public static function createBadRequest($error = '', $detail = [], $code = 0): Response
    {
        $data = self::error($error, $detail, $code);
        $result = Response::create($data, 'json')->code(self::HTTP_BAD_REQUEST);
        return self::setHeader($result);
    }

    public static function createUnauthorized($error = '', $detail = [], $code = 0): Response
    {
        $data = self::error($error, $detail, $code);
        $result = Response::create($data, 'json')->code(self::HTTP_UNAUTHORIZED);
        return self::setHeader($result);
    }

    public static function createForbidden($error = '', $detail = [], $code = 0): Response
    {
        $data = self::error($error, $detail, $code);
        $result = Response::create($data, 'json')->code(self::HTTP_FORBIDDEN);
        return self::setHeader($result);
    }

    public static function createNotFound($error = '', $detail = [], $code = 0): Response
    {
        $data = self::error($error, $detail, $code);
        $result = Response::create($data, 'json')->code(self::HTTP_NOT_FOUND);
        return self::setHeader($result);
    }

    public static function createConflict($error = '', $detail = [], $code = 0): Response
    {
        $data = self::error($error, $detail, $code);
        $result = Response::create($data, 'json')->code(self::HTTP_CONFLICT);
        return self::setHeader($result);
    }

    public static function createTooMany($error = '', $detail = [], $code = 0): Response
    {
        $data = self::error($error, $detail, $code);
        $result = Response::create($data, 'json')->code(self::HTTP_TOO_MANY);
        return self::setHeader($result);
    }

    public static function createError($error = '', $detail = [], $code = 0): Response
    {
        $data = self::error($error, $detail, $code);
        $result = Response::create($data, 'json')->code(self::HTTP_ERROR);
        return self::setHeader($result);
    }

    public static function createUnavailable($error = '', $detail = [], $code = 0): Response
    {
        $data = self::error($error, $detail, $code);
        $result = Response::create($data, 'json')->code(self::HTTP_UNAVAILABLE);
        return self::setHeader($result);
    }

    public static function error($message = '', $detail = [], $code = 0): array
    {
        return [
            'code'    => $code,
            'message' => $message,
            'detail'  => $detail,
        ];
    }
}
