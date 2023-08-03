<?php

namespace app\api\controller;

//TP
use think\facade\Request;
use think\exception\ValidateException;
use think\facade\Db;

//验证
use app\api\validate\Admin as AdminValidate;

//公共
use app\common\Common;
use app\api\common\Common as ApiCommon;

class Admin
{
    //添加用户-POST
    public function add()
    {
        //验证身份并返回数据
        $userData = ApiCommon::validateAuth();
        if (!empty($userData[0])) {
            return Common::create([], $userData[1], $userData[0]);
        }
        //权限验证
        if ($userData['power'] != 0) {
            return Common::create(['power' => 1], '权限不足', 401);
        }


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
            return Common::create($validateerror, '添加失败', 400);
        }

        //获取数据库对象
        $result = Db::table('admin');
        //整理数据
        $data = ['userName' => $userName, 'password' => sha1($password), 'power' => $power];
        //写入库
        $result->save($data);
        //返回数据
        return Common::create([], '添加成功', 200);
    }

    //编辑用户-POST
    public function edit()
    {
        //验证身份并返回数据
        $userData = ApiCommon::validateAuth();
        if (!empty($userData[0])) {
            return Common::create([], $userData[1], $userData[0]);
        }
        //权限验证
        if ($userData['power'] != 0) {
            return Common::create(['power' => 1], '权限不足', 401);
        }

        //传入必要参数
        $id = Request::param('id');
        $userName = Request::param('userName');
        $password = Request::param('password');
        $power = Request::param('power');

        //验证ID是否正常传入
        if (empty($id)) {
            return Common::create([], '缺少id参数', 400);
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
            return Common::create($uservalidateerror, '编辑失败', 400);
        }

        //获取数据库对象
        $result = Db::table('admin')->where('id', $id);

        $resultAdminData = $result->find();
        //验证ID是否存在
        if (!$userData) {
            return Common::create([], 'id不存在', 400);
        }

        //判断用户名是否修改
        if ($resultAdminData['userName'] != $userName) {
            //判断新用户名是否已存在
            if (!Db::table('admin')->where('userName', $userName)->find()) {
                $data['userName'] = $userName;
            } else {
                return Common::create([], '用户名已存在', 400);
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
        return Common::create([], '修改成功', 200);
    }

    //删除用户-POST
    public function delete()
    {
        //验证身份并返回数据
        $userData = ApiCommon::validateAuth();
        if (!empty($userData[0])) {
            return Common::create([], $userData[1], $userData[0]);
        }
        //权限验证
        if ($userData['power'] != 0) {
            return Common::create(['power' => 1], '权限不足', 401);
        }

        //传入必要参数
        $id = Request::param('id');

        //验证ID是否正常传入
        if (empty($id)) {
            return Common::create([], '缺少id参数', 400);
        }

        if ($userData['id'] == $id) {
            return Common::create(['tip' => '您不能删除您自己的账户'], '删除失败', 400);
        }
        //获取数据库对象
        $result = Db::table('admin')->where('id', $id);

        $resultAdminData = $result->find();
        //验证ID是否存在
        if (!$resultAdminData) {
            return Common::create([], 'id不存在', 400);
        }

        $result->delete();
        return Common::create([], '删除成功', 200);
    }
}
