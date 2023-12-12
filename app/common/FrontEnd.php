<?php

namespace app\common;

use think\Facade;
use think\facade\Db;
use think\facade\Cookie;
use think\facade\View;

use app\common\CheckClass;

use jwt\Jwt;

use app\api\model\Users as UsersModel;

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
        $lDef_GetNowUserAllDataResult = UsersModel::Get($lDef_JwtCheckTokenResult['data']['uid']);
        //判断数据是否存在
        if ($lDef_GetNowUserAllDataResult['status']) {
            //返回用户数据
            return Common::mArrayEasyReturnStruct(null, true, $lDef_GetNowUserAllDataResult['data']);
        } else {
            return Common::mArrayEasyReturnStruct($lDef_GetNowUserAllDataResult['msg'], false);
        }
    }

    /**
     * @description: 通用简单分页参数输出
     * @return {*}
     * @Author: github.com/zhiguai
     * @Date: 2023-09-06 16:10:06
     * @LastEditTime: Do not edit
     * @LastEditors: github.com/zhiguai
     * @param {*} $lDef_CommonNowList
     * @param {*} $tDef_CommonNowListEasyPagingComponent
     * @param {*} $tDef_CommonNowListMax
     */
    public static function mObjectEasyAssignCommonNowList($lDef_CommonNowList, $tDef_CommonNowListEasyPagingComponent, $tDef_CommonNowListMax)
    {
        View::assign([
            'CommonNowList'  => $lDef_CommonNowList,
            'CommonNowListEasyPagingComponent'  => $tDef_CommonNowListEasyPagingComponent,
            'CommonNowListMax'  => $tDef_CommonNowListMax
        ]);
    }

    /**
     * @description: 卡片简单分页参数输出
     * @return {*}
     * @Author: github.com/zhiguai
     * @Date: 2023-09-06 16:10:24
     * @LastEditTime: Do not edit
     * @LastEditors: github.com/zhiguai
     * @param {*} $lDef_CardsList
     * @param {*} $tDef_CardsListEasyPagingComponent
     * @param {*} $tDef_CardsListMax
     */
    public static function mObjectEasyAssignCards($lDef_CardsList, $tDef_CardsListEasyPagingComponent, $tDef_CardsListMax)
    {
        //赋值Cards相关变量;
        View::assign([
            'CardsList'  => $lDef_CardsList,
            'CardsListEasyPagingComponent'  => $tDef_CardsListEasyPagingComponent,
            'CardsListMax'  => $tDef_CardsListMax
        ]);
    }

    /**
     * @description: 标签简单分页参数输出
     * @return {*}
     * @Author: github.com/zhiguai
     * @Date: 2023-09-06 16:10:30
     * @LastEditTime: Do not edit
     * @LastEditors: github.com/zhiguai
     * @param {*} $lDef_AdminMethod
     */
    public static function mObjectEasyGetAndAssignCardsTags($lDef_AdminMethod = false)
    {
        //获取并赋值CardsTag相关变量
        if ($lDef_AdminMethod) {
            $lDef_Result = Db::table('tags')->select()->toArray();
        } else {
            $lDef_Result = Db::table('tags')->where('status', 0)->select()->toArray();
        }

        View::assign([
            'TagsListJson' => json_encode($lDef_Result),
            'TagsList' => $lDef_Result
        ]);
    }
}
