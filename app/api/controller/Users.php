<?php

namespace app\api\controller;

use think\facade\Request;

use app\api\service\Users as UsersService;
use app\api\validate\Users as UsersValidate;

use yunarch\validate\Common as CommonValidate;

use app\api\ApiResponse;

class Users extends BaseController
{
    use BatchOperateTrait;

    protected function getBatchService(): string
    {
        return \app\api\service\Users::class;
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
