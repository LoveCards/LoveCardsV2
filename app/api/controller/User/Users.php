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

    public function allList()
    {
        $params = $this->paramIndex(Request::param());
        $result = UsersService::listAll($params);
        return ApiResponse::createOk($result);
    }

    public function allGet($id)
    {
        $result = UsersService::Get((int) $id);
        return ApiResponse::createOk($result);
    }

    public function allUpdate($id)
    {
        $params = $this->param(UsersValidate::class, UsersValidate::$all_scene['allUpdate'], Request::param());
        $params['id'] = (int) $id;

        if (array_key_exists('password', $params)) {
            $params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);
        }

        UsersService::updateAny($params['id'], $params);
        return ApiResponse::createNoContent();
    }

    public function allDelete($id)
    {
        UsersService::deleteAny((int) $id);
        return ApiResponse::createNoContent();
    }
}
