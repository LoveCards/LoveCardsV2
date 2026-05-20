<?php

namespace app\api\controller;

use think\facade\Request;

use app\api\service\Content\Images as ImagesService;
use app\api\ApiResponse;

class Images extends BaseController
{
    public function list()
    {
        $params = ['pid' => Request::param('card_id')];

        if ($params['pid'] == null) {
            return ApiResponse::createBadRequest('参数错误(待完善接口)');
        }

        $result = ImagesService::CardIndex($params);
        return ApiResponse::createOk($result);
    }
}
