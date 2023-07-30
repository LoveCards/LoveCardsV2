<?php

namespace app\api\controller;

//TP
use think\facade\Request;
use think\exception\ValidateException;
use think\facade\Db;

//验证
use app\api\validate\Admin as AdminValidate;

//公共
use app\Common\Common;
use app\api\common\Common as ApiCommon;

//第三方
require($_SERVER['DOCUMENT_ROOT'] . '/../class/geetest/gt4.php');

use class\geetest\gt4\base as gt4;

class Auth
{

    //登入-POST
    public function login()
    {
        
        //人机二次验证
        if (!gt4::validate(Request::param('lot_number'), Request::param('captcha_output'), Request::param('pass_token'), Request::param('gen_time'))) {
            return ApiCommon::create(['prompt' => '人机验证失败'], '添加失败', 500);
        }

        $userName = Request::param('userName');
        $password = Request::param('password');

        //验证参数是否合法
        try {
            validate(AdminValidate::class)->batch(true)
                ->scene('login')
                ->check([
                    'userName'  => $userName,
                    'password'   => $password,
                ]);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $uservalidateerror = $e->getError();
            return ApiCommon::create($uservalidateerror, '登入失败', 401);
        }

        //获取数据对象
        $result = Db::table('admin')
            ->where('userName', $userName)
            ->where('password', sha1($password));

        //验证账号是否否存在
        if (empty($result->find())) {
            return ApiCommon::create([], '用户名或密码错误', 401);
        } else {
            //整理数据
            $uuid = ApiCommon::get_uuid();
            //更新uuid
            $result->update(['uuid' => $uuid]);
            //整理返回数据
            $data = [
                'uuid' => $uuid
            ];
            //返回数据
            return ApiCommon::create($data, '登录成功', 200);
        }
    }

    //注销-POST
    public function logout()
    {
        //验证身份并返回数据
        $userData = ApiCommon::validateAuth();
        if (!empty($userData[0])) {
            return ApiCommon::create([], $userData[1], $userData[0]);
        }

        //获取数据对象
        $result = Db::table('admin')
            ->where('id', $userData['id']);

        //整理数据
        $uuid = Null;
        //更新uuid
        $result->update(['uuid' => $uuid]);

        //返回数据
        return ApiCommon::create([], '注销成功', 200);
    }
}
