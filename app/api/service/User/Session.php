<?php

namespace app\api\service\User;

use app\api\model\Users as UsersModel;
use app\api\ApiException;
use app\common\infra\Jwt;
use app\api\service\Captcha\Captcha;

class Session
{
    public static function login(string $account, string $password): array
    {
        $result = UsersModel::where('number', $account)
            ->whereOr('username', $account)
            ->whereOr('email', $account)
            ->whereOr('phone', $account)
            ->find();

        if (!$result) {
            throw ApiException::unauthorized('用户不存在', ApiException::CODE_USER_NOT_FOUND);
        }

        if ($result['status'] != 0 && $result['status'] != 2) {
            throw ApiException::forbidden('您的账户已被封禁或未激活', ApiException::CODE_USER_BANNED);
        }

        if (!password_verify($password, $result['password'])) {
            throw ApiException::unauthorized('密码不匹配', ApiException::CODE_PASSWORD_MISMATCH);
        }

        $token = Jwt::sign(['uid' => $result->id]);

        return ['user' => $result, 'token' => $token];
    }

    public static function register(string $number, string $username, string $email, string $phone, string $password, array $rolesId = null, int $status = 0): array
    {
        if ($rolesId === null) {
            $rolesId = [config('system.system_roles.user')];
        }

        if ($password === '') {
            throw ApiException::badRequest('密码不得为空', ApiException::CODE_PARAM_INVALID);
        }

        $exists = null;
        if ($email !== '') {
            $exists = UsersModel::where('email', $email)->find();
        } elseif ($phone !== '') {
            $exists = UsersModel::where('phone', $phone)->find();
        }

        if ($exists) {
            throw ApiException::badRequest('邮箱或手机号已存在', ApiException::CODE_USER_ALREADY_EXISTS);
        }

        $user = UsersModel::create([
            'number'    => $number,
            'username'  => $username,
            'email'     => $email,
            'phone'     => $phone,
            'roles_id'  => $rolesId,
            'status'    => $status,
            'password'  => password_hash($password, PASSWORD_DEFAULT),
        ]);

        $token = Jwt::sign(['uid' => $user->id]);

        return ['user' => $user, 'token' => $token];
    }

    public static function guest(string $ip): array
    {
        $timekey = date('YmdH');
        $account = strtoupper(substr(md5($ip . $timekey), 0, 9)) . '@g.com';
        $password = '123456';
        $username = 'GUEST' . strtoupper(substr(md5($account . $password . time()), 0, 5));
        $number = self::generateNumber();

        $accountMap = self::resolveAccountType($account);

        try {
            return self::login($account, $password);
        } catch (ApiException $e) {
            if ($e->getCode() !== ApiException::CODE_USER_NOT_FOUND) {
                throw $e;
            }
            return self::register(
                $number,
                $username,
                $accountMap['email'],
                $accountMap['phone'],
                $password,
                [config('system.system_roles.guest')]
            );
        }
    }

    public static function sendCaptcha(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ApiException::badRequest('目前仅支持邮箱验证', ApiException::CODE_PARAM_INVALID);
        }

        Captcha::generate('code', ['to' => $email, 'scene' => 'Auth', 'ttl' => 300]);
    }

    public static function verifyCaptcha(string $email, string $code): bool
    {
        return Captcha::verify('code', ['key' => $email, 'code' => $code, 'scene' => 'Auth']);
    }

    public static function deleteCaptcha(string $email): void
    {
        $driver = Captcha::driver('code');
        if (method_exists($driver, 'delete')) {
            $driver->delete($email, 'Auth');
        }
    }

    public static function resolveAccountType(string $account, string $default = ''): array
    {
        if (preg_match('/^\d{11}$/', $account)) {
            return ['phone' => $account, 'email' => $default, 'number' => $default];
        }
        if (filter_var($account, FILTER_VALIDATE_EMAIL)) {
            return ['phone' => $default, 'email' => $account, 'number' => $default];
        }
        return ['phone' => $default, 'email' => $default, 'number' => $account];
    }

    public static function generateNumber(): string
    {
        $characters = '0123456789';
        $number = '';
        for ($i = 0; $i < 10; $i++) {
            $number .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $number;
    }
}
