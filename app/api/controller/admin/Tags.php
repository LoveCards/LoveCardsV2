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
    //基础分页数据
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
        $lDef_Result = TagsService::Index($params);
        //返回结果
        return Export::Create($lDef_Result['data'], 200, null);
    }

    //插入
    public function Create()
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

    //编辑
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

    //删除
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

    //批量操作
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
