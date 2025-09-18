<?php

namespace app\common;

use think\Facade;
use think\facade\Cookie;

use jwt\Jwt;

use app\api\service\Users as UsersService;

class FrontEnd extends Facade
{

    /**
     * @description: 前端跳转
     * @return {*}
     * @Author: github.com/zhiguai
     * @Date: 2022-12-29 18:55:49
     * @LastEditTime: Do not edit
     * @LastEditors: github.com/zhiguai
     * @param {*} $url
     * @param {*} $msg
     */
    public static function mObjectEasyFrontEndJumpUrl($url, $msg = 'undefined')
    {
        // 写人Msg信息
        Cookie::forever('msg', $msg);
        // 跳转至网页（记得前端显示完就删除）
        return redirect($url);
    }

    /**
     * @description: 前端依Coockie验证token并获取当前用户数据
     * @return {*}
     * @Author: github.com/zhiguai
     * @Date: 2022-12-29 18:56:45
     * @LastEditTime: Do not edit
     * @LastEditors: github.com/zhiguai
     */
    public static function mResultGetNowAdminAllData()
    {
        //$TDef_JwtData = request()->JwtData;
        //整理数据
        $token = Cookie::get('TOKEN');
        if (empty($token)) {
            return Common::mArrayEasyReturnStruct('请先登入', false);
        }

        //Jwt校验
        $lDef_JwtCheckTokenResult = jwt::CheckToken($token);
        if (!$lDef_JwtCheckTokenResult['status']) {
            return Common::mArrayEasyReturnStruct($lDef_JwtCheckTokenResult['msg'], false);
        }

        //取用户数据
        $lDef_GetNowAdminAllDataResult = BackEnd::mArrayGetNowAdminAllData($lDef_JwtCheckTokenResult['data']['aid']);
        //判断数据是否存在
        if ($lDef_GetNowAdminAllDataResult['status']) {
            //返回用户数据
            return Common::mArrayEasyReturnStruct(null, true, $lDef_GetNowAdminAllDataResult['data']);
        } else {
            return Common::mArrayEasyReturnStruct($lDef_GetNowAdminAllDataResult['msg'], false);
        }
    }

    /**
     * @description: 前端依Coockie验证token并获取当前用户数据
     * @return array[status,msg,data=>object|null]
     */
    public static function mResultGetNowUserAllData()
    {
        //$TDef_JwtData = request()->JwtData;
        //整理数据
        $token = Cookie::get('UTOKEN');
        if (empty($token)) {
            return Common::mArrayEasyReturnStruct('请先登入', false);
        }

        //Jwt校验
        $lDef_JwtCheckTokenResult = jwt::CheckToken($token);
        if (!$lDef_JwtCheckTokenResult['status']) {
            return Common::mArrayEasyReturnStruct($lDef_JwtCheckTokenResult['msg'], false);
        }

        //取用户数据
        $lDef_GetNowUserAllDataResult = UsersService::Get($lDef_JwtCheckTokenResult['data']['uid']);
        //判断数据是否存在
        if ($lDef_GetNowUserAllDataResult['status']) {
            //返回用户数据
            return Common::mArrayEasyReturnStruct(null, true, $lDef_GetNowUserAllDataResult['data']);
        } else {
            return Common::mArrayEasyReturnStruct($lDef_GetNowUserAllDataResult['msg'], false);
        }
    }
}
