<?php

namespace app\api\controller;

use think\facade\Request;
use think\exception\ValidateException;

use app\api\service\Users as UsersService;
use app\api\service\Config as ConfigService;
use app\api\validate\Users as UsersValidate;

use app\common\jwt\Jwt;
use app\common\email\Email;
use app\common\captcha\Code;

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

    public function check()
    {
        return ApiResponse::createOk([]);
    }

    public function login()
    {
        $account = Request::param('account');
        $password = Request::param('password');

        $accountArray = $this->mArrayEasyCheckAccountType($account);

        try {
            validate(UsersValidate::class)
                ->scene('login')
                ->remove(['email', 'number'])
                ->check([
                    'phone'  => $accountArray['phone'],
                    'email' => $accountArray['email'],
                    'password' => $password,
                ]);
        } catch (ValidateException $e) {
            return ApiResponse::createUnauthorized('登录失败', [$e->getError()]);
        }

        $user = UsersService::Login($account, $password);

        $result = Jwt::sign(['uid' => $user->id]);
        return ApiResponse::createOk(['token' => $result]);
    }

    public function register()
    {
        $account = Request::param('account');
        $password = Request::param('password');
        $username = 'USER' . strtoupper(substr(md5($account . $password . time()), 0, 5));
        $code = Request::param('code');
        $number = $this->generateNumber();

        $accountArray = $this->mArrayEasyCheckAccountType($account);

        if (ConfigService::get('user.captcha')) {
            if (!Code::CheckCaptcha($account, strtoupper($code), 'Auth')) {
                return ApiResponse::createUnauthorized('注册失败', ['验证码错误']);
            };
        }

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
            return ApiResponse::createUnauthorized('注册失败', [$e->getError()]);
        }

        $user = UsersService::Register($number, $username, $accountArray['email'], $accountArray['phone'], $password);

        $result = Jwt::sign(['uid' => $user->id]);

        Code::DeleteCaptcha($account, 'Auth');

        return ApiResponse::createOk(['token' => $result]);
    }

    public function guest()
    {
        if (!ConfigService::get('core.visitor_mode')) {
            return ApiResponse::createUnauthorized('该站点未开启访客模式');
        }

        $timekey = (date('YmdH'));
        $ip = request()->ip();
        $account = strtoupper(substr(md5($ip . $timekey), 0, 9)) . '@g.com';
        $password = '123456';
        $username = 'GUEST' . strtoupper(substr(md5($account . $password . time()), 0, 5));
        $number = $this->generateNumber();

        $accountArray = $this->mArrayEasyCheckAccountType($account);

        try {
            $result = UsersService::Login($account, $password);
            $data = $result->toArray();
            $result = Jwt::sign(['uid' => $data['id']]);
            return ApiResponse::createOk(['token' => $result]);
        } catch (\app\api\ApiException $e) {
            if ($e->getCode() == \app\api\ApiException::CODE_USER_NOT_FOUND) {
                $user = UsersService::Register($number, $username, $accountArray['email'], $accountArray['phone'], $password, [config('roles.system_roles.guest')]);
                $result = Jwt::sign(['uid' => $user->id]);
                return ApiResponse::createOk(['token' => $result]);
            }
            throw $e;
        }
    }

    public function captcha()
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

    public function logout()
    {
        return ApiResponse::createNoContent();
    }
}
