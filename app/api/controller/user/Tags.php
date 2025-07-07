<?php

namespace app\api\controller\user;

use app\api\service\Tags as TagsService;

use app\common\Export;

use \app\api\controller\Base;

class Tags extends Base
{

    //获取-GET
    public function noPaginateIndex()
    {
        $params = [
            'search_value' => 0,
            'search_keys' => ['status'],
        ];

        //调用服务
        $lDef_Result = TagsService::noPaginateIndex($params);
        //返回结果
        return Export::Create($lDef_Result['data'], 200, null);
    }
}
