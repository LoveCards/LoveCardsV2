<?php

namespace app\api\service;

class BaseService
{

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
}
