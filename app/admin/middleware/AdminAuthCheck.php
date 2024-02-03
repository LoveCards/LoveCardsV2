<?php

namespace app\admin\middleware;

use app\common\CheckClass;
use app\common\FrontEnd;

class AdminAuthCheck extends CheckClass
{
    public function handle($tDef_Request, \Closure $tDef_next)
    {
        //实现Admin鉴权
        $tDef_result = $this->mArrayGetNowAdminAllData();
        if ($tDef_result) {
            return $tDef_result;
        }
        //传递当前管理员全部数据
        $tDef_Request->NowAdminData = $this->attrLDefAdminAllData;

        return $tDef_next($tDef_Request);
    }
}
