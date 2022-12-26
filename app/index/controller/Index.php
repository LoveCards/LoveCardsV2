<?php

namespace app\index\controller;

//视图功能
use think\facade\View;

//公共类
use app\common\Common;

class Index
{

    //输出
    public function index()
    {
        //获取用户信息
        //View::assign($userData[1]);
        //获取LC配置
        View::assign('systemVer', Common::systemVer());
        //获取系统配置
        View::assign('systemData', Common::systemData());
        // 批量赋值
        View::assign([
            'viewTitle'  => '首页'
        ]);
        //输出模板
        return View::fetch('/index');
    }
}