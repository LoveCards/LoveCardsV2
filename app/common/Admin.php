<?php

namespace app\common;

use think\facade\Db;
use think\facade\Session;
use app\common\Common;

class Admin
{
    /**
     * 通过ID查询管理员全部数据
     *
     * @param int $id
     * @return array
     */
    public static function mArrayGetNowAdminAllData($id)
    {
        $result = Db::table('admin')
            ->where('id', $id)
            ->find();
        if (empty($result)) {
            return Common::mArrayEasyReturnStruct('管理员不存在', false);
        } else
            return Common::mArrayEasyReturnStruct(null, true, $result);
    }

    /**
     * @description: 依Session实现的防抖
     * @return {*}
     * @Author: github.com/zhiguai
     * @Date: 2023-07-18 15:17:21
     * @LastEditTime: Do not edit
     * @LastEditors: github.com/zhiguai
     * @param {*} $setName
     * @param {*} $time
     */
    public static function mRemindEasyDebounce($setName, $time = 6)
    {
        if (strtotime(date("Y-m-d H:i:s")) > strtotime(Session::get($setName))) {
            $result = [true];
        } else {
            $result = [false, '您的操作太快了，稍后再试试试吧'];
        }
        Session::set($setName, date("Y-m-d H:i:s", strtotime('+' . $time . ' second')));
        return $result;
    }
}
