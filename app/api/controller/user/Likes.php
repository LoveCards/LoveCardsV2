<?php

namespace app\api\controller\user;

use app\api\service\Likes as LikesService;

use app\api\controller\BaseController;
use think\facade\Request;

use app\api\controller\ApiResponse;

class Likes extends BaseController
{
    //列表
    public function list()
    {
        $result = LikesService::list($this->JWT_SESSION);
        return ApiResponse::createSuccess($result);
    }

    //取消点赞
    public function unLike()
    {
        try {
            //隐藏
            LikesService::delete(Request::param('id'), $this->JWT_SESSION);
        } catch (\Throwable $th) {
            return ApiResponse::createError($th->getMessage());
        }
        return ApiResponse::createNoCntent();
    }
}
