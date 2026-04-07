<?php

namespace app\api\middleware;

use app\common\Admin;

use app\api\ApiResponse;

class SessionDebounce
{
    public function handle($tDef_Request, \Closure $var_t_def_next)
    {

        //实现防抖
        $var_t_def_result = Admin::mRemindEasyDebounce('LastPostTime');
        if ($var_t_def_result[0] == false) {
            //返回数据
            return ApiResponse::createError('操作失败', [$var_t_def_result[1]]);
        }

        return $var_t_def_next($tDef_Request);
    }
}
