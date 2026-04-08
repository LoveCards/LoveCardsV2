<?php

namespace app\index\common;

use app\common\Common as BaseCommon;
use think\facade\Db;

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
        $result = Db::table('system')->select()->toArray();
        return array_column($result, 'value', 'name');
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
