<?php

namespace app\api\controller;

use app\api\service\Content\Likes as LikesService;
use app\api\ApiResponse;

class Likes extends BaseController
{
    public function list()
    {
        $result = LikesService::list(request()->uid);
        return ApiResponse::createOk($result);
    }

    public function unlike($id)
    {
        LikesService::delete($id, request()->uid);
        return ApiResponse::createNoContent();
    }
}
