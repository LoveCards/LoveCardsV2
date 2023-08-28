<?php

namespace app\admin\controller;

use think\Request as TypeRequest;
use think\facade\View;
use think\facade\Db;

use app\common\Common;

class Admin
{

    //中间件
    protected $middleware = [\app\admin\middleware\AdminPowerCheck::class];

    //Index
    public function Index(TypeRequest $var_t_def_request)
    {
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
            'adminData'  => $var_t_def_request->attrLDefNowAdminAllData,
            'systemVer' => Common::systemVer(),
            'viewTitle'  => '账号管理'
        ]);

        //输出模板
        return View::fetch('/admin');
    }
}
