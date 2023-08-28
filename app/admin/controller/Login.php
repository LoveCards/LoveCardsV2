<?php

namespace app\admin\controller;

use think\Request as TypeRequest;
use think\facade\View;
use think\facade\Config;

use app\common\Common;

class Login
{

    //Index
    public function Index(TypeRequest $var_t_def_request)
    {
        //验证身份并返回数据
        $userData = Common::validateViewAuth();
        if ($userData[0] == true) {
            return Common::jumpUrl('/admin/index', '请不要重复登入');
        }

        //基础变量
        View::assign([
            'systemVer' => Common::systemVer(),
            'configData' => Config::get('lovecards'),
            'viewTitle'  => '登入后台'
        ]);

        //输出模板
        return View::fetch('/login');
    }
}
