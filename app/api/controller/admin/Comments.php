<?php

namespace app\api\controller\admin;

use app\api\validate\Comments as CommentsValidate;
use app\api\service\Comments as CommentsService;

use yunarch\validate\Common as CommonValidate;

use app\api\controller\BaseController;
use app\api\controller\Params;
use think\facade\Request;
use app\api\controller\ApiResponse;

class Comments extends BaseController
{

    var $Params;

    public function __construct(Params $Params)
    {
        parent::__construct();
        $this->Params = new Params();
    }

    //基础分页数据
    public function Index(CommentsService $CommentsService)
    {
        //获取过滤参数
        $params = $this->Params->IndexParams(Request::param());
        //调用服务
        $result = $CommentsService->newList($params);
        //返回结果
        return ApiResponse::createSuccess($result['data']);
    }

    //编辑
    public function Patch()
    {
        //获取参数
        $params = $this->Params->getParams(CommentsValidate::class, CommentsValidate::$all_scene['admin']['patch'], Request::param());
        if (gettype($params) == 'object') {
            return $params;
        }

        //调用服务
        $result = CommentsService::updateComment($params);
        //返回结果
        return ApiResponse::createSuccess($result['data']);
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
        $result = CommentsService::deleteComments($params);
        //返回数据
        return ApiResponse::createNoCntent();
    }

    //批量操作
    public function BatchOperate()
    {
        $params = $this->Params->getParams(CommonValidate::class, CommonValidate::$all_scene['BatchOperate'], Request::param());
        if (gettype($params) == 'object') {
            return $params;
        }
        $ids = json_decode($params['ids'], true);
        $result = CommentsService::batchOperate($params['method'], $ids);

        //返回数据
        return ApiResponse::createNoCntent();
    }
}
