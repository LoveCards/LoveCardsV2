<?php

namespace app\common;

use think\Facade;
use think\facade\Session;
use think\facade\Request;
use think\facade\Db;
use app\common\Common;

class BackEnd extends Facade
{
    /**
     * 通过ID查询管理员全部数据
     *
     * @param int $id
     * @return array
     */
    public static function mArrayGetNowAdminAllData($id)
    {
        //查询数据
        $result = Db::table('admin')
            ->where('id', $id)
            ->find();
        //判断数据是否存在
        if (empty($result)) {
            return Common::mArrayEasyReturnStruct('管理员不存在', false);
        } else
            //返回用户数据
            return Common::mArrayEasyReturnStruct(null, true, $result);
    }

    /**
     * @description: 生成UUID
     * @return {*}
     * @Author: github.com/zhiguai
     * @Date: 2022-12-29 18:57:34
     * @LastEditTime: Do not edit
     * @LastEditors: github.com/zhiguai
     */
    public static function mStringGenerateUUID()
    {
        $charid = md5(uniqid(mt_rand(), true));
        $hyphen = chr(45); // "-"
        $uuid = //chr(123) "{"
            substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);
        //.chr(125); "}"
        return $uuid;
    }

    /**
     * 编辑配置文件
     *
     * @param string $filename
     * @param array $data
     * @param boolean $free
     * @param string $env
     * @return boolean
     */
    public static function mBoolCoverConfig($filename = '', $data = [], $free = false, $env = ''): bool
    {
        if (!$env) {
            $env = $filename;
        }
        $filename = '../config/' . $filename . '.php';
        $str_file = file_get_contents($filename);

        if ($free == true) {
            foreach ($data as $key => $value) {
                //构建正则匹配
                $pattern = "/env\('" . $env . "\." . $key . "',\s*([^']*)\)/";
                //判断是否成功匹配
                if (preg_match($pattern, $str_file)) {
                    //匹配成功更新
                    $str_file = preg_replace($pattern, "env('" . $env . "." . $key . "', " . $value . ")", $str_file);
                }
            }
        } else {
            foreach ($data as $key => $value) {
                //构建正则匹配
                $pattern = "/env\('" . $env . "\." . $key . "',\s*'([^']*)'\)/";
                //判断是否成功匹配
                if (preg_match($pattern, $str_file)) {
                    //匹配成功更新
                    $str_file = preg_replace($pattern, "env('" . $env . "." . $key . "', '" . $value . "')", $str_file);
                }
            }
        }

        //写入并返回结果
        if (!file_put_contents($filename, $str_file)) {
            return false;
        } else {
            return true;
        }
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
            //符合要求
            $result = [true];
        } else {
            $result = [false, '您的操作太快了，稍后再试试试吧'];
        }
        //设置上次时间
        Session::set($setName, date("Y-m-d H:i:s", strtotime('+' . $time . ' second')));
        //返回结果
        return $result;
    }
}
