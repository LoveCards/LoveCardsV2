<?php

namespace app\admin\controller;

use think\Request as TypeRequest;
use think\facade\View;
use think\facade\Db;

use app\common\Common;

use app\admin\BaseController;

class Updata extends BaseController
{
    //Index
    public function Index()
    {
        //基础变量
        View::assign([
            'AdminData'  => request()->middleware('NowAdminData'),
            'ViewTitle'  => '系统更新'
        ]);

        //输出模板
        return View::fetch($this->attrGReqView);
    }
}
