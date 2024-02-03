<?php

namespace app\admin\controller;

use think\facade\View;
use think\facade\Request;
use think\facade\Db;

use app\common\FrontEnd;
use app\api\model\Users as UsersModel;
use app\admin\BaseController;

class Users extends BaseController
{
    //Index
    public function Index()
    {
        //基础变量
        View::assign([
            'AdminData'  => request()->middleware('NowAdminData'),
            'ViewTitle'  => '用户管理'
        ]);

        //输出模板
        return View::fetch($this->attrGReqView);
    }
}
