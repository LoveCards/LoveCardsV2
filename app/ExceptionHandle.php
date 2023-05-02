<?php

namespace app;
//全局异常接管类
use think\exception\Handle;
// use think\exception\HttpException;
// use think\exception\ValidateException;
// use think\exception\ErrorException;
use think\Response;
use Throwable;

class ExceptionHandle extends Handle
{
    public function render($request, Throwable $e): Response
    {

        if ($e->getCode() == 10501) {
            dd($e->getMessage());
        }
        // 其他错误交给系统处理
        return parent::render($request, $e);
    }
}
