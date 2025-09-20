<?php

namespace app\api\controller\public;

use think\facade\View;

use app\common\File;
use app\common\Theme as CommonTheme;
use app\common\Common;

use think\facade\Config;

use app\common\ConfigFacade;

use app\api\controller\BaseController;
use app\api\controller\ApiResponse;

class Theme extends BaseController
{
    function Config()
    {
        //获取主题配置
        $lRes_ThemeConfig = CommonTheme::mResultGetThemeConfig(CommonTheme::mArrayGetThemeDirectory()['N']);
        if ($lRes_ThemeConfig === false) {
            $lRes_ThemeConfig = [];
        }
        //获取主题原配置
        $data['config'] = CommonTheme::mResultGetThemeConfig(CommonTheme::mArrayGetThemeDirectory()['N'], true);

        //主题dark模式支持
        //dd(cookie('ThemeDark'));
        if (array_key_exists('ThemeDark', $lRes_ThemeConfig)) {
            if (cookie('ThemeDark') != null) {
                if (cookie('ThemeDark') == "false") {
                    $lRes_ThemeConfig['ThemeDark'] = false;
                } else {
                    $lRes_ThemeConfig['ThemeDark'] = true;
                }
            }
        }

        //根据主题覆盖模板配置
        //CommonTheme::mObjectEasySetViewConfig(CommonTheme::mArrayGetThemeDirectory()['N']);

        //读取系统配置
        $SyetemFileConfig = $this->SYSTEM_CONFIG;
        unset($SyetemFileConfig['Geetest']['Key']);

        //公共模板变量
        $data = [
            'request' => [
                'time' => date('Y-m-d H:i:s'),
                'ip' => Common::mStringGetIP()
            ],
            'view' => [
                'path' => [
                    'root' => '/theme/' . CommonTheme::mArrayGetThemeDirectory()['N'],
                    'assets' => '/theme/' . CommonTheme::mArrayGetThemeDirectory()['N'] . '/assets',
                ]
            ],
            'system' => [
                'version' => Common::mArrayGetLCVersionInfo(),
                'config' => [
                    'file' => $SyetemFileConfig,
                    'db' => Common::mArrayGetDbSystemData(),
                ],
            ],
            'config' => $lRes_ThemeConfig,
        ];

        return ApiResponse::createSuccess($data);
    }
}
