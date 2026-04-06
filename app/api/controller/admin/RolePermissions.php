<?php

namespace app\api\controller\admin;

use app\api\service\RolePermissions as RolePermissionsService;
use app\api\validate\RolePermissions as RolePermissionsValidate;

use app\api\ApiResponse;

use app\api\controller\BaseController;
use app\api\Params;

use think\facade\Request;

class RolePermissions extends BaseController
{
    var $Params;

    public function __construct()
    {
        parent::__construct();
        $this->Params = new Params();
    }

    //添加权限
    public function Add()
    {
        //获取参数
        $params = $this->Params->getParams(RolePermissionsValidate::class, RolePermissionsValidate::$all_scene['admin']['add'], Request::param());
        if (gettype($params) == 'object') {
            return $params;
        }

        //调用服务
        RolePermissionsService::addPermission($params['role_id'], $params['permission_id']);
        //返回结果
        return ApiResponse::createNoContent();
    }

    //移除权限
    public function Remove()
    {
        //获取参数
        $params = $this->Params->getParams(RolePermissionsValidate::class, RolePermissionsValidate::$all_scene['admin']['remove'], Request::param());
        if (gettype($params) == 'object') {
            return $params;
        }

        //调用服务
        RolePermissionsService::removePermission($params['role_id'], $params['permission_id']);
        //返回结果
        return ApiResponse::createNoContent();
    }

    //批量添加权限
    public function BatchAdd()
    {
        //获取参数
        $params = $this->Params->getParams(RolePermissionsValidate::class, RolePermissionsValidate::$all_scene['admin']['batchAdd'], Request::param());
        if (gettype($params) == 'object') {
            return $params;
        }

        $permissionIds = json_decode($params['permission_ids'], true);

        //调用服务
        RolePermissionsService::batchAddPermissions($params['role_id'], $permissionIds);
        //返回结果
        return ApiResponse::createNoContent();
    }

    //批量移除权限
    public function BatchRemove()
    {
        //获取参数
        $params = $this->Params->getParams(RolePermissionsValidate::class, RolePermissionsValidate::$all_scene['admin']['batchRemove'], Request::param());
        if (gettype($params) == 'object') {
            return $params;
        }

        $permissionIds = json_decode($params['permission_ids'], true);

        //调用服务
        RolePermissionsService::batchRemovePermissions($params['role_id'], $permissionIds);
        //返回结果
        return ApiResponse::createNoContent();
    }
}

