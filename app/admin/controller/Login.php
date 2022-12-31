<?php

namespace app\admin\controller;

//视图功能
use think\facade\View;

//公共类
use app\common\Common;

class Login
{

    //输出
    public function index()
    {
        //验证身份并返回数据
        $userData = Common::validateViewAuth();
        if ($userData[0] == true) {
            //跳转返回消息
            return Common::jumpUrl('/admin/index','请不要重复登入');
        }

        //获取LC配置
        View::assign('lcSys', Common::systemVer());
        // 批量赋值
        View::assign([
            'viewTitle'  => '登入后台'
        ]);
        //输出模板
        return View::fetch('/login');
    }
}