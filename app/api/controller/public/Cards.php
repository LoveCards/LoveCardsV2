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

    public function index()
    {
        // 获取参数并按照规则过滤
        $params = ApiCommonValidate::sceneFilter(Request::param(), ApiIndexValidate::$all_scene['Defult']);
        // search_keys转数组
        $params = ApiControllerIndexUtils::paramsJsonToArray('search_keys', $params['pass']);

        //验证参数
        try {
            validate(ApiIndexValidate::class)
                ->batch(true)
                ->check($params);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $error = $e->getError();
            return Export::Create($error, 400, '参数错误');
        }

        //调用服务
        $result = $this->CardsService->newList($params, 1);
        //返回结果
        return Export::Create($result['data'], 200, null);
    }

    public function hotList()
    {
        //调用服务
        $result = $this->CardsService->hotList();
        //返回结果
        return Export::Create($result['data'], 200, null);
    }
}
