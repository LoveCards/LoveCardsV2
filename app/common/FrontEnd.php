<?php

namespace app\common;

use think\Facade;
use think\facade\Db;
use think\facade\Cookie;

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
        return "<script>window.location.replace('" . $url . "')</script>";
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
}
