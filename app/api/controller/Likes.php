<?php
/*
 * @Description: 
 * @Author: github.com/zhiguai
 * @Date: 2024-09-18 16:38:22
 * @Email: 2903074366@qq.com
 */

namespace app\api\controller;

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
        try {
            $result = LikesService::list();
        } catch (\Throwable $th) {
            return Export::Create([], $th->getCode(), $th->getMessage());
        }
        return Export::Create($result);
    }

    //删除
    public function Delete()
    {
        try {
            LikesService::delete(Request::param('id'));
        } catch (\Throwable $th) {
            return Export::Create([], $th->getCode(), $th->getMessage());
        }
        return Export::Create([]);
    }
}
