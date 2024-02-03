<?php

namespace app\admin\controller;

use think\facade\View;
use think\facade\Db;

use app\common\FrontEnd;

use app\admin\BaseController;

class Tags extends BaseController
{
    //Index
    public function Index()
    {
        //获取列表
        $tDef_TagsListMax = 12; //每页个数
        $lDef_Result = Db::table('tags')
            ->paginate($tDef_TagsListMax, true);

        $tDef_TagsListEasyPagingComponent = $lDef_Result->render();
        $lDef_TagsList = $lDef_Result->items();

        View::assign([
            'TagsList'  => $lDef_TagsList,
            'TagsListEasyPagingComponent'  => $tDef_TagsListEasyPagingComponent,
            'TagsListMax'  => $tDef_TagsListMax
        ]);

        //通用列表
        FrontEnd::mObjectEasyAssignCommonNowList($lDef_TagsList, $tDef_TagsListEasyPagingComponent, $tDef_TagsListMax);

        //基础变量
        View::assign([
            'AdminData'  => request()->middleware('NowAdminData'),
            'ViewTitle'  => '标签管理'
        ]);

        //输出模板
        return View::fetch($this->attrGReqView);
    }
}
