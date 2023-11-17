<?php

namespace app\common;

use app\common\Common;
use app\common\Export;
use app\common\BackEnd;
use app\common\FrontEnd;

use think\facade\Cookie;

class CheckClass
{

    //管理员全部数据
    var $attrLDefAdminAllData;

    //API使用
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

    //后端渲染使用
    public function mArrayGetNowAdminAllData()
    {
        //通过Cookie的TOKEN验证身份并返回数据
        $TDef_AdminAllData = FrontEnd::mResultGetNowAdminAllData();
        if (!$TDef_AdminAllData['status']) {
            //身份获取失败跳转并提醒
            //Cookie::delete('TOKEN'); //清除token并重定向
            return FrontEnd::mObjectEasyFrontEndJumpUrl('/admin/login', $TDef_AdminAllData['msg']);
        }
        $this->attrLDefAdminAllData = $TDef_AdminAllData['data'];
    }

    public function mArrayEasyVerifyPower()
    {
        //权限验证
        if ($this->attrLDefAdminAllData['power'] != 0) {
            return FrontEnd::mObjectEasyFrontEndJumpUrl('/admin', '权限不足');
        }
    }
}
