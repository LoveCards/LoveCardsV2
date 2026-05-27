<?php

namespace app\api\service\User;

use app\api\model\Users as UsersModel;
use app\api\model\Roles as RolesModel;
use app\api\service\Rbac\RBAC;
use app\api\ApiException;
use app\common\captcha\Code;
use app\api\service\Sender\Sender;

class Profile
{
    public static function get(int $uid): array
    {
        $user = UsersModel::where('id', $uid)
            ->withoutField(['password', 'deleted_at'])
            ->findOrEmpty();

        if ($user->isEmpty()) {
            throw ApiException::notFound('用户不存在', ApiException::CODE_USER_NOT_FOUND);
        }

        $userData = $user->toArray();
        $rolesId = is_array($user->roles_id) ? $user->roles_id : (json_decode($user->roles_id, true) ?: []);

        $userData['roles'] = RolesModel::whereIn('id', $rolesId)
            ->whereNull('deleted_at')
            ->field('id,name,slug')
            ->select()
            ->toArray();

        $userData['permissions'] = RBAC::getUserPermissions($rolesId);

        return $userData;
    }

    public static function update(int $uid, array $data): void
    {
        if (isset($data['password']) && $data['password'] !== '') {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }

        UsersModel::update($data, ['id' => $uid]);
    }

    public static function changePassword(int $uid, string $password): void
    {
        if ($password === '') {
            throw ApiException::badRequest('密码不可为空', ApiException::CODE_PARAM_INVALID);
        }

        UsersModel::update(['password' => password_hash($password, PASSWORD_DEFAULT)], ['id' => $uid]);
    }

    public static function changeEmail(int $uid, string $email, string $captcha): void
    {
        if (!Code::CheckCaptcha($email, strtoupper($captcha), 'Info_BindEmailCaptcha')) {
            throw ApiException::badRequest('验证码错误', ApiException::CODE_PARAM_INVALID);
        }

        UsersModel::update(['email' => $email], ['id' => $uid]);
    }

    public static function sendEmailCaptcha(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ApiException::badRequest('邮箱格式错误', ApiException::CODE_PARAM_INVALID);
        }

        $data = Code::CreateCaptcha($email, 'Info_BindEmailCaptcha', 300);
        $code = $data['data'];

        Sender::code('smtp', $email, $code);
    }
}
