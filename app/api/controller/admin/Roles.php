<?php

namespace app\api\controller\admin;

use app\api\service\RBAC\Roles as RolesService;
use app\api\validate\Roles as RolesValidate;

use app\api\ApiResponse;

use yunarch\validate\Common as CommonValidate;

use app\api\controller\BaseController;
use app\api\Params;

use think\facade\Request;

class Roles extends BaseController
{
    var $Params;

    public function __construct()
    {
        parent::__construct();
        $this->Params = new Params();
    }

    //基础分页数据
    public function Index()
    {
        //获取过滤参数
        $params = $this->Params->IndexParams(Request::param());
        //调用服务
        $result = RolesService::Index($params);
        //返回结果
        return ApiResponse::createOk($result);
    }

    //获取
    public function Get()
    {
        //获取参数
        $params = $this->Params->getParams(CommonValidate::class, CommonValidate::$all_scene['SingleOperate'], Request::param());
        if (gettype($params) == 'object') {
            return $params;
        }

        //调用服务
        $result = RolesService::Get($params['id']);
        //返回结果
        return ApiResponse::createOk($result);
    }

    //创建
    public function Create()
    {
        //获取参数
        $params = $this->Params->getParams(RolesValidate::class, RolesValidate::$all_scene['admin']['create'], Request::param());
        if (gettype($params) == 'object') {
            return $params;
        }

        //调用服务
        $id = RolesService::createRole($params);
        //返回结果
        return ApiResponse::createOk(['id' => $id]);
    }

    //编辑
    public function Patch()
    {
        //获取参数
        $params = $this->Params->getParams(RolesValidate::class, RolesValidate::$all_scene['admin']['patch'], Request::param());
        if (gettype($params) == 'object') {
            return $params;
        }

        $id = $params['id'];
        unset($params['id']);

        //调用服务
        RolesService::updateRole($id, $params);
        //返回结果
        return ApiResponse::createNoContent();
    }

    //删除
    public function Delete()
    {
        //获取参数
        $params = $this->Params->getParams(CommonValidate::class, CommonValidate::$all_scene['SingleOperate'], Request::param());
        if (gettype($params) == 'object') {
            return $params;
        }

        //调用服务
        RolesService::deleteRoles($params);
        //返回数据
        return ApiResponse::createNoContent();
    }

    //分配权限
    public function AssignPermissions()
    {
        //获取参数
        $params = $this->Params->getParams(RolesValidate::class, RolesValidate::$all_scene['admin']['assignPermissions'], Request::param());
        if (gettype($params) == 'object') {
            return $params;
        }

        $roleId = $params['id'];
        $permissionHashes = json_decode($params['permission_hashes'], true);

        //调用服务
        RolesService::assignPermissions($roleId, $permissionHashes);
        //返回结果
        return ApiResponse::createNoContent();
    }

    //获取角色的权限 hash 列表
    public function GetRolePermissions()
    {
        //获取参数
        $params = $this->Params->getParams(CommonValidate::class, CommonValidate::$all_scene['SingleOperate'], Request::param());
        if (gettype($params) == 'object') {
            return $params;
        }

        //调用服务
        $result = RolesService::getRolePermissionHashes($params['id']);
        //返回结果
        return ApiResponse::createOk($result);
    }
}

