<?php

namespace app\api\middleware;

use app\common\BackEnd;
use app\common\Export;

class SessionDebounce
{
    public function handle($var_t_def_request, \Closure $var_t_def_next)
    {

        //实现防抖
        $var_t_def_result = BackEnd::mRemindEasyDebounce('LastPostTime');
        if ($var_t_def_result[0] == false) {
            //返回数据
            return Export::mObjectEasyCreate(['prompt' => $var_t_def_result[1]], '添加失败', 500);
        }

        return $var_t_def_next($var_t_def_request);
    }
}
