<?php

namespace app\common;

use think\Facade;
use think\facade\Db;
use think\facade\Cookie;
use think\facade\View;

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
    protected static function mObjectEasyFrontEndJumpUrl($url, $msg = 'undefined')
    {
        // 写人Msg信息
        Cookie::forever('msg', $msg);
        // 跳转至网页（记得前端显示完就删除）
        return redirect($url);
    }

    /**
     * @description: 前端依Coockie验证uuid并获取当前用户数据
     * @return {*}
     * @Author: github.com/zhiguai
     * @Date: 2022-12-29 18:56:45
     * @LastEditTime: Do not edit
     * @LastEditors: github.com/zhiguai
     */
    protected static function mResultGetNowAdminAllData()
    {
        //整理数据
        $uuid = Cookie::get('uuid');
        if (empty($uuid)) {
            return array(false, '请先登入');
        }
        //查询数据
        $result = Db::table('admin')
            ->where('uuid', $uuid)
            ->find();
        //判断数据是否存在
        if (empty($result)) {
            return array(false, '当前uuid已失效请重新登入');
        } else {
            //返回用户数据
            return array(true, $result);
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
    protected static function mObjectEasyAssignCommonNowList($lDef_CommonNowList, $tDef_CommonNowListEasyPagingComponent, $tDef_CommonNowListMax)
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
    protected static function mObjectEasyAssignCards($lDef_CardsList, $tDef_CardsListEasyPagingComponent, $tDef_CardsListMax)
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
    protected static function mObjectEasyGetAndAssignCardsTags($lDef_AdminMethod = false)
    {
        //获取并赋值CardsTag相关变量
        if ($lDef_AdminMethod) {
            $lDef_Result = Db::table('cards_tag')->select()->toArray();
        } else {
            $lDef_Result = Db::table('cards_tag')->where('status', 0)->select()->toArray();
        }

        View::assign([
            'CardsTagsListJson' => json_encode($lDef_Result),
            'CardsTagsList' => $lDef_Result
        ]);
    }
}
