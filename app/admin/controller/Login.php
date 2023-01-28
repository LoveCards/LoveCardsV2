<?php

namespace app\admin\controller;

//TP类
use think\facade\View;

//类
use app\common\Common;

class Login
{

    //Index
    public function index()
    {
        //验证身份并返回数据
        $userData = Common::validateViewAuth();
        if ($userData[0] == true) {
            return Common::jumpUrl('/admin/index','请不要重复登入');
        }

        //基础变量
        View::assign([
            'systemVer' => Common::systemVer(),
            'viewTitle'  => '登入后台'
        ]);

        //输出模板
        return View::fetch('/login');
    }
}