<?php
/*
 * @Description: 
 * @Author: github.com/zhiguai
 * @Date: 2026-04-12 13:34:02
 * @Email: 2903074366@qq.com
 */

namespace app\api\controller\public;

use think\facade\View;
use think\facade\Config;
use think\facade\Db;

use app\common\Theme as CommonTheme;

use app\api\controller\BaseController;
use app\api\ApiResponse;

class Theme extends BaseController
{
    function Config()
    {
        $lRes_ThemeConfig = CommonTheme::mResultGetThemeConfig(CommonTheme::mArrayGetThemeDirectory()['N']);
        if ($lRes_ThemeConfig === false) {
            $lRes_ThemeConfig = [];
        }
        $data['config'] = CommonTheme::mResultGetThemeConfig(CommonTheme::mArrayGetThemeDirectory()['N'], true);

        if (array_key_exists('ThemeDark', $lRes_ThemeConfig)) {
            if (cookie('ThemeDark') != null) {
                if (cookie('ThemeDark') == "false") {
                    $lRes_ThemeConfig['ThemeDark'] = false;
                } else {
                    $lRes_ThemeConfig['ThemeDark'] = true;
                }
            }
        }

        $SyetemFileConfig = Config::get('master');
        unset($SyetemFileConfig['Geetest']['Key']);

        $data = [
            'request' => [
                'time' => date('Y-m-d H:i:s'),
                'ip' => request()->ip()
            ],
            'view' => [
                'path' => [
                    'root' => '/theme/' . CommonTheme::mArrayGetThemeDirectory()['N'],
                    'assets' => '/theme/' . CommonTheme::mArrayGetThemeDirectory()['N'] . '/assets',
                ]
            ],
            'system' => [
                'version' => [
                    'Name' => 'LoveCards',
                    'Url' => '//lovecards.cn',
                    'VerS' => '2.4.1',
                    'Ver' => '21',
                    'GithubUrl' => '//github.com/LoveCards/LoveCardsV2',
                    'QGroupUrl' => '//jq.qq.com/?_wv=1027&k=qM8f2RMg',
                ],
                'config' => [
                    'file' => $SyetemFileConfig,
                    'db' => array_column(Db::table('system')->select()->toArray(), 'value', 'name'),
                ],
            ],
            'config' => $lRes_ThemeConfig,
        ];

        return ApiResponse::createOk($data);
    }
}
