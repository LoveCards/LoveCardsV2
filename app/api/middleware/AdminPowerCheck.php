<?php

namespace app\api\middleware;

use app\api\middleware\CheckClass;

class AdminPowerCheck extends CheckClass
{
    public function handle($var_t_def_request, \Closure $var_t_def_next)
    {
        //实现Admin鉴权并返回Admin全部数据
        $var_t_def_result = $this->mObjectGetNowAdminAllData();
        if ($var_t_def_result) {
            return $var_t_def_result;
        }
        //传递当前管理员全部数据
        $var_t_def_request->attrLDefAdminAllData = $this->attrLDefAdminAllData;


        //实现AdminPower鉴权
        $var_t_def_result = $this->mObjectEasyVerifyPower();
        if ($var_t_def_result) {
            return $var_t_def_result;
        }

        return $var_t_def_next($var_t_def_request);
    }
}
