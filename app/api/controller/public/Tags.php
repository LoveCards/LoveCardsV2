<?php

namespace app\api\controller\public;

use app\api\service\Tags as TagsService;

use app\api\ApiResponse;
use \app\api\controller\BaseController;

class Tags extends BaseController
{
    public function list()
    {
        $params = [
            'where' => ['status' => 0]
        ];

        //调用服务
        $lDef_Result = TagsService::noPaginateIndex($params);
        //返回结果
        return ApiResponse::createOk($lDef_Result);
    }
}
