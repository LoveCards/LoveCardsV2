<?php

namespace app\admin\controller;

use think\Request as TypeRequest;
use think\facade\View;
use think\facade\Db;
use think\facade\Config;

use app\common\Common;

class Cards
{

    //中间件
    protected $middleware = [
        \app\admin\middleware\AdminAuthCheck::class => [
            'only' => ['Index', 'Edit']
        ],
        \app\admin\middleware\AdminPowerCheck::class => [
            'only' => ['Setting']
        ]
    ];

    //Index
    public function Index(TypeRequest $var_t_def_request)
    {

        //获取列表
        $listNum = 12; //每页个数
        $list = Db::table('cards')
            ->order('id', 'desc')
            ->paginate($listNum, true);
        View::assign([
            'list'  => $list,
            'listNum'  => $listNum
        ]);

        //基础变量
        View::assign([
            'adminData'  => $var_t_def_request->attrLDefNowAdminAllData,
            'systemVer' => Common::systemVer(),
            'viewTitle'  => '卡片管理'
        ]);

        //输出模板
        return View::fetch('/cards');
    }

    //Edit
    public function Edit(TypeRequest $var_t_def_request)
    {
        //传入必要参数
        $id = request()->param('id');
        //验证ID是否正常传入
        if (empty($id)) {
            return Common::jumpUrl('/admin/cards', '缺少id参数');
        }

        //获取数据库对象
        $result = Db::table('cards')->where('id', $id);
        $idCardData = $result->find();
        //验证ID是否存在
        if (!$idCardData) {
            return Common::jumpUrl('/admin/user', 'id不存在');
        }

        //获取IDCardData信息
        View::assign('idCardData', $idCardData);
        //判断是否存在图片并获取图集
        if ($idCardData['img']) {
            //获取IMG数据
            $result = Db::table('img')->where('pid', $id)->select()->toArray();
            View::assign('idImgData', $result);
        } else {
            View::assign('idImgData', false);
        }

        //获取标签数据
        $result = Db::table('cards_tag')->where('status', 0)->select()->toArray();
        View::assign('cardsTagData', $result);

        //基础变量
        View::assign([
            'adminData'  => $var_t_def_request->attrLDefNowAdminAllData,
            'systemVer' => Common::systemVer(),
            'viewTitle'  => '编辑卡片'
        ]);

        //输出模板
        return View::fetch('/cards-edit');
    }

    //setting
    public function Setting(TypeRequest $var_t_def_request)
    {
        //获取配置
        View::assign([
            'configData' => Config::get('lovecards.api')
        ]);

        //基础变量
        View::assign([
            'adminData'  => $var_t_def_request->attrLDefNowAdminAllData,
            'systemVer' => Common::systemVer(),
            'viewTitle'  => '模块设置'
        ]);

        //输出模板
        return View::fetch('/cards-setting');
    }
}
