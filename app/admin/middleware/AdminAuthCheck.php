<?php

namespace app\admin\middleware;

use app\admin\middleware\CheckClass;

class AdminAuthCheck extends CheckClass
{
    public function handle($tDef_Request, \Closure $tDef_Next)
    {
        //实现Admin鉴权
        $tDef_Result = $this->mObjectGetNowAdminAllData();
        if ($tDef_Result) {
            return $tDef_Result;
        }
        //传递当前管理员全部数据
        $tDef_Request->attrLDefNowAdminAllData = $this->attrLDefNowAdminAllData[1];

        return $tDef_Next($tDef_Request);
    }
}
