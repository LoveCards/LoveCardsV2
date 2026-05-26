<?php

namespace app\api\controller\User;

use think\facade\Request;
use think\exception\ValidateException;

use app\api\service\User\Session as SessionService;
use app\api\service\System\Config as ConfigService;
use app\api\validate\Users as UsersValidate;

use app\api\ApiResponse;
use app\api\controller\BaseController;

class Session extends BaseController
{
    public function check()
    {
        return ApiResponse::createOk([]);
    }

    public function login()
    {
        $account = Request::param('account');
        $password = Request::param('password');
        $accountMap = SessionService::resolveAccountType($account);

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

        $result = SessionService::login($account, $password);

        return ApiResponse::createOk(['token' => $result['token']]);
    }

    public function register()
    {
        $account = Request::param('account');
        $password = Request::param('password');
        $code = Request::param('code');

        if (ConfigService::get('user.captcha')) {
            if (!SessionService::verifyCaptcha($account, $code)) {
                return ApiResponse::createUnauthorized('注册失败', ['验证码错误']);
            }
        }

        $username = 'USER' . strtoupper(substr(md5($account . $password . time()), 0, 5));
        $number = SessionService::generateNumber();
        $accountMap = SessionService::resolveAccountType($account);

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

        $result = SessionService::register($number, $username, $accountMap['email'], $accountMap['phone'], $password);

        SessionService::deleteCaptcha($account);

        return ApiResponse::createOk(['token' => $result['token']]);
    }

    public function guest()
    {
        if (!ConfigService::get('core.visitor_mode')) {
            return ApiResponse::createUnauthorized('该站点未开启访客模式');
        }

        $result = SessionService::guest(request()->ip());

        return ApiResponse::createOk(['token' => $result['token']]);
    }

    public function captcha()
    {
        $account = Request::param('account');

        try {
            SessionService::sendCaptcha($account);
        } catch (\RuntimeException $e) {
            return ApiResponse::createBadRequest($e->getMessage());
        } catch (\app\api\ApiException $e) {
            return ApiResponse::createError('发送失败', [$e->getMessage()]);
        }

        return ApiResponse::createNoContent();
    }

    public function logout()
    {
        return ApiResponse::createNoContent();
    }
}
