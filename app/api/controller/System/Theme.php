<?php

namespace app\api\controller\System;

use app\api\service\System\Theme as CommonTheme;
use app\api\service\System\Config as ConfigService;
use app\common\VersionService;
use app\api\ApiResponse;
use app\api\controller\BaseController;

class Theme extends BaseController
{
    public function config()
    {
        $themeDir = CommonTheme::getThemeDirectory()['N'];
        $themeConfig = CommonTheme::getThemeConfig($themeDir);
        if ($themeConfig === false) {
            $themeConfig = [];
        }

        $themeConfigFull = CommonTheme::getThemeConfig($themeDir, true);
        $data['config'] = $themeConfigFull;

        if (array_key_exists('ThemeDark', $themeConfig)) {
            $darkCookie = cookie('ThemeDark');
            $themeConfig['ThemeDark'] = ($darkCookie != "false");
        }

        $coreConfig = ConfigService::getGroup('core');

        return ApiResponse::createOk([
            'request' => [
                'time' => date('Y-m-d H:i:s'),
                'ip' => request()->ip()
            ],
            'view' => [
                'path' => [
                    'root' => '/theme/' . $themeDir,
                    'assets' => '/theme/' . $themeDir . '/assets',
                ]
            ],
            'system' => [
                'version' => VersionService::public(),
                'config' => [
                    'file' => $coreConfig,
                    'db' => $coreConfig,
                ],
            ],
            'config' => $themeConfig,
        ]);
    }
}
