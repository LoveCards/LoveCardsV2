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
    //中间件
    protected $middleware = [\app\api\middleware\AdminPowerCheck::class];

    //添加用户-POST
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
            return Export::mObjectEasyCreate($validateerror, '添加失败', 400);
        }

        //获取数据库对象
        $result = Db::table('admin');
        //整理数据
        $data = ['userName' => $userName, 'password' => sha1($password), 'power' => $power];
        //写入库
        $result->save($data);
        //返回数据
        return Export::mObjectEasyCreate([], '添加成功', 200);
    }

    //编辑用户-POST
    public function Edit()
    {
        //传入必要参数
        $id = Request::param('id');
        $userName = Request::param('userName');
        $password = Request::param('password');
        $power = Request::param('power');

        //验证ID是否正常传入
        if (empty($id)) {
            return Export::mObjectEasyCreate([], '缺少id参数', 400);
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
            return Export::mObjectEasyCreate($uservalidateerror, '编辑失败', 400);
        }

        //获取数据库对象
        $result = Db::table('admin')->where('id', $id);

        $resultAdminData = $result->find();
        //验证ID是否存在
        if (!$resultAdminData) {
            return Export::mObjectEasyCreate([], 'id不存在', 400);
        }

        //判断用户名是否修改
        if ($resultAdminData['userName'] != $userName) {
            //判断新用户名是否已存在
            if (!Db::table('admin')->where('userName', $userName)->find()) {
                $data['userName'] = $userName;
            } else {
                return Export::mObjectEasyCreate([], '用户名已存在', 400);
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
        return Export::mObjectEasyCreate([], '修改成功', 200);
    }

    //删除用户-POST
    public function Delete(TypeRequest $tDef_Request)
    {
        //传入必要参数
        $id = Request::param('id');

        //验证ID是否正常传入
        if (empty($id)) {
            return Export::mObjectEasyCreate([], '缺少id参数', 400);
        }

        if ($tDef_Request->attrGReqNowAdminAllData['id'] == $id) {
            return Export::mObjectEasyCreate(['tip' => '您不能删除您自己的账户'], '删除失败', 400);
        }
        //获取数据库对象
        $result = Db::table('admin')->where('id', $id);

        $resultAdminData = $result->find();
        //验证ID是否存在
        if (!$resultAdminData) {
            return Export::mObjectEasyCreate([], 'id不存在', 400);
        }

        $result->delete();
        return Export::mObjectEasyCreate([], '删除成功', 200);
    }
}
