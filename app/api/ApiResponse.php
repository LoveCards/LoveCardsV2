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

    /**
     * 包装成功响应
     *
     * @param mixed $data 业务数据（分页数据自动提取 pagination）
     * @param string $message 提示消息
     * @return array
     */
    public static function wrap($data = null, string $message = '操作成功'): array
    {
        $result = [
            'success' => true,
            'message' => $message,
            'timestamp' => date('c'),
        ];

        // 检测分页结构: {total, per_page, current_page, last_page, data}
        if (is_array($data) && isset($data['total'], $data['current_page'], $data['data']) && is_array($data['data'])) {
            $result['data'] = $data['data'];
            $result['pagination'] = [
                'currentPage' => (int) $data['current_page'],
                'totalPages'  => (int) ($data['last_page'] ?? 1),
                'totalItems'  => (int) $data['total'],
                'itemsPerPage' => (int) ($data['per_page'] ?? count($data['data'])),
            ];
        } else {
            $result['data'] = $data;
        }

        return $result;
    }

    /**
     * 包装错误响应
     *
     * @param string $message 错误消息
     * @param mixed $detail 错误详情
     * @param int $code 业务码
     * @return array
     */
    public static function error(string $message = '', $detail = null, int $code = 0): array
    {
        return [
            'success' => false,
            'error' => [
                'code'    => $code,
                'message' => $message,
                'details' => $detail,
            ],
            'timestamp' => date('c'),
        ];
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // 成功响应（200）
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    public static function createOk($data = null, string $message = '操作成功'): Response
    {
        $result = Response::create(self::wrap($data, $message), 'json')->code(self::HTTP_OK);
        return self::setHeader($result);
    }

    public static function createCreated(): Response
    {
        $result = Response::create(self::wrap(null, '创建成功'), 'json')->code(self::HTTP_CREATED);
        return self::setHeader($result);
    }

    public static function createNoContent(): Response
    {
        return self::setHeader(Response::create()->code(self::HTTP_NO_CONTENT));
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // 错误响应
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    public static function createBadRequest(string $message = '', $detail = null, int $code = 0): Response
    {
        return self::errorResponse(self::error($message, $detail, $code), self::HTTP_BAD_REQUEST);
    }

    public static function createUnauthorized(string $message = '', $detail = null, int $code = 0): Response
    {
        return self::errorResponse(self::error($message, $detail, $code), self::HTTP_UNAUTHORIZED);
    }

    public static function createForbidden(string $message = '', $detail = null, int $code = 0): Response
    {
        return self::errorResponse(self::error($message, $detail, $code), self::HTTP_FORBIDDEN);
    }

    public static function createNotFound(string $message = '', $detail = null, int $code = 0): Response
    {
        return self::errorResponse(self::error($message, $detail, $code), self::HTTP_NOT_FOUND);
    }

    public static function createConflict(string $message = '', $detail = null, int $code = 0): Response
    {
        return self::errorResponse(self::error($message, $detail, $code), self::HTTP_CONFLICT);
    }

    public static function createTooMany(string $message = '', $detail = null, int $code = 0): Response
    {
        return self::errorResponse(self::error($message, $detail, $code), self::HTTP_TOO_MANY);
    }

    public static function createError(string $message = '', $detail = null, int $code = 0): Response
    {
        return self::errorResponse(self::error($message, $detail, $code), self::HTTP_ERROR);
    }

    public static function createUnavailable(string $message = '', $detail = null, int $code = 0): Response
    {
        return self::errorResponse(self::error($message, $detail, $code), self::HTTP_UNAVAILABLE);
    }

    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
    // 内部方法
    // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

    private static function errorResponse(array $errorData, int $httpCode): Response
    {
        $result = Response::create($errorData, 'json')->code($httpCode);
        return self::setHeader($result);
    }
}
