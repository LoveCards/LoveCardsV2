<?php

namespace app\api\controller\user;

use think\facade\Request;
use think\facade\Db;
use think\exception\ValidateException;

use app\api\model\Tags as TagsModel;
use app\api\service\Tags as TagsService;
use app\api\model\TagsMap as TagsMapModel;
use app\api\validate\Tags as TagsValidate;

use app\common\Common;
use app\common\Export;

use yunarch\app\api\controller\Utils as ApiControllerUtils;
use yunarch\app\api\controller\IndexUtils as ApiControllerIndexUtils;
use yunarch\app\api\validate\Index as ApiIndexValidate;

class Tags extends Common
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
