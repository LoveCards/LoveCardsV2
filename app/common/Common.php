<?php

namespace app\common;

use think\Facade;
use think\facade\Db;


class Common extends Facade
{

    //基础参数
    var $attrGReqTime;
    var $attrGReqIp;
    public function __construct()
    {
        $this->attrGReqTime = date('Y-m-d H:i:s');
        $this->attrGReqIp = $this->mStringGetIP();
    }

    /**
     * @description: 版本信息
     * @return {*}
     * @Author: github.com/zhiguai
     * @Date: 2022-12-29 18:48:48
     * @LastEditTime: Do not edit
     * @LastEditors: github.com/zhiguai
     */
    public static function mArrayGetLCVersionInfo()
    {
        return [
            'Name' => 'LoveCards',
            'Url' => '//lovecards.cn',
            'VerS' => '2.2.0',
            'Ver' => '1.0.15',
            'GithubUrl' => '//github.com/LoveCards/LoveCardsV2',
            'QGroupUrl' => '//jq.qq.com/?_wv=1027&k=qM8f2RMg',
            'InstallEnvironment' => [
                'php' => [
                    'f' => '7.2.5',
                    'l' => '8.0.99'
                ],
                'mysql' => [
                    'f' => '5.7',
                    'l' => '9999'
                ],
            ]
        ];
    }

    /**
     * @description: 取数据库system数据
     * @return {*}
     * @Author: github.com/zhiguai
     * @Date: 2023-01-18 18:10:57
     * @LastEditTime: Do not edit
     * @LastEditors: github.com/zhiguai
     */
    public static function mArrayGetDbSystemData()
    {
        $result = Db::table('system')->select()->toArray();
        return array_column($result, 'value', 'name');
    }


    /**
     * 获取IP
     *
     * @param integer $type
     * @return string
     */
    public static function mStringGetIP($type = 0): string
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
     * 格式化返回
     *
     * @param string|null $msg
     * @param boolean $status
     * @param object $data
     * @return array ['status','msg','data':*]
     */
    public static function mArrayEasyReturnStruct(string $msg = null, bool $status = true, $data = null): array
    {
        return [
            'status' => $status,
            'msg' => $msg,
            'data' => $data,
        ];
    }


    /**
     * 验证字符串是邮箱还是手机号
     *
     * @param string $input
     * @return boolean|string
     */
    public static function  mBoolEasyIsPhoneNumberOrEmail($input): string
    {
        // 去除字符串首尾的空格
        $input = trim($input);

        // 使用正则表达式检查是否是手机号
        $phoneNumberPattern = '/^\d{11}$/';
        if (preg_match($phoneNumberPattern, $input)) {
            return 'phone';
        }

        // 使用正则表达式检查是否是邮箱
        $emailPattern = '/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/';
        if (preg_match($emailPattern, $input)) {
            return 'email';
        }

        // 如果都不匹配，则返回未知
        return 'other';
    }
}
