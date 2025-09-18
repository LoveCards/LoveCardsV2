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
use yunarch\validate\Common as CommonValidate;

use app\api\controller\BaseController;
use app\api\controller\Params;

class Cards extends BaseController
{
    var $Params;

    public function __construct()
    {
        parent::__construct();
        $this->Params = new Params();
    }

    //基础分页数据
    public function Index(CardsService $CardsService)
    {
        //获取过滤参数
        $params = $this->Params->IndexParams();
        //调用服务
        $result = $CardsService->newList($params);
        //返回结果
        return Export::Create($result['data'], 200, null);
    }

    //获取
    public function Get()
    {
        //获取参数
        $params = $this->Params->getParams(CommonValidate::class, CommonValidate::$all_scene['SingleOperate']);
        if (gettype($params) == 'object') {
            return $params;
        }

        //调用服务
        $result = CardsService::Get($params['id']);
        //返回结果
        return Export::Create($result['data'], 200, null);
    }

    //编辑
    public function Patch()
    {
        //获取参数
        $params = $this->Params->getParams(CardsValidate::class, CardsValidate::$all_scene['admin']['patch']);
        if (gettype($params) == 'object') {
            return $params;
        }

        //调用服务
        $result = CardsService::updateCard($params);
        //返回结果
        return Export::Create($result['data'], 200, null);
    }

    //删除
    public function Delete()
    {
        //获取参数
        $params = $this->Params->getParams(CommonValidate::class, CommonValidate::$all_scene['SingleOperate']);
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

        $params = $this->Params->getParams(CommonValidate::class, CommonValidate::$all_scene['BatchOperate']);
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
