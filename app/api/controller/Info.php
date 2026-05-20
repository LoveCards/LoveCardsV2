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

class Info extends BaseController
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
        $lDef_ParamData = [
            'id' => request()->uid,
            'avatar' => Request::param('avatar'),
            'username' => Request::param('username'),
            'password' => Request::param('password'),
        ];

        try {
            validate(UsersValidate::class)
                ->batch(true)
                ->scene('edit')
                ->check($lDef_ParamData);
        } catch (ValidateException $e) {
            return ApiResponse::createBadRequest('编辑失败', $e->getError());
        }

        if ($lDef_ParamData['password']) {
            $lDef_ParamData['password'] = password_hash($lDef_ParamData['password'], PASSWORD_DEFAULT);
        }

        UsersService::Patch($lDef_ParamData['id'], array_diff($lDef_ParamData, [null, '']));
        return ApiResponse::createNoContent();
    }

    public function password()
    {
        $lDef_ParamData = [
            'id' => request()->uid,
            'password' => Request::param('password'),
        ];

        if (!$lDef_ParamData['password']) {
            return ApiResponse::createBadRequest('编辑失败', ['密码不可为空']);
        }

        try {
            validate(UsersValidate::class)
                ->batch(true)
                ->scene('edit')
                ->check($lDef_ParamData);
        } catch (ValidateException $e) {
            return ApiResponse::createBadRequest('编辑失败', $e->getError());
        }

        $lDef_ParamData['password'] = password_hash($lDef_ParamData['password'], PASSWORD_DEFAULT);

        UsersService::Patch($lDef_ParamData['id'], array_diff($lDef_ParamData, [null, '']));
        return ApiResponse::createNoContent();
    }

    public function email()
    {
        $lDef_ParamData = [
            'id' => request()->uid,
            'email' => Request::param('email')
        ];

        if (!Code::CheckCaptcha($lDef_ParamData['email'], strtoupper(Request::param('captcha')), 'Info_BindEmailCaptcha')) {
            return ApiResponse::createBadRequest('编辑失败', ['验证码错误']);
        };

        try {
            validate(UsersValidate::class)
                ->batch(true)
                ->scene('edit')
                ->check($lDef_ParamData);
        } catch (ValidateException $e) {
            return ApiResponse::createBadRequest('编辑失败', $e->getError());
        }

        UsersService::Patch($lDef_ParamData['id'], array_diff($lDef_ParamData, [null, '']));
        return ApiResponse::createNoContent();
    }

    public function emailCaptcha()
    {
        $account = Request::param('email');

        if (filter_var($account, FILTER_VALIDATE_EMAIL)) {
            $data = Code::CreateCaptcha($account, 'Info_BindEmailCaptcha', 300);
            $code = $data['data'];
            Email::SendCaptcha($code, $account);
            return ApiResponse::createOk(['300s后失效']);
        } else {
            return ApiResponse::createError('发送失败', ['邮箱格式错误']);
        }
    }
}
