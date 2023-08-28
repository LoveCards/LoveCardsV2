<?php

namespace app\admin\middleware;

use app\admin\middleware\CheckClass;

class AdminAuthCheck extends CheckClass
{
    public function handle($var_t_def_request, \Closure $var_t_def_next)
    {
        //实现Admin鉴权
        $var_t_def_result = $this->mObjectGetNowAdminAllData();
        if ($var_t_def_result) {
            return $var_t_def_result;
        }
        //传递当前管理员全部数据
        $var_t_def_request->attrLDefNowAdminAllData = $this->attrLDefNowAdminAllData[1];

        return $var_t_def_next($var_t_def_request);
    }
}
