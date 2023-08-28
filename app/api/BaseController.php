<?php

namespace app\api;

//公共
use app\Common\Common;
use app\api\common\Common as ApiCommon;

//TP类
use think\Facade;

class BaseController
{
    var $NowAdminAllData;

    public function __call($method, $args)
    {
        dd();
        // 需要鉴权的方法列表
        $methodsRequiringAuth = ['add', 'edit', 'delete'];

        if (in_array($method, $methodsRequiringAuth)) {
            
            $verifyResult = $this->mObjectGetNowAdminAllData();
            if ($verifyResult) {
                return $verifyResult;
            }
        }

        // 如果没有特殊处理，你可以在这里调用默认的方法处理
        // 例如：return call_user_func_array([$this, $method], $args);
    }


}
