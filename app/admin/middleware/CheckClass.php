<?php

namespace app\admin\middleware;

use app\common\FrontEnd;

class CheckClass
{

    //管理员全部数据
    var $attrLDefNowAdminAllData;

    public function mObjectGetNowAdminAllData()
    {
        //验证身份并返回数据
        $this->attrLDefNowAdminAllData = FrontEnd::mResultGetNowAdminAllData();
        if (!$this->attrLDefNowAdminAllData[0]) {
            return FrontEnd::mObjectEasyFrontEndJumpUrl('/admin/login/index', '请先登入');
        }
    }

    public function mObjectEasyVerifyPower()
    {
        //权限验证
        if ($this->attrLDefNowAdminAllData[1]['power'] != 0) {
            return FrontEnd::mObjectEasyFrontEndJumpUrl('/admin/index', '权限不足');
        }
    }
}
