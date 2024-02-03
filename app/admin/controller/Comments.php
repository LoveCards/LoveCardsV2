<?php

namespace app\admin\controller;

use think\Request as TypeRequest;
use think\facade\View;
use think\facade\Db;

use app\common\FrontEnd;

use app\admin\BaseController;

class Comments extends BaseController
{

    //Index
    public function Index()
    {
        //获取列表
        $tDef_CommentsListMax = 12; //每页个数
        $lDef_Result = Db::table('comments')
            ->order('pid', 'desc')
            ->paginate($tDef_CommentsListMax, true);

        $tDef_CommentsListEasyPagingComponent = $lDef_Result->render();
        $lDef_CommentsList = $lDef_Result->items();

        View::assign([
            'CommentsList'  => $lDef_CommentsList,
            'CommentsListEasyPagingComponent'  => $tDef_CommentsListEasyPagingComponent,
            'CommentsListMax'  => $tDef_CommentsListMax
        ]);

        //通用列表
        FrontEnd::mObjectEasyAssignCommonNowList($lDef_CommentsList, $tDef_CommentsListEasyPagingComponent, $tDef_CommentsListMax);

        //基础变量
        View::assign([
            'AdminData'  => request()->middleware('NowAdminData'),
            'ViewTitle'  => '评论管理'
        ]);

        //输出模板
        return View::fetch($this->attrGReqView);
    }
}
