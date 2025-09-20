<?php

namespace app\api\controller\admin;

use think\facade\Request;

use app\api\service\Images as ImagesService;

use app\common\Common;

use app\api\controller\ApiResponse;

class Images extends Common
{
    public function CardIndex()
    {
        $params = ['pid' => Request::param('card_id')];

        if ($params['pid'] == null) {
            return ApiResponse::createBadRequest('参数错误(待完善接口)');
        }

        //调用服务
        $lDef_Result = ImagesService::CardIndex($params);
        //返回结果
        return ApiResponse::createSuccess($lDef_Result['data']);
    }
}
