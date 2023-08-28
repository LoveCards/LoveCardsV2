<?php

namespace app\admin\controller;

use think\Request as TypeRequest;
use think\facade\View;
use think\facade\Db;

use app\common\Common;

class Updata
{
    //中间件
    protected $middleware = [\app\admin\middleware\AdminPowerCheck::class];

    //Index
    public function Index(TypeRequest $var_t_def_request)
    {
        //基础变量
        View::assign([
            'adminData'  => $var_t_def_request->attrLDefNowAdminAllData,
            'systemVer' => Common::systemVer(),
            'viewTitle'  => '系统更新'
        ]);

        //输出模板
        return View::fetch('/updata');
    }
}
