<?php

namespace app\api\controller;

use think\facade\Request;
use think\exception\ValidateException;

use app\api\service\Users as UsersService;
use app\api\service\RBAC\RBAC;
use app\api\model\Roles as RolesModel;
use app\api\validate\Users as UsersValidate;

use app\common\captcha\Code;
use app\common\email\Email;

use app\api\ApiResponse;

class UserProfile extends BaseController
{
    public function get()
    {
        $user = UsersService::Get(request()->uid, ['id']);
        $userData = $user->toArray();

        $rolesId = request()->rolesId ?? (is_array($user->roles_id) ? $user->roles_id : (json_decode($user->roles_id, true) ?: []));
        $userData['roles'] = RolesModel::whereIn('id', $rolesId)
            ->whereNull('deleted_at')
            ->field('id,name,slug')
            ->select()
            ->toArray();
        $userData['permissions'] = RBAC::getUserPermissions($rolesId);

        return ApiResponse::createOk($userData);
    }

    public function update()
    {
        $params = [
            'id' => request()->uid,
            'avatar' => Request::param('avatar'),
            'username' => Request::param('username'),
            'password' => Request::param('password'),
        ];

        $params = $this->validateAndClean($params, '编辑失败');

        if (isset($params['password']) && $params['password']) {
            $params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);
        }

        UsersService::Patch($params['id'], array_diff($params, [null, '']));
        return ApiResponse::createNoContent();
    }

    public function password()
    {
        $params = [
            'id' => request()->uid,
            'password' => Request::param('password'),
        ];

        if (empty($params['password'])) {
            return ApiResponse::createBadRequest('编辑失败', ['密码不可为空']);
        }

        $params = $this->validateAndClean($params, '编辑失败');
        $params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);

        UsersService::Patch($params['id'], array_diff($params, [null, '']));
        return ApiResponse::createNoContent();
    }

    public function email()
    {
        $params = [
            'id' => request()->uid,
            'email' => Request::param('email'),
        ];

        $captcha = strtoupper(Request::param('captcha', ''));
        if (!Code::CheckCaptcha($params['email'], $captcha, 'Info_BindEmailCaptcha')) {
            return ApiResponse::createBadRequest('编辑失败', ['验证码错误']);
        }

        $params = $this->validateAndClean($params, '编辑失败');

        UsersService::Patch($params['id'], array_diff($params, [null, '']));
        return ApiResponse::createNoContent();
    }

    public function emailCaptcha()
    {
        $account = Request::param('email');

        if (!filter_var($account, FILTER_VALIDATE_EMAIL)) {
            return ApiResponse::createError('发送失败', ['邮箱格式错误']);
        }

        $data = Code::CreateCaptcha($account, 'Info_BindEmailCaptcha', 300);
        $code = $data['data'];
        Email::SendCaptcha($code, $account);

        return ApiResponse::createOk(['300s后失效']);
    }

    private function validateAndClean(array $params, string $errorPrefix): array
    {
        try {
            validate(UsersValidate::class)
                ->batch(true)
                ->scene('edit')
                ->check($params);
        } catch (ValidateException $e) {
            throw \app\api\ApiException::badRequest($errorPrefix, \app\api\ApiException::CODE_PARAM_INVALID, $e->getError());
        }

        return $params;
    }
}
