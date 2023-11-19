<?php

namespace app\admin\controller;

use think\Request as TypeRequest;
use think\facade\View;
use think\facade\Db;

use app\common\FrontEnd;

use app\admin\BaseController;

class Admin extends BaseController
{

    //Index
    public function Index()
    {
        $tDef_AdminListMax = 5;
        $lDef_Result = Db::table('admin')
            ->paginate($tDef_AdminListMax, true);

        $tDef_AdminListEasyPagingComponent = $lDef_Result->render();
        $lDef_AdminList = $lDef_Result->items();

        View::assign([
            'AdminList'  => $lDef_AdminList,
            'AdminListEasyPagingComponent'  => $tDef_AdminListEasyPagingComponent,
            'AdminListMax'  => $tDef_AdminListMax
        ]);
        //通用列表
        FrontEnd::mObjectEasyAssignCommonNowList($lDef_AdminList, $tDef_AdminListEasyPagingComponent, $tDef_AdminListMax);

        //基础变量
        View::assign([
            'AdminData'  => request()->middleware('NowAdminData'),
            'ViewTitle'  => '账号管理'
        ]);

        //输出模板
        return View::fetch($this->attrGReqView);
    }
}
