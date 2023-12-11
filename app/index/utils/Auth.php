<?php

namespace app\index\utils;

use app\common\CheckClass;
use app\common\FrontEnd;
use think\facade\Request;
use app\common\Export;
use jwt\Jwt;

class Auth
{
    public static function JwtCheck()
    {
        //通过Cookie的TOKEN验证身份并返回数据
        $tDef_AdminAllData = FrontEnd::mResultGetNowUserAllData();
        if (!$tDef_AdminAllData['status']) {
            return '没有通过';
            //身份获取失败跳转并提醒
            //Cookie::delete('TOKEN'); //清除token并重定向
            //return FrontEnd::mObjectEasyFrontEndJumpUrl('/', $tDef_AdminAllData['msg']);
        }
        dd($tDef_AdminAllData);
        return '通过';
        //$token = $tDef_AdminAllData;
    }
}
