<?php

namespace app\index\method;

use app\common\Common;
use app\common\FrontEnd;
use app\api\model\Users as UsersModel;

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
        $tDef_EmptyData = UsersModel::Get(0);
        return Common::mArrayEasyReturnStruct($tDef_UserAllData['msg'], false, [
            'MyInfo' => $tDef_EmptyData['data'],
        ]);
    }
}
