<?php

namespace app\api\controller\user;

use app\api\service\Content\Likes as LikesService;

use app\api\controller\BaseController;
use think\facade\Request;

use app\api\ApiResponse;

class Likes extends BaseController
{
    //列表
    public function list()
    {
        $result = LikesService::list(request()->uid);
        return ApiResponse::createOk($result);
    }

    //取消点赞
    public function unLike()
    {
        LikesService::delete(Request::param('id'), request()->uid);
        return ApiResponse::createNoContent();
    }
}
