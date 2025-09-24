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
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, \Throwable $e): Response
    {
        if ($e instanceof api\ApiException) {
            return $e->exceptionHandle();
        }

        if ($e instanceof ValidateException) {
            // 返回验证失败的详细信息
            // return ApiResponse::createError($e->getMessage(), $e->getData(), 422);
        }

        if ($e instanceof HttpException) {
            if ($e->getStatusCode() == 404) {
                // 可以返回JSON，也可以像你之前一样重定向
                return redirect('/index/404');
                //return ApiResponse::createError('资源未找到', null, 404);
            }
            // 其他HTTP异常可以返回对应的状态码和信息
            return ApiResponse::createError($e->getMessage(), null, $e->getStatusCode());
        }

        //if (config('app.app_debug')) {
        $detail = [
            'error_class' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString()) // 格式化 trace
        ];
        // 这里可以决定是否要把详细信息暴露给API调用方
        return ApiResponse::createError('内部服务器错误 (Debug)', $detail, 500);
        //}

        return ApiResponse::createError('服务器繁忙，请稍后再试', null, 500);

        // 其他错误交给系统处理
        //return parent::render($request, $e);
    }
}
