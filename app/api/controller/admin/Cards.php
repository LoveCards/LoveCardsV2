<?php

namespace app\api\controller\admin;

use think\facade\Request;
use think\exception\ValidateException;

//基础应用验证
use app\api\validate\Cards as CardsValidate;
use app\api\validate\CardsSetting as CardsValidateSetting;

//基础应用服务
use app\api\service\Cards as CardsService;

//旧的
use app\common\Export;
use app\common\BackEnd;

//yunarch框架相关
use yunarch\app\api\controller\IndexUtils as ApiControllerIndexUtils;
use yunarch\app\api\validate\Index as ApiIndexValidate;
use yunarch\app\api\validate\Common as ApiCommonValidate;

use app\api\controller\Base;

class Cards extends Base
{
    /**
     * 快速验证并过滤数据
     *
     * @param string 对应的验证类
     * @param array 对应的验证场景
     * @return array|object
     */
    protected function getParams($ValidateClass, $scene)
    {
        // 获取参数并按照规则过滤
        $result = ApiCommonValidate::sceneFilter(Request::param(), $scene);

        //验证参数
        try {
            //场景参数验证
            $params = ApiCommonValidate::sceneMessage($result);
            //参数验证
            validate($ValidateClass)
                ->batch(true)
                ->check($params);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $error = $e->getError();
            return Export::Create($error, 400, '参数错误');
        }

        return $params;
    }

    //读取列表
    public function Index()
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
        $result = CardsService::Index($params);
        //返回结果
        return Export::Create($result['data'], 200, null);
    }

    //读取一张卡片
    public function Get()
    {
        //获取参数
        $params = $this->getParams(ApiCommonValidate::class, ApiCommonValidate::$all_scene['SingleOperate']);
        if (gettype($params) == 'object') {
            return $params;
        }

        //调用服务
        $result = CardsService::Get($params['id']);
        //返回结果
        return Export::Create($result['data'], 200, null);
    }

    //编辑一张卡片
    public function Patch()
    {
        //获取参数
        $params = $this->getParams(CardsValidate::class, CardsValidate::$all_scene['admin']['patch']);
        if (gettype($params) == 'object') {
            return $params;
        }

        //调用服务
        $result = CardsService::updateCard($params);
        //返回结果
        return Export::Create($result['data'], 200, null);
    }

    //删除一张卡片
    public function Delete()
    {
        //获取参数
        $params = $this->getParams(ApiCommonValidate::class, ApiCommonValidate::$all_scene['SingleOperate']);
        if (gettype($params) == 'object') {
            return $params;
        }

        //调用服务
        $result = CardsService::deleteCards($params);
        //返回数据
        return Export::Create(null, 200);
    }

    //批量操作
    public function BatchOperate()
    {

        $params = $this->getParams(ApiCommonValidate::class, ApiCommonValidate::$all_scene['BatchOperate']);
        if (gettype($params) == 'object') {
            return $params;
        }
        $ids = json_decode($params['ids'], true);
        $result = CardsService::batchOperate($params['method'], $ids);

        //返回数据
        return Export::Create(null, 200);
    }

    //设置
    public function Setting()
    {

        $data = [
            'DefSetCardsImgNum' => Request::param('DefSetCardsImgNum'),
            'DefSetCardsTagNum' => Request::param('DefSetCardsTagNum'),
            'DefSetCardsStatus' => Request::param('DefSetCardsStatus'),
            'DefSetCardsImgSize' => Request::param('DefSetCardsImgSize'),
            'DefSetCardsCommentsStatus' => Request::param('DefSetCardsCommentsStatus')
        ];

        // 数据校验
        try {
            validate(CardsValidateSetting::class)
                ->batch(true)
                ->check($data);
        } catch (ValidateException $e) {
            $validateerror = $e->getError();
            return Export::Create($validateerror, 400, '修改失败');
        }

        $result = BackEnd::mBoolCoverConfig('lovecards', $data, true);

        if ($result == true) {
            return Export::Create(null, 200);
        } else {
            return Export::Create(null, 400, '修改失败，请重试');
        }
    }
}
