<?php

namespace app\api\controller\user;

use think\facade\Request;
use think\exception\ValidateException;

use app\api\service\Comments as CommentsService;
use app\api\service\Cards as CardsService;

use app\api\validate\Comments as CommentsValidate;

//旧的
use app\common\Export;

use app\api\controller\Base;

class Comments extends Base
{
    //列表
    public function index()
    {
        //调用服务
        $result = CommentsService::list($this->JWT_SESSION);
        //返回结果
        return Export::Create($result, 200, null);
    }

    //删除
    public function delete()
    {
        try {
            CommentsService::updata($this->JWT_SESSION, ['status' => 1], ['id' => Request::param('id')]);
        } catch (\Throwable $th) {
            return Export::Create([], $th->getCode(), $th->getMessage());
        }
        return Export::Create([]);
    }
}
