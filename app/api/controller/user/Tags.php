<?php

namespace app\api\controller\user;

use app\api\service\Tags as TagsService;

use app\api\controller\ApiResponse;
use \app\api\controller\BaseController;

class Tags extends BaseController
{

    //获取-GET
    public function noPaginateIndex(TagsService $TagsService)
    {
        $params = [
            'search_value' => 0,
            'search_keys' => ['status'],
        ];

        //调用服务
        $lDef_Result = $TagsService->noPaginateIndex($params);
        //返回结果
        return ApiResponse::createSuccess($lDef_Result['data']);
    }
}
