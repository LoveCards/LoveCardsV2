<?php

namespace app\admin\controller;

//视图功能
use think\facade\View;
//TPDb类
use think\facade\Db;

//公共类
use app\common\Common;

class User
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

        //获取用户信息
        View::assign($userData[1]);
        //获取LC配置
        View::assign(Common::systemVer());
        //获取系统配置
        View::assign(Common::systemData());
        // 批量赋值
        View::assign([
            'title'  => '标签管理'
        ]);

        //获取列表
        $listNum = 5; //每页个数
        $list = Db::table('user')
            ->paginate($listNum, true);
        View::assign([
            'list'  => $list,
            'listNum'  => $listNum
        ]);

        //输出模板
        return View::fetch('/user');
    }

    //添加
    public function add()
    {
        //验证身份并返回数据
        $userData = Common::validateViewAuth();
        if ($userData[0] == false) {
            //跳转返回消息
            return Common::jumpUrl('/admin/login/index', '请先登入');
        }

        //获取用户信息
        View::assign($userData[1]);
        //获取LC配置
        View::assign(Common::systemVer());
        //获取系统配置
        View::assign(Common::systemData());
        // 批量赋值
        View::assign([
            'title'  => '添加账号'
        ]);

        //输出模板
        return View::fetch('/user-add');
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
            return Common::jumpUrl('/admin/user', '缺少id参数');
        }
        //获取数据库对象
        $result = Db::table('user')->where('id', $id);
        $idData = $result->find();
        //验证ID是否存在
        if (!$idData) {
            //跳转返回消息
            return Common::jumpUrl('/admin/user', 'id不存在');
        }
        //获取ID信息
        View::assign([
            'idUserName'  => $idData['userName'],
            'idPower'  => $idData['power']
        ]);

        //获取用户信息
        View::assign($userData[1]);
        //获取LC配置
        View::assign(Common::systemVer());
        //获取系统配置
        View::assign(Common::systemData());
        // 批量赋值
        View::assign([
            'title'  => '编辑账号',
            'id'  => $id
        ]);

        //输出模板
        return View::fetch('/user-edit');
    }
}
