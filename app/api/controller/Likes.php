<?php

namespace app\api\controller;

use think\facade\Request;
use app\api\service\Content\Likes as LikesService;
use app\api\ApiResponse;

class Likes extends BaseController
{
    public function list()
    {
        $type = Request::param('type', null);
        $result = LikesService::getUserLikes(request()->uid, $type);
        return ApiResponse::createOk($result);
    }

    public function unlike($id)
    {
        $type = Request::param('type', 'card');
        LikesService::unlike($type, (int) $id, request()->uid);
        return ApiResponse::createNoContent();
    }
}
