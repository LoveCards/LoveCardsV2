<?php

namespace app;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;

use app\api\ApiResponse;
use app\api\ApiException;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     */
    public function report(Throwable $exception): void
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, \Throwable $e): Response
    {
        // ApiException → 标准错误格式
        if ($e instanceof api\ApiException) {
            return $e->exceptionHandle();
        }

        // ValidateException → 400 + 参数错误
        if ($e instanceof ValidateException) {
            return ApiResponse::createBadRequest(
                '参数错误',
                $e->getError(),
                ApiException::CODE_PARAM_INVALID
            );
        }

        // HttpException → 统一 JSON 响应
        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
            if ($statusCode === 404) {
                return ApiResponse::createNotFound(
                    '资源未找到',
                    ['path' => request()->pathinfo()],
                    ApiException::CODE_RESOURCE_NOT_FOUND
                );
            }
            return ApiResponse::createError($e->getMessage(), null, $statusCode);
        }

        // 其他异常
        if (env('app_debug', false)) {
            $detail = [
                'error_class' => get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString())
            ];
            return ApiResponse::createError('内部服务器错误 (Debug)', $detail, ApiException::CODE_SYSTEM_ERROR);
        }

        return ApiResponse::createError('服务器繁忙，请稍后再试', null, ApiException::CODE_SYSTEM_ERROR);
    }
}
