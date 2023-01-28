<?php

namespace app\admin\controller;

//TP类
use think\facade\View;
use think\facade\Db;

//类
use app\common\Common;

class System
{
    
    //Index
    public function index()
    {
        //验证身份并返回数据
        $userData = Common::validateViewAuth();
        if ($userData[0] == false) {
            //跳转返回消息
            return Common::jumpUrl('/admin/login/index', '请先登入');
        }
        //验证权限
        if ($userData[1]['power'] != 0) {
            return Common::jumpUrl('/admin/index', '权限不足');
        }

        //取系统数据
        $systemData = array_column(Db::table('system')->select()->toArray(), 'value', 'name');
        View::assign($systemData);

        //基础变量
        View::assign([
            'adminData'  => $userData[1],
            'systemVer' => Common::systemVer(),
            'viewTitle'  => '系统设置'
        ]);

        //输出模板
        return View::fetch('/system');
    }
}
