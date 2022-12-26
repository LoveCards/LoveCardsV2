<?php

namespace app\admin\controller;

//视图功能
use think\facade\View;

//公共类
use app\common\Common;

class Index
{

    //输出
    public function index()
    {
        //验证身份并返回数据
        $userData = Common::validateViewAuth();
        if ($userData[0] == false) {
            //跳转返回消息
            return Common::jumpUrl('/admin/login/index','请先登入');
        }

        //获取用户信息
        View::assign($userData[1]);
        //获取LC配置
        View::assign(Common::systemVer());
        //获取系统配置
        View::assign(Common::systemData());
        // 批量赋值
        View::assign([
            'title'  => '总览'
        ]);
        //输出模板
        return View::fetch('/index');
    }
}