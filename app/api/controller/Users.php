<?php

namespace app\api\controller;

use think\Request as TypeRequest;
use think\facade\Request;
use think\exception\ValidateException;
use think\facade\Db;

use app\api\model\Users as UsersModel;
use app\api\validate\Users as UsersValidate;

use app\common\Export;

class Users
{
    //查询-GET
    public function Index()
    {
        if (Request::param('page') == null) {
            $lDef_Paginte['page'] = 0;
        } else {
            $lDef_Paginte['page'] = Request::param('page');
        }

        if (Request::param('list_rows') == null) {
            $lDef_Paginte['list_rows'] = 12;
        } else {
            $lDef_Paginte['list_rows'] = Request::param('list_rows') > 100 ? 100 : Request::param('list_rows');
        }

        $lDef_Result = UsersModel::Index($lDef_Paginte);

        return Export::Create($lDef_Result['data'], 200, null);
    }

    //添加-POST
    // public function Add()
    // {
    //     $context = request()->JwtData;

    //     $userName = Request::param('userName');
    //     $password = Request::param('password');
    //     $power = Request::param('power');

    //     //验证参数是否合法
    //     try {
    //         validate(UserValidate::class)->batch(true)
    //             ->scene('add')
    //             ->check([
    //                 'userName'  => $userName,
    //                 'password'   => $password,
    //                 'power' => $power
    //             ]);
    //     } catch (ValidateException $e) {
    //         // 验证失败 输出错误信息
    //         $validateerror = $e->getError();
    //         return Export::Create($validateerror, 400, '添加失败');
    //     }

    //     //获取数据库对象
    //     $result = Db::table('admin');
    //     //整理数据
    //     $data = ['userName' => $userName, 'password' => sha1($password), 'power' => $power];
    //     //写入库
    //     $result->save($data);
    //     //返回数据
    //     return Export::Create(null, 200, null);
    // }

    //编辑-PUT
    public function Patch()
    {
        //传入必要参数
        $lDef_ParamData = [
            'id' => Request::param('id'),
            'number' => Request::param('number'),
            'avatar' => Request::param('avatar'),
            'email' => Request::param('email'),
            'phone' => Request::param('phone'),
            'username' => Request::param('username'),
            'password' => Request::param('password'),
            'status' => Request::param('status'),
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

        $tDef_Result = UsersModel::Del($id);
        if ($tDef_Result['status']) {
            return Export::Create(null, 200, null);
        }
        return Export::Create(null, 500, $tDef_Result['msg']);
    }
}
