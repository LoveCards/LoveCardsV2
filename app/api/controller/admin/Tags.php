<?php

namespace app\api\controller\admin;

use app\api\service\Tags as TagsService;
use app\api\validate\Tags as TagsValidate;

use app\api\ApiResponse;

use yunarch\validate\Common as CommonValidate;

use app\api\controller\BaseController;
use app\api\Params;

use think\facade\Request;

class Tags extends BaseController
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
        $lDef_Result = TagsService::Index($params);
        //返回结果
        return ApiResponse::createOk($lDef_Result);
    }

    //创建
    public function Create()
    {
        //获取参数
        $params = $this->Params->getParams(TagsValidate::class, TagsValidate::$all_scene['admin']['create'], Request::param());
        if (gettype($params) == 'object') {
            return $params;
        }

        $params['user_id'] = $this->JWT_SESSION['uid'];

        //调用服务
        TagsService::createTag($params);
        //返回结果
        return ApiResponse::createOk();
    }

    //编辑
    public function Patch()
    {
        //获取参数
        $params = $this->Params->getParams(TagsValidate::class, TagsValidate::$all_scene['admin']['patch'], Request::param());
        if (gettype($params) == 'object') {
            return $params;
        }

        //调用服务
        TagsService::updateTag($params);
        //返回结果
        return ApiResponse::createOk();
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
        TagsService::deleteTags($params);
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
        TagsService::batchOperate($params['method'], $ids);

        //返回数据
        return ApiResponse::createNoContent();
    }
}
