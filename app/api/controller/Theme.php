<?php

namespace app\api\controller;

use app\api\service\Theme as CommonTheme;
use app\api\service\Config as ConfigService;
use app\api\ApiResponse;

class Theme extends BaseController
{
    public function config()
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

        $coreConfig = ConfigService::getGroup('core');

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
                    'file' => $coreConfig,
                    'db' => $coreConfig,
                ],
            ],
            'config' => $lRes_ThemeConfig,
        ];

        return ApiResponse::createOk($data);
    }
}
