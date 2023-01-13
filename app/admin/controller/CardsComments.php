<?php

namespace app\admin\controller;

//视图功能
use think\facade\View;
//TPDb类
use think\facade\Db;

//公共类
use app\common\Common;

class CardsComments
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
            'viewTitle'  => '评论管理'
        ]);

        //获取列表
        $listNum = 12; //每页个数
        $list = Db::table('cards_comments')
            ->paginate($listNum, true);
        View::assign([
            'list'  => $list,
            'listNum'  => $listNum
        ]);

        //输出模板
        return View::fetch('/cards-comments');
    }

    //添加
    public function add()
    {

    }

    //编辑
    public function edit()
    {
 
    }
}
