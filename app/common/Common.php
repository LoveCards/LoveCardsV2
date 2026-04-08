<?php

namespace app\common;

use think\facade\Db;

class Common
{
    public static function mArrayGetLCVersionInfo(): array
    {
        return [
            'Name' => 'LoveCards',
            'Url' => '//lovecards.cn',
            'VerS' => '2.4.1',
            'Ver' => '21',
            'GithubUrl' => '//github.com/LoveCards/LoveCardsV2',
            'QGroupUrl' => '//jq.qq.com/?_wv=1027&k=qM8f2RMg',
            'InstallEnvironment' => [
                'php' => [
                    '[' => '7.2.5',
                    ')' => '8.0.99'
                ],
                'mysql' => [
                    '[' => '5.7',
                    ')' => '9999'
                ],
            ]
        ];
    }

    public static function mArrayGetDbSystemData(): array
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
