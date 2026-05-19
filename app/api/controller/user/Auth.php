<?php

namespace app\api\controller\user;

use think\facade\Request;
use think\facade\Config;
use think\exception\ValidateException;

use app\api\service\Users as UsersService;
use app\api\service\Config as ConfigService;
use app\api\validate\Users as UsersValidate;

use app\common\jwt\Jwt;
use app\common\email\Email;
use app\common\captcha\Code;

use app\api\controller\BaseController;

use app\api\ApiResponse;

class Auth extends BaseController
{
    protected function generateNumber(): string
    {
        $length = 10;
        $characters = '0123456789';
        $user_id = '';

        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $user_id .= $characters[$index];
        }

        return $user_id;
    }

    protected function mArrayEasyCheckAccountType($account, $defult = ''): array
    {
        if (preg_match('/^\d{11}$/', $account)) {
            return [
                'phone' => $account,
                'email' => $defult,
                'number' => $defult,
            ];
        } else if (filter_var($account, FILTER_VALIDATE_EMAIL)) {
            return [
                'phone' => $defult,
                'email' => $account,
                'number' => $defult,
            ];
        } else {
            return [
                'phone' => $defult,
                'email' => $defult,
                'number' => $account,
            ];
        }
    }

    //token校验
    public function Check()
    {
        return ApiResponse::createOk([]);
    }

    //登入-POST
    public function Login()
    {
        $account = Request::param('account');
        $password = Request::param('password');
        // $code = Request::param('code');

        //判断是手机号还是邮箱
        $accountArray = $this->mArrayEasyCheckAccountType($account);

        //验证器
        try {
            validate(UsersValidate::class)
                ->scene('login')
                ->remove(['email', 'number'])
                ->check([
                    'phone'  => $accountArray['phone'],
                    'email' => $accountArray['email'],
                    // 'number' => $accountArray['number'],
                    'password' => $password,
                ]);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            return ApiResponse::createUnauthorized('登录失败', [$e->getError()]);
        }

        //账号校验请求
        $user = UsersService::Login($account, $password);

        //常规密码登入
        // if ($result['status'] == false) {
        //     return ApiResponse::createUnauthorized('登录失败', [$result['msg']]);
        // }

        // if ($code != '') {
        //     //验证码登入(优先)
        //     if (!Code::CheckCaptcha($account, strtoupper($code), 'Auth')) {
        //         return ApiResponse::createUnauthorized('登入失败', ['验证码错误']);
        //     };
        //     //清除验证码
        //     Code::DeleteCaptcha($account, 'Auth');
        // }

        $result = Jwt::sign(['uid' => $user->id]);
        return ApiResponse::createOk(['token' => $result]);
    }

    //注册-POST
    public function Register()
    {
        $account = Request::param('account');
        $password = Request::param('password');
        $username = 'USER' . strtoupper(substr(md5($account . $password . time()), 0, 5));
        $code = Request::param('code');
        $number = $this->generateNumber();

        //判断是手机号还是邮箱
        $accountArray = $this->mArrayEasyCheckAccountType($account);

        //验证码校验
        if (ConfigService::get('user.captcha')) {
            if (!Code::CheckCaptcha($account, strtoupper($code), 'Auth')) {
                return ApiResponse::createUnauthorized('注册失败', ['验证码错误']);
            };
        }

        //验证器
        try {
            validate(UsersValidate::class)
                ->scene('register')
                ->check([
                    'phone'  => $accountArray['phone'],
                    'email' => $accountArray['email'],
                    'username' => $username,
                    'password' => $password,
                ]);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            return ApiResponse::createUnauthorized('注册失败', [$e->getError()]);
        }

        //写入数据
        $user = UsersService::Register($number, $username, $accountArray['email'], $accountArray['phone'], $password);

        $result = Jwt::sign(['uid' => $user->id]);

        //清除验证码
        Code::DeleteCaptcha($account, 'Auth');

        return ApiResponse::createOk(['token' => $result]);
    }

    //访客登入-POST
    public function Guest()
    {
        if (!ConfigService::get('core.visitor_mode')) {
            return ApiResponse::createUnauthorized('该站点未开启访客模式');
        }

        $timekey = (date('YmdH')); //支持一小时内重复登入
        $ip = request()->ip();
        $account = strtoupper(substr(md5($ip . $timekey), 0, 9)) . '@g.com';
        $password = '123456';
        $username = 'GUEST' . strtoupper(substr(md5($account . $password . time()), 0, 5));
        $number = $this->generateNumber();

        //判断是手机号还是邮箱
        $accountArray = $this->mArrayEasyCheckAccountType($account);

        //同IP登入
        try {
            $result = UsersService::Login($account, $password);
            //如果访客账号已存在，直接返回token
            $data = $result->toArray();
            $result = Jwt::sign(['uid' => $data['id']]);
            return ApiResponse::createOk(['token' => $result]);
        } catch (\app\api\ApiException $e) {
            if ($e->getCode() == \app\api\ApiException::CODE_USER_NOT_FOUND) {
                //写入数据
                $user = UsersService::Register($number, $username, $accountArray['email'], $accountArray['phone'], $password, [4]);
                //返回令牌
                $result = Jwt::sign(['uid' => $user->id]);
                return ApiResponse::createOk(['token' => $result]);
            }
            throw $e;
        }
    }

    //获取验证码-POST
    public function Captcha()
    {
        $account = Request::param('account');

        if (filter_var($account, FILTER_VALIDATE_EMAIL)) {
            $data = Code::CreateCaptcha($account, 'Auth', 300);
            $code = $data['data'];
            Email::SendCaptcha($code, $account);
            return ApiResponse::createNoContent();
        } else {
            return ApiResponse::createError('发送失败', ['目前仅支持邮箱验证']);
        }
    }

    //注销-POST
    public function Logout()
    {
        return ApiResponse::createNoContent();
    }
}
