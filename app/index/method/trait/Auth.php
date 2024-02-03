<?php

namespace app\index\method;

use app\common\FrontEnd;

trait Auth
{
    /**
     * 通过Cookie的TOKEN验证身份并返回数据
     *
     * @return void
     */
    public static function CookieUtokenCheck()
    {
        $tDef_AdminAllData = FrontEnd::mResultGetNowUserAllData();
        if (!$tDef_AdminAllData['status']) {
            return FrontEnd::mObjectEasyFrontEndJumpUrl('/', $tDef_AdminAllData['msg']);
        }
    }
}
