<?php

namespace app\api\controller\admin;

use app\api\service\Users as UsersService;
use app\api\validate\Users as UsersValidate;

use yunarch\validate\Common as CommonValidate;

use app\common\Export;

use \app\api\controller\BaseController;
use app\api\controller\Params;

class Users extends BaseController
{
    var $Params;

    public function __construct()
    {
        parent::__construct();
        $this->Params = new Params();
    }

    //基础分页数据
    public function Index(UsersService $UsersService)
    {
        //获取过滤参数
        $params = $this->Params->IndexParams();
        //调用服务
        $lDef_Result = $UsersService->Index($params);
        //返回结果
        return Export::Create($lDef_Result['data'], 200, null);
    }

    //编辑
    public function Patch()
    {
        //获取参数
        $params = $this->Params->getParams(UsersValidate::class, UsersValidate::$all_scene['admin']['patch']);
        if (gettype($params) == 'object') {
            return $params;
        }

        //如果密码存在则进行密码加密
        if (array_key_exists('password', $params)) {
            $params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);
        }
        //调用服务
        $tDef_Result = UsersService::Patch($params['id'], $params);
        if ($tDef_Result['status']) {
            return Export::Create(null, 200, null);
        }

        //错误返回
        $lDef_ErrorMsg = $tDef_Result['data']->getMessage();
        return Export::Create(null, 500, $lDef_ErrorMsg);
    }

    //删除
    public function Delete()
    {
        //获取参数
        $params = $this->Params->getParams(CommonValidate::class, CommonValidate::$all_scene['SingleOperate']);
        if (gettype($params) == 'object') {
            return $params;
        }

        //调用服务
        $result = UsersService::deleteUsers($params);

        //返回数据
        return Export::Create(null, 200);
    }

    //批量操作
    public function BatchOperate()
    {
        $params = $this->Params->getParams(CommonValidate::class, CommonValidate::$all_scene['BatchOperate']);
        if (gettype($params) == 'object') {
            return $params;
        }
        $ids = json_decode($params['ids'], true);
        $result = UsersService::batchOperate($params['method'], $ids);

        //返回数据
        return Export::Create(null, 200);
    }
}
