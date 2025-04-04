<?php

namespace app\api\controller;

use think\Request as TypeRequest;
use think\facade\Request;
use think\exception\ValidateException;
use think\facade\Db;

use app\api\validate\Admin as AdminValidate;

use app\common\Export;

class Admin
{
    //添加管理员-POST
    public function Add()
    {
        $userName = Request::param('userName');
        $password = Request::param('password');
        $power = Request::param('power');

        //验证参数是否合法
        try {
            validate(AdminValidate::class)->batch(true)
                ->scene('add')
                ->check([
                    'userName'  => $userName,
                    'password'   => $password,
                    'power' => $power
                ]);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $validateerror = $e->getError();
            return Export::Create($validateerror, 400, '添加失败');
        }

        //获取数据库对象
        $result = Db::table('admin');
        //整理数据
        $data = ['userName' => $userName, 'password' => sha1($password), 'power' => $power];
        //写入库
        $result->save($data);
        //返回数据
        return Export::Create(null, 200, null);
    }

    //编辑管理员-POST
    public function Edit()
    {
        //传入必要参数
        $id = Request::param('id');
        $userName = Request::param('userName');
        $password = Request::param('password');
        $power = Request::param('power');

        //验证ID是否正常传入
        if (empty($id)) {
            return Export::Create(null, 400, '缺少id参数');
        }

        //验证修改参数是否合法
        try {
            validate(AdminValidate::class)->batch(true)
                ->scene('edit')
                ->check([
                    'userName'  => $userName,
                    'password'   => $password,
                    'power' => $power
                ]);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $uservalidateerror = $e->getError();
            return Export::Create($uservalidateerror, 400, '编辑失败');
        }

        //获取数据库对象
        $result = Db::table('admin')->where('id', $id);

        $resultAdminData = $result->find();
        //验证ID是否存在
        if (!$resultAdminData) {
            return Export::Create(null, 400, 'id不存在');
        }

        //判断管理员名是否修改
        if ($resultAdminData['userName'] != $userName) {
            //判断新管理员名是否已存在
            if (!Db::table('admin')->where('userName', $userName)->find()) {
                $data['userName'] = $userName;
            } else {
                return Export::Create(null, 400, '管理员名已存在');
            }
        }

        //判断是否修改密码
        if (!empty($password)) {
            $data['password'] = sha1($password);
        }

        $data['power'] = $power;
        $result->data($data);

        //写入数据
        $result->update();
        return Export::Create(null, 200, null);
    }

    //删除管理员-POST
    public function Delete()
    {
        $context = request()->JwtData;

        //传入必要参数
        $id = Request::param('id');

        //验证ID是否正常传入
        if (empty($id)) {
            return Export::Create(null, 400, '缺少id参数');
        }

        if ($context['aid'] == $id) {
            return Export::Create(['您不能删除您自己的账户'], 400, '删除失败');
        }
        //获取数据库对象
        $result = Db::table('admin')->where('id', $id);

        $resultAdminData = $result->find();
        //验证ID是否存在
        if (!$resultAdminData) {
            return Export::Create(null, 400, 'id不存在');
        }

        $result->delete();
        return Export::Create(null, 200, null);
    }
}
