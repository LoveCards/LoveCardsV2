<?php

namespace app\api\service;

use app\common\Common;

use app\api\model\Images as ImagesModel;
use app\api\service\Exception;

use yunarch\app\api\service\IndexUtils;

class Images
{

    /**
     * 读取用户卡片
     *
     * @return void
     */
    static public function CardIndex($params)
    {
        $result = ImagesModel::where('aid', 1)->where('pid', $params['pid'])->select();
        if ($result) {
            return Common::mArrayEasyReturnStruct(null, true, $result->toArray());
        }
        return Common::mArrayEasyReturnStruct('查询失败', false);
    }
}
