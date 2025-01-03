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

use app\api\model\Cards as CardsModel;
use app\api\service\Exception;
use Error;
use Exception as GlobalException;

class Cards
{

    //列表
    static public function list()
    {
        $context = request()->JwtData;
        if ($context['uid'] == 0) {
            throw new \Exception('请先登入', 401);
        }

        //$currentPage = 1;
        $pageSize = 15;

        $result = CardsModel::where('status', 0)
            ->where('uid', $context['uid'])
            ->order('id', 'desc')
            ->paginate($pageSize);

        return $result;
    }

    //更新指定ID的指定字段
    static public function updata($data, $where = [], $allowField = [])
    {
        $context = request()->JwtData;
        if ($context['uid'] == 0) {
            throw new \Exception('请先登入', 401);
        }

        $where = ['uid' => $context['uid']] + $where;
        $result = CardsModel::update($data, $where, $allowField);

        return $result;
    }
}
