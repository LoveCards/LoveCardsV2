<?php

namespace app\api\controller;

use think\Request as TypeRequest;
use think\facade\Request;
use think\exception\ValidateException;
use think\facade\Db;

use app\api\service\Users as UsersService;
use app\api\validate\Users as UsersValidate;

use yunarch\app\api\utils\Common as UtilsCommon;
use yunarch\app\api\facade\ControllerUtils as ApiControllerUtils;
use yunarch\app\api\validate\Index as ApiIndexValidate;
use yunarch\app\api\utils\Json as ApiJsonUtils;

use app\common\Export;

class Users
{

    //查询-GET
    public function Index()
    {
        // 获取参数并按照规则过滤
        $params = ApiControllerUtils::filterParams(Request::param(), ApiIndexValidate::$all_scene['index']);
        // 特殊处理
        if (array_key_exists("search_keys", $params)) {
            $decoded = json_decode($params['search_keys'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $params['search_keys'] = $decoded;
            } else {
                unset($params['search_keys']);
            }
        }

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
        $lDef_Result = UsersService::Index($params);
        //返回结果
        return Export::Create($lDef_Result['data'], 200, null);
    }

    //创建-POST
    public function Create()
    {
        //接收参数
        //验证参数
        //调用服务
        //返回结果
    }

    //编辑-PUT
    public function Patch()
    {
        //获取参数并按照规则过滤
        $MustParams = ['number'];
        $lDef_ParamData = ApiControllerUtils::filterParams(Request::param(), UsersValidate::$all_scene['edit'], $MustParams);

        //验证参数
        try {
            validate(UsersValidate::class)
                ->batch(true)
                ->scene('edit')
                ->check($lDef_ParamData);
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $uservalidateerror = $e->getError();
            return Export::Create($uservalidateerror, 400, '编辑失败');
        }

        //如果密码存在则进行密码加密
        if (array_key_exists('password', $lDef_ParamData)) {
            $lDef_ParamData['password'] = password_hash($lDef_ParamData['password'], PASSWORD_DEFAULT);
        }
        //调用服务
        $tDef_Result = UsersService::Patch($lDef_ParamData['id'], $lDef_ParamData);
        if ($tDef_Result['status']) {
            return Export::Create(null, 200, null);
        }

        //错误返回
        $lDef_ErrorMsg = $tDef_Result['data']->getMessage();
        return Export::Create(null, 500, $lDef_ErrorMsg);
    }

    //删除-DELETE
    public function Delete()
    {

        //传入必要参数
        $id = Request::param('id');

        //验证ID是否正常传入
        if (empty($id)) {
            return Export::Create(null, 400, '缺少id参数');
        }

        $id = UtilsCommon::BatchOrSingle($id);

        $tDef_Result = UsersService::Delete($id);
        if ($tDef_Result['status']) {
            return Export::Create(null, 200, null);
        }
        return Export::Create(null, 500, $tDef_Result['msg']);
    }
}
