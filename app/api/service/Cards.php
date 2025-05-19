<?php

namespace app\api\service;

use app\common\Common;

use app\api\model\Cards as CardsModel;
use app\api\service\Exception;

use yunarch\app\api\service\IndexUtils;

class Cards
{

    /**
     * 读取用户卡片
     *
     * @return void
     */
    static public function Index($params)
    {
        $index = new IndexUtils(CardsModel::class, $params);
        $result = $index->common('content', [], true);
        if ($result) {
            return Common::mArrayEasyReturnStruct(null, true, $result->toArray());
        }
        return Common::mArrayEasyReturnStruct('查询失败', false);
    }

    /**
     * 读取卡片
     *
     * @return void
     */
    static public function Get($id)
    {
        $result = CardsModel::where('id', $id)->find();
        if ($result) {
            return Common::mArrayEasyReturnStruct(null, true, $result->toArray());
        }
        return Common::mArrayEasyReturnStruct('查询失败', false);
    }

    //列表
    static public function list()
    {
        $context = request()->JwtData;

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

        $where = ['uid' => $context['uid']] + $where;
        $result = CardsModel::update($data, $where, $allowField);

        return $result;
    }
}
