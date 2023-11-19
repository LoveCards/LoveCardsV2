<?php

namespace app\admin\controller;

use think\Request as TypeRequest;
use think\facade\View;
use think\facade\Db;

use app\common\FrontEnd;

use app\admin\BaseController;

class Comments extends BaseController
{

    //中间件
    protected $middleware = [\app\admin\middleware\AdminAuthCheck::class];

    //Index
    public function Index()
    {
        //获取列表
        $tDef_CardsCommentsListMax = 12; //每页个数
        $lDef_Result = Db::table('comments')
            ->order('cid', 'desc')
            ->paginate($tDef_CardsCommentsListMax, true);

        $tDef_CardsCommentsListEasyPagingComponent = $lDef_Result->render();
        $lDef_CardsCommentsList = $lDef_Result->items();

        View::assign([
            'CardsCommentsList'  => $lDef_CardsCommentsList,
            'CardsCommentsListEasyPagingComponent'  => $tDef_CardsCommentsListEasyPagingComponent,
            'CardsCommentsListMax'  => $tDef_CardsCommentsListMax
        ]);

        //通用列表
        FrontEnd::mObjectEasyAssignCommonNowList($lDef_CardsCommentsList, $tDef_CardsCommentsListEasyPagingComponent, $tDef_CardsCommentsListMax);

        //基础变量
        View::assign([
            'AdminData'  => request()->middleware('NowAdminData'),
            'ViewTitle'  => '评论管理'
        ]);

        //输出模板
        return View::fetch($this->attrGReqView);
    }
}
