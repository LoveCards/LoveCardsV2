<?php

namespace app\admin\controller;

//TP类
use think\facade\View;
use think\facade\Db;

//类
use app\common\Common;

class Admin
{
    //Index
    public function index()
    {
        //验证身份并返回数据
        $userData = Common::validateViewAuth();
        if ($userData[0] == false) {
            return Common::jumpUrl('/admin/login/index', '请先登入');
        }
        //验证权限
        if ($userData[1]['power'] != 0) {
            return Common::jumpUrl('/admin/index', '权限不足');
        }

        //获取列表
        $listNum = 5;
        $list = Db::table('admin')
            ->paginate($listNum, true);
        View::assign([
            'list'  => $list,
            'listNum'  => $listNum
        ]);

        //基础变量
        View::assign([
            'adminData'  => $userData[1],
            'systemVer' => Common::systemVer(),
            'systemData' => Common::systemData(),
            'viewTitle'  => '账号管理'
        ]);

        //输出模板
        return View::fetch('/admin');
    }
}
