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
        $characters = '0123456789';
        $number = '';
        for ($i = 0; $i < 10; $i++) {
            $number .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $number;
    }

    protected function resolveAccountType(string $account, string $default = ''): array
    {
        if (preg_match('/^\d{11}$/', $account)) {
            return ['phone' => $account, 'email' => $default, 'number' => $default];
        }
        if (filter_var($account, FILTER_VALIDATE_EMAIL)) {
            return ['phone' => $default, 'email' => $account, 'number' => $default];
        }
        return ['phone' => $default, 'email' => $default, 'number' => $account];
    }

    public function check()
    {
        return ApiResponse::createOk([]);
    }

    public function login()
    {
        $account = Request::param('account');
        $password = Request::param('password');
        $accountMap = $this->resolveAccountType($account);

        try {
            validate(UsersValidate::class)
                ->scene('login')
                ->remove(['email', 'number'])
                ->check([
                    'phone' => $accountMap['phone'],
                    'email' => $accountMap['email'],
                    'password' => $password,
                ]);
        } catch (ValidateException $e) {
            return ApiResponse::createUnauthorized('登录失败', [$e->getError()]);
        }

        $user = UsersService::Login($account, $password);
        $token = Jwt::sign(['uid' => $user->id]);

        return ApiResponse::createOk(['token' => $token]);
    }

    public function register()
    {
        $account = Request::param('account');
        $password = Request::param('password');
        $code = Request::param('code');

        if (ConfigService::get('user.captcha')) {
            if (!Code::CheckCaptcha($account, strtoupper($code), 'Auth')) {
                return ApiResponse::createUnauthorized('注册失败', ['验证码错误']);
            }
        }

        $username = 'USER' . strtoupper(substr(md5($account . $password . time()), 0, 5));
        $number = $this->generateNumber();
        $accountMap = $this->resolveAccountType($account);

        try {
            validate(UsersValidate::class)
                ->scene('register')
                ->check([
                    'phone' => $accountMap['phone'],
                    'email' => $accountMap['email'],
                    'username' => $username,
                    'password' => $password,
                ]);
        } catch (ValidateException $e) {
            return ApiResponse::createUnauthorized('注册失败', [$e->getError()]);
        }

        $user = UsersService::Register($number, $username, $accountMap['email'], $accountMap['phone'], $password);
        $token = Jwt::sign(['uid' => $user->id]);

        Code::DeleteCaptcha($account, 'Auth');

        return ApiResponse::createOk(['token' => $token]);
    }

    public function guest()
    {
        if (!ConfigService::get('core.visitor_mode')) {
            return ApiResponse::createUnauthorized('该站点未开启访客模式');
        }

        $ip = request()->ip();
        $timekey = date('YmdH');
        $account = strtoupper(substr(md5($ip . $timekey), 0, 9)) . '@g.com';
        $password = '123456';
        $username = 'GUEST' . strtoupper(substr(md5($account . $password . time()), 0, 5));
        $number = $this->generateNumber();

        $accountMap = $this->resolveAccountType($account);

        try {
            $user = UsersService::Login($account, $password);
            $token = Jwt::sign(['uid' => $user->id]);
            return ApiResponse::createOk(['token' => $token]);
        } catch (\app\api\ApiException $e) {
            if ($e->getCode() !== \app\api\ApiException::CODE_USER_NOT_FOUND) {
                throw $e;
            }
            $user = UsersService::Register($number, $username, $accountMap['email'], $accountMap['phone'], $password, [config('roles.system_roles.guest')]);
            $token = Jwt::sign(['uid' => $user->id]);
            return ApiResponse::createOk(['token' => $token]);
        }
    }

    public function captcha()
    {
        $account = Request::param('account');

        if (!filter_var($account, FILTER_VALIDATE_EMAIL)) {
            return ApiResponse::createError('发送失败', ['目前仅支持邮箱验证']);
        }

        $data = Code::CreateCaptcha($account, 'Auth', 300);
        $code = $data['data'];
        Email::SendCaptcha($code, $account);

        return ApiResponse::createNoContent();
    }

    public function logout()
    {
        return ApiResponse::createNoContent();
    }
}
