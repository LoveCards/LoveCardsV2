<?php

namespace app\common;

use app\common\Export;
use app\common\BackEnd;

class CheckClass
{

    //管理员全部数据
    var $attrLDefAdminAllData;

    public function mObjectGetNowAdminAllData()
    {
        $TDef_JwtData = request()->JwtData;
        //验证身份并返回数据
        $this->attrLDefAdminAllData = BackEnd::mArrayGetNowAdminAllData($TDef_JwtData['aid'])['data'];
        if (!empty($this->attrLDefAdminAllData[0])) {
            return Export::mObjectEasyCreate([], $this->attrLDefAdminAllData[1], $this->attrLDefAdminAllData[0]);
        }
    }

    public function mObjectEasyVerifyPower()
    {
        //权限验证
        if ($this->attrLDefAdminAllData['power'] != 0) {
            return Export::mObjectEasyCreate(['power' => 1], '权限不足', 401);
        }
    }
}
