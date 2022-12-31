<?php
//公共类
namespace app\common;

//TP门面类
use think\Facade;
//TPResponse类
use think\Response;

//TPCache类
//use think\facade\Cache;
//TP请求类
use think\facade\Request;
//TPDb类
use think\facade\Db;
//TPCoockie类
use think\facade\Cookie;

class Common extends Facade
{

    /**
     * @description: 版本信息
     * @return {*}
     * @Author: github.com/zhiguai
     * @Date: 2022-12-29 18:48:48
     * @LastEditTime: Do not edit
     * @LastEditors: github.com/zhiguai
     */
    protected static function systemVer()
    {
        return [
            'Name' => 'LoveCards',
            'Url' => '//lovecards.cn',
            'VerS' => '2.0.0',
            'Ver' => '1.0'
        ];
    }

    //前端数据准备
    protected static function systemData()
    {
        return [];
    }

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
    protected static function jumpUrl($url, $msg = 'undefined')
    {
        // 写人Msg信息
        Cookie::forever('msg', $msg);
        // 跳转至网页（记得前端显示完就删除）
        return "<script>window.location.replace('" . $url . "')</script>";
    }

    /**
     * @description: 获取IP
     * @return {*}
     * @Author: github.com/zhiguai
     * @Date: 2022-12-29 18:56:28
     * @LastEditTime: Do not edit
     * @LastEditors: github.com/zhiguai
     * @param {*} $type
     */
    protected static function getIp($type = 0)
    {
        $type       =  $type ? 1 : 0;
        static $ip  =   NULL;
        if ($ip !== NULL) return $ip[$type];
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown', $arr);
            if (false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = ip2long($ip);
        $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }

    /**
     * @description: 前端Coockie验证uuid
     * @return {*}
     * @Author: github.com/zhiguai
     * @Date: 2022-12-29 18:56:45
     * @LastEditTime: Do not edit
     * @LastEditors: github.com/zhiguai
     */
    protected static function validateViewAuth()
    {
        //整理数据
        $uuid = Cookie::get('uuid');
        if (empty($uuid)) {
            return array(false, '请先登入');
        }
        //查询数据
        $result = Db::table('user')
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
     * @description: API验证uuid并获取当前用户数据
     * @return {*}
     * @Author: github.com/zhiguai
     * @Date: 2022-12-29 18:57:00
     * @LastEditTime: Do not edit
     * @LastEditors: github.com/zhiguai
     */
    protected static function validateAuth()
    {
        //整理数据
        $uuid = Request::param('uuid');
        if (empty($uuid)) {
            return array(400, '缺少uuid');
        }
        //查询数据
        $result = Db::table('user')
            ->where('uuid', $uuid)
            ->find();
        //判断数据是否存在
        if (empty($result)) {
            return array(401, '当前uuid已失效请重新登入');
        } else {
            //返回用户数据
            return $result;
        }
    }

    /**
     * @description: API格式输出方法
     * @return {*}
     * @Author: github.com/zhiguai
     * @Date: 2022-12-29 18:57:16
     * @LastEditTime: Do not edit
     * @LastEditors: github.com/zhiguai
     * @param {*} $data
     * @param {string} $msg
     * @param {int} $code
     * @param {string} $type
     */
    protected static function create($data, string $msg = '', int $code = 200, string $type = 'json'): Response
    {
        $result = [
            //状态码
            'ec' => $code,
            //消息
            'msg' => $msg,
            //数据
            'data' => $data
        ];

        //返回API接口
        return Response::create($result, $type);
    }

    /**
     * @description: 生成UUID
     * @return {*}
     * @Author: github.com/zhiguai
     * @Date: 2022-12-29 18:57:34
     * @LastEditTime: Do not edit
     * @LastEditors: github.com/zhiguai
     */
    protected static function get_uuid()
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
}
