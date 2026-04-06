<?php

namespace app\api\controller\admin;

use app\api\service\Permissions as PermissionsService;
use app\api\validate\Permissions as PermissionsValidate;

use app\api\ApiResponse;

use yunarch\validate\Common as CommonValidate;

use app\api\controller\BaseController;
use app\api\Params;

use think\facade\Request;

class Permissions extends BaseController
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
        $result = PermissionsService::Index($params);
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
        $result = PermissionsService::Get($params['id']);
        //返回结果
        return ApiResponse::createOk($result);
    }

    //创建
    public function Create()
    {
        //获取参数
        $params = $this->Params->getParams(PermissionsValidate::class, PermissionsValidate::$all_scene['admin']['create'], Request::param());
        if (gettype($params) == 'object') {
            return $params;
        }

        //调用服务
        $id = PermissionsService::createPermission($params);
        //返回结果
        return ApiResponse::createOk(['id' => $id]);
    }

    //编辑
    public function Patch()
    {
        //获取参数
        $params = $this->Params->getParams(PermissionsValidate::class, PermissionsValidate::$all_scene['admin']['patch'], Request::param());
        if (gettype($params) == 'object') {
            return $params;
        }

        $id = $params['id'];
        unset($params['id']);

        //调用服务
        PermissionsService::updatePermission($id, $params);
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
        PermissionsService::deletePermissions($params);
        //返回数据
        return ApiResponse::createNoContent();
    }

    //获取所有权限（不分页，用于下拉选择等）
    public function All()
    {
        //获取过滤参数
        $params = $this->Params->IndexParams(Request::param());
        //调用服务
        $result = PermissionsService::noPaginateIndex($params);
        //返回结果
        return ApiResponse::createOk($result);
    }
}

