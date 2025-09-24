<?php

namespace app\api\controller\public;

use app\api\service\Cards as CardsService;

use app\api\ApiResponse;

use app\api\controller\BaseController;

use app\api\Params;
use app\api\ApiException;
use think\facade\Request;

class Cards extends BaseController
{
    var $Params;

    public function __construct()
    {
        parent::__construct();
        $this->Params = new Params();
    }

    public function index()
    {
        //获取过滤参数
        $params = $this->Params->IndexParams(Request::param());
        //调用服务
        $result = CardsService::newList($params);
        //返回结果
        return ApiResponse::createOk($result);
    }

    public function hotList()
    {
        //调用服务
        $result = CardsService::hotList();
        //返回结果
        return ApiResponse::createOk($result);
    }
}
