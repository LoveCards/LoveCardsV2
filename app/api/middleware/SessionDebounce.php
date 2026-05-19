<?php

namespace app\api\middleware;

use think\facade\Session;
use app\api\ApiResponse;

class SessionDebounce
{
    public function handle($tDef_Request, \Closure $var_t_def_next)
    {
        $setName = 'LastPostTime';
        $time = 6;

        if (strtotime(date("Y-m-d H:i:s")) > strtotime(Session::get($setName))) {
            Session::set($setName, date("Y-m-d H:i:s", strtotime('+' . $time . ' second')));
        } else {
            return ApiResponse::createError('操作失败', ['您的操作太快了，稍后再试试试吧']);
        }

        return $var_t_def_next($tDef_Request);
    }
}
