<?php

namespace app\admin\middleware;

use app\admin\middleware\CheckClass;

class AdminPowerCheck extends CheckClass
{
    public function handle($tDef_Request, \Closure $tDef_Next)
    {
        //实现Admin鉴权并返回Admin全部数据
        $tdef_Result = $this->mObjectGetNowAdminAllData();
        if ($tdef_Result) {
            return $tdef_Result;
        }
        //传递当前管理员全部数据
        $tDef_Request->attrLDefNowAdminAllData = $this->attrLDefNowAdminAllData[1];


        //实现AdminPower鉴权
        $tdef_Result = $this->mObjectEasyVerifyPower();
        if ($tdef_Result) {
            return $tdef_Result;
        }

        return $tDef_Next($tDef_Request);
    }
}
