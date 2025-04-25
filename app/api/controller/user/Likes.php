<?php

namespace app\api\controller\user;

use think\facade\Request;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Config;

use app\api\model\Images as ImagesModel;

use app\api\service\Likes as LikesService;

use app\common\Common;
use app\common\Export;
use app\common\BackEnd;
use app\common\App;

class Likes extends Common
{

    //列表
    public function List()
    {
        $context = request()->JwtData;
        if ($context['uid'] == 0) {
            return Export::Create([], 401, '请先登入');
        }

        try {
            $result = LikesService::list($context);
        } catch (\Throwable $th) {
            return Export::Create([], $th->getCode(), $th->getMessage());
        }
        return Export::Create($result);
    }

    //删除
    public function Delete()
    {
        $context = request()->JwtData;
        if ($context['uid'] == 0) {
            return Export::Create([], 401, '请先登入');
        }

        try {
            LikesService::delete(Request::param('id'), $context);
        } catch (\Throwable $th) {
            return Export::Create([], $th->getCode(), $th->getMessage());
        }
        return Export::Create([]);
    }
}
