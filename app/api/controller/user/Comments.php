<?php

namespace app\api\controller\user;

use think\facade\Request;


use app\api\model\Comments as CommentsModel;
use app\api\service\Comments as CommentsService;

use app\api\controller\BaseController;
use app\api\ApiResponse;
use app\api\Params;

class Comments extends BaseController
{
    //我的评论列表
    public function index()
    {
        //获取过滤参数
        $params = Params::listParams(Request::param());
        //调用服务
        $result = CommentsService::newList($params, $this->JWT_SESSION['uid']);
        //返回结果
        return ApiResponse::createOk($result);
    }

    //我的评论删除(隐藏)
    public function delete()
    {
        $result = CommentsModel::where([
            'id' => Request::param('id'),
            'user_id' => $this->JWT_SESSION['uid'],
            'status' => 0
        ])->update(['status' => 2]);

        if ($result === 0) {
            return ApiResponse::createNotFound([]);
        }
        return ApiResponse::createNoContent([]);
    }
}
