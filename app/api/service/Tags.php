<?php

namespace app\api\service;

use app\common\Common;

use app\api\model\Tags as TagsModel;
use app\api\service\Exception;

use yunarch\app\api\service\IndexUtils;

class Tags
{

    /**
     * 读取全部标签列表
     *
     * @return void
     */
    static public function noPaginateIndex($params)
    {
        $index = new IndexUtils(TagsModel::class, $params);
        $result = $index->noPaginate('name', [], true);
        if ($result) {
            return Common::mArrayEasyReturnStruct(null, true, $result->toArray());
        }
        return Common::mArrayEasyReturnStruct('列表查询失败', false);
    }

    /**
     * 读取全部标签列表
     *
     * @return void
     */
    static public function Index($params)
    {
        $index = new IndexUtils(TagsModel::class, $params);
        $result = $index->common('name', [], true);
        if ($result) {
            return Common::mArrayEasyReturnStruct(null, true, $result->toArray());
        }
        return Common::mArrayEasyReturnStruct('列表查询失败', false);
    }
}
