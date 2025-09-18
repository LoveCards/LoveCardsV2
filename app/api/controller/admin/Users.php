<?php

namespace app\api\controller\admin;

use think\facade\Request;
use think\exception\ValidateException;

use app\api\service\Users as UsersService;
use app\api\validate\Users as UsersValidate;

use yunarch\app\api\utils\Common as UtilsCommon;
use yunarch\app\api\controller\Utils as ApiControllerUtils;
use yunarch\utils\src\ValidateExtend as ApiControllerIndexUtils;
use yunarch\app\validate\ModelList as ApiIndexValidate;
use yunarch\app\validate\Common as ApiCommonValidate;

use app\common\Export;

use \app\api\controller\Base;


class Users extends Base
{
    //基础分页数据
    public function Index(UsersService $UsersService)
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
        $lDef_Result = $UsersService->Index($params);
        //返回结果
        return Export::Create($lDef_Result['data'], 200, null);
    }

    //编辑
    public function Patch()
    {
        //获取参数
        $params = $this->getParams(UsersValidate::class, UsersValidate::$all_scene['admin']['patch']);
        if (gettype($params) == 'object') {
            return $params;
        }

        //如果密码存在则进行密码加密
        if (array_key_exists('password', $params)) {
            $params['password'] = password_hash($params['password'], PASSWORD_DEFAULT);
        }
        //调用服务
        $tDef_Result = UsersService::Patch($params['id'], $params);
        if ($tDef_Result['status']) {
            return Export::Create(null, 200, null);
        }

        //错误返回
        $lDef_ErrorMsg = $tDef_Result['data']->getMessage();
        return Export::Create(null, 500, $lDef_ErrorMsg);
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
        $result = UsersService::deleteUsers($params);

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
        $result = UsersService::batchOperate($params['method'], $ids);

        //返回数据
        return Export::Create(null, 200);
    }
}
