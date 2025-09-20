<?php

namespace app\api\controller\public;

use app\api\service\Cards as CardsService;

use app\api\controller\ApiResponse;

use app\api\controller\BaseController;

use app\api\controller\Params;

use think\facade\Request;

class Cards extends BaseController
{
    var $Params;

    public function __construct()
    {
        parent::__construct();
        $this->Params = new Params();
    }

    public function index(CardsService $CardsService)
    {
        //获取过滤参数
        $params = $this->Params->IndexParams(Request::param());
        //调用服务
        $result = $CardsService->newList($params);
        //返回结果
        return ApiResponse::createSuccess($result['data']);
    }

    public function hotList(CardsService $CardsService)
    {
        //调用服务
        $result = $CardsService->hotList();
        //返回结果
        return ApiResponse::createSuccess($result['data']);
    }
}
