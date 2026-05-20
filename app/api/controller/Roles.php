<?php

namespace app\api\controller;

use think\facade\Request;

use app\api\service\RBAC\Roles as RolesService;
use app\api\validate\Roles as RolesValidate;

use app\api\ApiResponse;

class Roles extends BaseController
{
    public function list()
    {
        $params = $this->paramIndex(Request::param());
        $result = RolesService::Index($params);
        return ApiResponse::createOk($result);
    }

    public function get($id)
    {
        $result = RolesService::Get((int) $id);
        return ApiResponse::createOk($result);
    }

    public function create()
    {
        $params = $this->param(RolesValidate::class, RolesValidate::$all_scene['create'], Request::param());

        $id = RolesService::createRole($params);
        return ApiResponse::createOk(['id' => $id]);
    }

    public function update($id)
    {
        $params = $this->param(RolesValidate::class, RolesValidate::$all_scene['update'], Request::param());

        $id = (int) $id;
        unset($params['id']);

        RolesService::updateRole($id, $params);
        return ApiResponse::createNoContent();
    }

    public function delete($id)
    {
        RolesService::deleteRoles((int) $id);
        return ApiResponse::createNoContent();
    }

    public function assignPermissions($id)
    {
        $params = $this->param(RolesValidate::class, RolesValidate::$all_scene['assignPermissions'], Request::param());

        $roleId = (int) $id;
        $permissionHashes = json_decode($params['permission_hashes'], true);

        RolesService::assignPermissions($roleId, $permissionHashes);
        return ApiResponse::createNoContent();
    }

    public function getRolePermissions($id)
    {
        $result = RolesService::getRolePermissionHashes((int) $id);
        return ApiResponse::createOk($result);
    }
}
