<?php

namespace app\api\controller;

use think\Request as TypeRequest;
use think\facade\Request;
use think\exception\ValidateException;
use think\facade\Db;

use app\api\validate\Admin as AdminValidate;

use app\common\Export;
use app\common\BackEnd;
use jwt\Jwt;

class Auth
{
    //登入-POST
    public function Login()
    {

        //人机二次验证
        $gt4 = new \geetest\Gt4();
        if (!$gt4::validate(Request::param('lot_number'), Request::param('captcha_output'), Request::param('pass_token'), Request::param('gen_time'))) {
            return Export::Create([], 500, '人机验证失败');
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
            return Export::Create($uservalidateerror, 401, '登入失败');
        }

        //获取数据对象
        $result = Db::table('admin')
            ->where('userName', $userName)
            ->where('password', sha1($password));

        //验证账号是否否存在
        $tDef_UserData = $result->find();
        if (empty($tDef_UserData)) {
            return Export::Create([], 401, '用户名或密码错误');
        } else {
            $tDef_Token = Jwt::signToken(['aid' => $tDef_UserData['id']]);
            //返回数据
            return Export::Create(['token' => $tDef_Token], 200);
        }
    }

    //注销-POST
    public function Logout()
    {
        //获取数据对象
        return Export::Create(null, 200);
    }
}
