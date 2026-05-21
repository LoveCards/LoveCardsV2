<?php

namespace app\api\middleware;

use think\facade\Request;

use geetest\Gt4;

use app\api\ApiResponse;

class GeetestCheck
{
    public function handle($request, \Closure $next)
    {
        $result = Gt4::validate(
            Request::param('lot_number'),
            Request::param('captcha_output'),
            Request::param('pass_token'),
            Request::param('gen_time')
        );

        if (!$result) {
            return ApiResponse::createUnauthorized('人机验证失败');
        }

        return $next($request);
    }
}
