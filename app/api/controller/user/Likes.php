<?php

namespace app\api\controller\user;

use app\common\Export;

use app\api\service\Likes as LikesService;

use app\api\controller\BaseController;
use think\facade\Request;

class Likes extends BaseController
{
    //列表
    public function list()
    {
        $result = LikesService::list($this->JWT_SESSION);
        return Export::Create($result, 200, null);
    }

    //取消点赞
    public function unLike()
    {
        try {
            //隐藏
            LikesService::delete(Request::param('id'), $this->JWT_SESSION);
        } catch (\Throwable $th) {
            return Export::Create([], $th->getCode(), $th->getMessage());
        }
        return Export::Create([]);
    }
}
