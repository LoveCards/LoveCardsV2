<?php
namespace app\admin\controller;

//视图功能
use think\facade\View;
//TPDb类
use think\facade\Db;

//公共类
use app\common\Common;

class System
{

    //输出
    public function index()
    {
        //验证身份并返回数据
        $userData = Common::validateViewAuth();
        if ($userData[0] == false) {
            //跳转返回消息
            return Common::jumpUrl('/admin/login/index','请先登入');
        }

        //获取管理员用户信息
        View::assign('adminData', $userData[1]);
        //获取LC配置
        View::assign('lcSys', Common::systemVer());
        // 批量赋值
        View::assign([
            'viewTitle'  => '系统设置'
        ]);

        //获取列表
        $systemData = array_column(Db::table('system')->select()->toArray(), 'value','name');
        View::assign($systemData);

        //输出模板
        return View::fetch('/system');
    }

}