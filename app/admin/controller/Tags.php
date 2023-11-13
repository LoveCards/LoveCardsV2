<?php

namespace app\admin\controller;

use think\Request as TypeRequest;
use think\facade\View;
use think\facade\Db;

use app\common\FrontEnd;

use app\admin\BaseController;

class Tags extends BaseController
{
    //中间件
    protected $middleware = [\app\admin\middleware\AdminAuthCheck::class];

    //Index
    public function Index(TypeRequest $tDef_Request)
    {
        //获取列表
        $tDef_CardsTagListMax = 12; //每页个数
        $lDef_Result = Db::table('cards_tag')
            ->paginate($tDef_CardsTagListMax, true);

        $tDef_CardsTagListEasyPagingComponent = $lDef_Result->render();
        $lDef_CardsTagList = $lDef_Result->items();

        View::assign([
            'CardsTagList'  => $lDef_CardsTagList,
            'CardsTagListEasyPagingComponent'  => $tDef_CardsTagListEasyPagingComponent,
            'CardsTagListMax'  => $tDef_CardsTagListMax
        ]);

        //通用列表
        FrontEnd::mObjectEasyAssignCommonNowList($lDef_CardsTagList,$tDef_CardsTagListEasyPagingComponent,$tDef_CardsTagListMax);

        //基础变量
        View::assign([
            'AdminData'  => $tDef_Request->attrLDefNowAdminAllData,
            'ViewTitle'  => '标签管理'
        ]);

        //输出模板
        return View::fetch('/cards-tag');
    }
}
