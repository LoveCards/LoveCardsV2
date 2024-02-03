<?php

namespace app\admin\controller;

use think\Request as TypeRequest;
use think\facade\View;
use think\facade\Db;
use think\facade\Config;

use app\common\Common;
use app\common\FrontEnd;

use app\admin\BaseController;
use app\api\model\Images as ImagesModel;

class Cards extends BaseController
{
    //Index
    public function Index()
    {

        //获取列表
        $tDef_CardsListMax = 12;
        $lDef_Result = Db::table('cards')
            ->order('id', 'desc')
            ->paginate($tDef_CardsListMax, true);

        $tDef_CardsEasyPagingComponent = $lDef_Result->render();
        $lDef_CardsList = $lDef_Result->items();
        FrontEnd::mObjectEasyAssignCards($lDef_CardsList, $tDef_CardsEasyPagingComponent, $tDef_CardsListMax);
        //通用列表
        FrontEnd::mObjectEasyAssignCommonNowList($lDef_CardsList, $tDef_CardsEasyPagingComponent, $tDef_CardsListMax);

        //基础变量
        View::assign([
            'AdminData'  => request()->middleware('NowAdminData'),
            'ViewTitle'  => '卡片管理'
        ]);

        //输出模板
        return View::fetch($this->attrGReqView);
    }

    //Edit
    public function Edit()
    {
        //传入必要参数
        $tDef_Id = request()->param('id');
        //验证ID是否正常传入
        if (empty($tDef_Id)) {
            return FrontEnd::mObjectEasyFrontEndJumpUrl('/admin/cards', '缺少id参数');
        }

        //获取数据库对象
        $lDef_Result = Db::table('cards')->where('id', $tDef_Id);
        $tDef_CardData = $lDef_Result->find();
        //验证ID是否存在
        if (!$tDef_CardData) {
            return FrontEnd::mObjectEasyFrontEndJumpUrl('/admin/user', 'id不存在');
        }

        //获取IDCardData信息
        View::assign('CardData', $tDef_CardData);
        //判断是否存在图片并获取图集
        if ($tDef_CardData['img']) {
            //获取IMG数据
            $lDef_Result = ImagesModel::where('pid', $tDef_Id)->select()->toArray();
            View::assign('CardImgList', $lDef_Result);
        } else {
            View::assign('CardImgList', false);
        }

        //获取标签数据
        FrontEnd::mObjectEasyGetAndAssignCardsTags(true);

        //基础变量
        View::assign([
            'AdminData'  => request()->middleware('NowAdminData'),
            'ViewTitle'  => '编辑卡片'
        ]);

        //输出模板
        return View::fetch($this->attrGReqView);
    }

    //setting
    public function Setting()
    {
        //基础变量
        View::assign([
            'AdminData'  => request()->middleware('NowAdminData'),
            'ViewTitle'  => '模块设置'
        ]);

        //输出模板
        return View::fetch($this->attrGReqView);
    }
}
