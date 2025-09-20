<?php

namespace app\api\middleware;

use think\facade\Request;

use geetest\Gt4;

use app\api\controller\ApiResponse;

class GeetestCheck
{
    public function handle($tDef_Request, \Closure $tDef_next)
    {

        //实现gt4鉴权
        $tDef_result = gt4::validate(Request::param('lot_number'), Request::param('captcha_output'), Request::param('pass_token'), Request::param('gen_time'));
        if (!$tDef_result) {
            return ApiResponse::createUnauthorized('人机验证失败');
        }

        return $tDef_next($tDef_Request);
    }
}
