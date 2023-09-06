<?php

namespace app\api\controller;

use think\Request as TypeRequest;
use think\facade\Request;
use think\exception\ValidateException;
use think\facade\Db;

use app\api\validate\Admin as AdminValidate;

use app\common\Export;
use app\common\BackEnd;

class Auth
{

    //中间件
    protected $middleware = [
        \app\api\middleware\AdminAuthCheck::class => [
            'only' => [
                'Logout'
            ]
        ],
    ];

    //登入-POST
    public function Login()
    {

        //人机二次验证
        $gt4 = new \geetest\gt4();
        if (!$gt4::validate(Request::param('lot_number'), Request::param('captcha_output'), Request::param('pass_token'), Request::param('gen_time'))) {
            return Export::mObjectEasyCreate(['prompt' => '人机验证失败'], '添加失败', 500);
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
            return Export::mObjectEasyCreate($uservalidateerror, '登入失败', 401);
        }

        //获取数据对象
        $result = Db::table('admin')
            ->where('userName', $userName)
            ->where('password', sha1($password));

        //验证账号是否否存在
        if (empty($result->find())) {
            return Export::mObjectEasyCreate([], '用户名或密码错误', 401);
        } else {
            //整理数据
            $uuid = BackEnd::mStringGenerateUUID();
            //更新uuid
            $result->update(['uuid' => $uuid]);
            //整理返回数据
            $data = [
                'uuid' => $uuid
            ];
            //返回数据
            return Export::mObjectEasyCreate($data, '登录成功', 200);
        }
    }

    //注销-POST
    public function Logout(TypeRequest $tDef_Request)
    {
        //获取数据对象
        $result = Db::table('admin')
            ->where('id', $tDef_Request->attrLDefAdminAllData['id']);

        //整理数据
        $uuid = Null;
        //更新uuid
        $result->update(['uuid' => $uuid]);

        //返回数据
        return Export::mObjectEasyCreate([], '注销成功', 200);
    }
}
