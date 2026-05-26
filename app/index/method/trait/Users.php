<?php

namespace app\index\method;

use app\index\common\Common;
use app\index\common\FrontEnd;
use app\api\service\User\Users as UsersService;

trait Users
{
    /**
     * 个人信息获取
     *
     * @return array['status','msg','data':array=>['MyInfo':array]]
     */
    public static function MyInfo()
    {
        $tDef_UserAllData = FrontEnd::mResultGetNowUserAllData();
        if ($tDef_UserAllData['status']) {
            return Common::mArrayEasyReturnStruct(null, true, [
                'MyInfo' => $tDef_UserAllData['data'],
            ]);
        }
        $user = UsersService::Get(0);
        return Common::mArrayEasyReturnStruct($tDef_UserAllData['msg'], false, [
            'MyInfo' => $user,
        ]);
    }
}
