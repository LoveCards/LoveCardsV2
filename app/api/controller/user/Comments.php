<?php

namespace app\api\controller\user;

use think\facade\Request;

use app\api\service\Comments as CommentsService;

use app\api\controller\BaseController;
use app\api\controller\ApiResponse;

class Comments extends BaseController
{
    //列表
    public function index()
    {
        //调用服务
        $result = CommentsService::list($this->JWT_SESSION);
        //返回结果
        return ApiResponse::createSuccess($result);
    }

    //删除
    public function delete()
    {
        try {
            CommentsService::updata($this->JWT_SESSION, ['status' => 2], ['id' => Request::param('id')]);
        } catch (\Throwable $th) {
            return ApiResponse::createError($th->getMessage());
        }
        return ApiResponse::createNoCntent();
    }
}
