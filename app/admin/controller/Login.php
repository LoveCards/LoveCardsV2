<?php

namespace app\admin\controller;

use think\Request as TypeRequest;
use think\facade\View;
use think\facade\Config;

use app\common\Common;
use app\common\FrontEnd;

use app\admin\BaseController;

class Login extends BaseController
{

    //Index
    public function Index(TypeRequest $tDef_Request)
    {
        //验证身份并返回数据
        $tDef_UserData = FrontEnd::mResultGetNowAdminAllData();
        if ($tDef_UserData[0] == true) {
            return FrontEnd::mObjectEasyFrontEndJumpUrl('/admin/index', '请不要重复登入');
        }

        //基础变量
        View::assign([
            'ViewTitle'  => '登入后台'
        ]);

        //输出模板
        return View::fetch('/login');
    }
}