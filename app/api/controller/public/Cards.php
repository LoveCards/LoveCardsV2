<?php

namespace app\api\controller\public;

use app\api\service\Cards as CardsService;
use app\api\service\Likes as LikesService;
use app\api\service\Comments as CommentsService;

use app\common\Export;

use think\facade\Request;
use think\exception\ValidateException;

use yunarch\app\api\controller\IndexUtils as ApiControllerIndexUtils;
use yunarch\app\api\validate\Index as ApiIndexValidate;
use yunarch\app\api\validate\Common as ApiCommonValidate;

use app\api\controller\Base;

class Cards extends Base
{
    protected $CardsService;

    public function __construct(CardsService $CardsService)
    {
        $this->CardsService = $CardsService;
    }

    public function hotList()
    {
        //调用服务
        $result = $this->CardsService->hotList();
        //返回结果
        return Export::Create($result['data'], 200, null);
    }
}
