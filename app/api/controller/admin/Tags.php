<?php

namespace app\api\controller\admin;

use think\facade\Request;
use think\exception\ValidateException;

use app\api\service\Tags as TagsService;
use app\api\validate\Tags as TagsValidate;

use app\common\Export;

use yunarch\app\api\controller\IndexUtils as ApiControllerIndexUtils;
use yunarch\app\api\validate\Index as ApiIndexValidate;
use yunarch\app\api\validate\Common as ApiCommonValidate;

use app\api\controller\Base;

class Tags extends Base
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

    public function Index()
    {
        // 获取参数并按照规则过滤
        $params = ApiCommonValidate::sceneFilter(Request::param(), ApiIndexValidate::$all_scene['Defult']);
        // search_keys转数组
        $params = ApiControllerIndexUtils::paramsJsonToArray('search_keys', $params);

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
        $lDef_Result = TagsService::Index($params);
        //返回结果
        return Export::Create($lDef_Result['data'], 200, null);
    }

    public function Post()
    {
        //获取参数
        $params = $this->getParams(TagsValidate::class, TagsValidate::$all_scene['admin']['post']);
        if (gettype($params) == 'object') {
            return $params;
        }

        $params['user_id'] = $this->JWT_SESSION['uid'];

        //调用服务
        $result = TagsService::createTag($params);
        //返回结果
        return Export::Create($result['data'], 200, null);
    }

    public function Patch()
    {
        //获取参数
        $params = $this->getParams(TagsValidate::class, TagsValidate::$all_scene['admin']['patch']);
        if (gettype($params) == 'object') {
            return $params;
        }

        //调用服务
        $result = TagsService::updateTag($params);
        //返回结果
        return Export::Create($result['data'], 200, null);
    }

    public function Delete()
    {
        //获取参数
        $params = $this->getParams(ApiCommonValidate::class, ApiCommonValidate::$all_scene['SingleOperate']);
        if (gettype($params) == 'object') {
            return $params;
        }

        //调用服务
        $result = TagsService::deleteTags($params);
        //返回数据
        return Export::Create(null, 200);
    }

    public function BatchOperate()
    {
        $params = $this->getParams(ApiCommonValidate::class, ApiCommonValidate::$all_scene['BatchOperate']);
        if (gettype($params) == 'object') {
            return $params;
        }
        $ids = json_decode($params['ids'], true);
        $result = TagsService::batchOperate($params['method'], $ids);

        //返回数据
        return Export::Create(null, 200);
    }
}
