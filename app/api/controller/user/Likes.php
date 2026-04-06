<?php

namespace app\api\controller\user;

use app\api\service\Likes as LikesService;

use app\api\controller\BaseController;
use think\facade\Request;

use app\api\ApiResponse;

class Likes extends BaseController
{
    //列表
    public function list()
    {
        $result = LikesService::list($this->JWT_SESSION);
        return ApiResponse::createOk($result);
    }

    //取消点赞
    public function unLike()
    {
        LikesService::delete(Request::param('id'), $this->JWT_SESSION);
        return ApiResponse::createNoContent();
    }
}
