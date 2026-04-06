<?php

namespace app\api\controller\admin;

use app\api\service\Users as UsersService;
use app\api\validate\Users as UsersValidate;

use yunarch\validate\Common as CommonValidate;

use app\api\ApiResponse;

use \app\api\controller\BaseController;
use app\api\Params;

use think\facade\Request;

class Users extends BaseController
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
        $list = UsersService::Index($params);
        //返回结果
        return ApiResponse::createOk($list);
    }

    //编辑
    public function Patch()
    {
        //获取参数
        $params = $this->Params->getParams(UsersValidate::class, UsersValidate::$all_scene['admin']['patch'], Request::param());
        if (gettype($params) == 'object') {
            return $params;
        }

        //如果密码存在则进行密码加密
        if (array_key_exists('password', $params)) {
            $params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);
        }
        //调用服务
        UsersService::Patch($params['id'], $params);
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
        UsersService::deleteUsers($params);

        //返回数据
        return ApiResponse::createNoContent();
    }

    //批量操作
    public function BatchOperate()
    {
        $params = $this->Params->getParams(CommonValidate::class, CommonValidate::$all_scene['BatchOperate'], Request::param());
        if (gettype($params) == 'object') {
            return $params;
        }
        $ids = json_decode($params['ids'], true);
        UsersService::batchOperate($params['method'], $ids);

        //返回数据
        return ApiResponse::createNoContent();
    }
}
