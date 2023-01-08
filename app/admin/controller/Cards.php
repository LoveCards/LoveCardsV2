<?php

namespace app\admin\controller;

//视图功能
use think\facade\View;
//TPDb类
use think\facade\Db;

//公共类
use app\common\Common;

class Cards
{
    //默认
    public function index()
    {
        //验证身份并返回数据
        $userData = Common::validateViewAuth();
        if ($userData[0] == false) {
            //跳转返回消息
            return Common::jumpUrl('/admin/login/index', '请先登入');
        }

        //获取管理员用户信息
        View::assign('adminData', $userData[1]);
        //获取LC配置
        View::assign('lcSys', Common::systemVer());
        // 批量赋值
        View::assign([
            'viewTitle'  => '卡片管理'
        ]);

        //获取列表
        $listNum = 12; //每页个数
        $list = Db::table('cards')
            ->paginate($listNum, true);
        View::assign([
            'list'  => $list,
            'listNum'  => $listNum
        ]);

        //输出模板
        return View::fetch('/cards');
    }

    //编辑
    public function edit()
    {
        //验证身份并返回数据
        $userData = Common::validateViewAuth();
        if ($userData[0] == false) {
            //跳转返回消息
            return Common::jumpUrl('/admin/login/index', '请先登入');
        }

        //传入必要参数
        $id = request()->param('id');
        //验证ID是否正常传入
        if (empty($id)) {
            //跳转返回消息
            return Common::jumpUrl('/admin/cards', '缺少id参数');
        }
        //获取数据库对象
        $result = Db::table('cards')->where('id', $id);
        $idCardData = $result->find();
        //验证ID是否存在
        if (!$idCardData) {
            //跳转返回消息
            return Common::jumpUrl('/admin/user', 'id不存在');
        }
        //获取IDCardData信息
        View::assign('idCardData', $idCardData);
        //判断是否存在图片并获取图集
        if ($idCardData['img']) {
            //获取IMG数据
            $result = Db::table('img')->where('pid', $id)->select()->toArray();
            View::assign('idImgData', $result);
        }else{
            View::assign('idImgData', false);
        }


        //获取标签数据
        $result = Db::table('cards_tag')->where('state', 0)->select()->toArray();
        View::assign('cardsTagData', $result);

        //获取管理员用户信息
        View::assign('adminData', $userData[1]);
        //获取LC配置
        View::assign('lcSys', Common::systemVer());
        // 批量赋值
        View::assign([
            'viewTitle'  => '编辑卡片'
        ]);

        //输出模板
        return View::fetch('/cards-edit');
    }
}
