<?php

namespace app\index\common;

use app\common\Common as BaseCommon;
use app\api\service\Config as ConfigService;

class Common extends BaseCommon
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
        return ConfigService::getGroup('core');
    }

    public static function mStringGetIP($type = 0): string
    {
        $type = $type ? 1 : 0;
        static $ip = NULL;
        if ($ip !== NULL) return $ip[$type];
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) unset($arr[$pos]);
            $ip = trim($arr[0]);
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $long = ip2long($ip);
        $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }

    public static function mArrayEasyReturnStruct(string $msg = null, bool $status = true, $data = null): array
    {
        return [
            'status' => $status,
            'msg' => $msg,
            'data' => $data,
        ];
    }
}
