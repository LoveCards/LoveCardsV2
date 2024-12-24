<?php

namespace app\api\service;

use think\facade\View;
use think\facade\Db;
use think\facade\Request;
use think\facade\Config;

use app\api\model\Images as ImagesModel;
use app\common\BackEnd;
use app\common\FrontEnd;
use app\common\Common;
use app\index\BaseController;

use app\api\model\Comments as CommentsModel;
use app\api\service\Exception;
use Error;
use Exception as GlobalException;

class Comments
{

    //列表
    static public function list($context)
    {
        //$currentPage = 1;
        $pageSize = 15;

        $result = CommentsModel::where('status', 0)
            ->where('uid', $context['uid'])
            ->order('id', 'desc')
            ->paginate($pageSize);

        return $result;
    }

    //更新指定ID的指定字段
    static public function updata($context, $data, $where = [], $allowField = [])
    {
        $where = ['uid' => $context['uid']] + $where;
        $result = CommentsModel::update($data, $where, $allowField);

        return $result;
    }
}
