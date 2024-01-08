<?php

namespace app\api\controller\user;

use think\Request as TypeRequest;
use think\facade\Request;
use think\exception\ValidateException;
use think\facade\Db;

use app\api\model\Users as UsersModel;
use app\api\validate\Users as UsersValidate;

use app\common\Export;

class Info
{
    //获取资料-GET
    public function Get()
    {
        $context = request()->JwtData;

        $tDef_Result = UsersModel::Get($context['uid'], ['id']);

        if ($tDef_Result['status']) {
            return Export::Create($tDef_Result['data'], 200, null, $context);
        }

        return Export::Create(null, 500, $tDef_Result['msg'], $context);
    }

    //编辑资料-Patch
    public function Patch()
    {
        $context = request()->JwtData;

        //传入必要参数
        $lDef_ParamData = [
            'id' => $context['uid'],
            'avatar' => Request::param('avatar'),
            'username' => Request::param('username'),
            'password' => Request::param('password'),
        ];

        //验证修改参数是否合法
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
        if ($lDef_ParamData['password']) {
            $lDef_ParamData['password'] = password_hash($lDef_ParamData['password'], PASSWORD_DEFAULT);
        }

        $tDef_Result = UsersModel::Patch($lDef_ParamData['id'], array_diff($lDef_ParamData, [null, '']));
        if ($tDef_Result['status']) {
            return Export::Create(null, 200, null, $context);
        }

        //错误返回
        $lDef_ErrorMsg = $tDef_Result['data']->getMessage();
        return Export::Create(null, 500, $lDef_ErrorMsg, $context);
    }

    //修改密码-Post
    public function PostPassword()
    {
        $context = request()->JwtData;

        //传入必要参数
        $lDef_ParamData = [
            'id' => $context['uid'],
            'password' => Request::param('password'),
        ];

        if (!$lDef_ParamData['password']) {
            return Export::Create(['密码不可为空'], 400, '编辑失败');
        }

        //验证修改参数是否合法
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

        $lDef_ParamData['password'] = password_hash($lDef_ParamData['password'], PASSWORD_DEFAULT);

        $tDef_Result = UsersModel::Patch($lDef_ParamData['id'], array_diff($lDef_ParamData, [null, '']));
        if ($tDef_Result['status']) {
            return Export::Create(null, 200, null, $context);
        }

        //错误返回
        $lDef_ErrorMsg = $tDef_Result['data']->getMessage();
        return Export::Create(null, 500, $lDef_ErrorMsg, $context);
    }
}
