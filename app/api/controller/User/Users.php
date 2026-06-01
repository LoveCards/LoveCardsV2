<?php

namespace app\api\controller\User;

use think\facade\Request;

use app\api\service\User\Users as UsersService;
use app\api\validate\Users as UsersValidate;

use app\api\ApiResponse;
use app\api\controller\BaseController;
use app\api\controller\BatchOperateTrait;

class Users extends BaseController
{
    use BatchOperateTrait;

    protected function getBatchService(): string
    {
        return \app\api\service\User\Users::class;
    }

    public function list()
    {
        $params = $this->paramIndex(Request::param());
        $result = UsersService::listAll($params);
        return ApiResponse::createOk($result);
    }

    public function get($id)
    {
        $result = UsersService::Get((int) $id);
        return ApiResponse::createOk($result);
    }

    public function update($id)
    {
        $params = $this->param(UsersValidate::class, UsersValidate::$all_scene['allUpdate'], Request::param());
        $params['id'] = (int) $id;

        if (array_key_exists('password', $params)) {
            $params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);
        }

        $caps = request()->caps ?? [];

        // 有 users.update.all → 走 updateAny（无归属限制）
        if (in_array('users.update.all', $caps)) {
            UsersService::updateAny($params['id'], $params);
        } else {
            // users.update → 走 updateUser（有归属检查）
            UsersService::updateUser($params['id'], $params, request()->uid, $caps);
        }

        return ApiResponse::createNoContent();
    }

    public function delete($id)
    {
        $caps = request()->caps ?? [];

        // 有 users.delete.all → 走 deleteAny（无归属限制）
        if (in_array('users.delete.all', $caps)) {
            UsersService::deleteAny((int) $id);
        } else {
            // users.delete → 走 deleteUser（有归属检查）
            UsersService::deleteUser((int) $id, request()->uid, $caps);
        }

        return ApiResponse::createNoContent();
    }
}
