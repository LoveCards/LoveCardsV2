<?php

namespace app\api\controller;

use think\facade\Request;
use app\common\cache\CacheManager;

use app\api\service\Theme;
use app\common\Common;
use app\api\service\Config as ConfigService;

use app\api\ApiResponse;

class System extends BaseController
{
    public function themes()
    {
        $tDef_NowThemeDirectory = Theme::mArrayGetThemeDirectory()['N'];
        $lDef_NowThemeInfo = json_decode(@file_get_contents('./theme/' . $tDef_NowThemeDirectory . '/info.ini'), true);
        $lDef_NowThemeInfo['Config'] = Theme::mResultGetThemeConfig($tDef_NowThemeDirectory);
        $lDef_NowThemeInfo['DirectoryName'] = $tDef_NowThemeDirectory;
        $key = $lDef_NowThemeInfo['Name'] . $lDef_NowThemeInfo['Version'] . $lDef_NowThemeInfo['DirectoryName'];
        $lDef_NowThemeInfo['Hash'] = hash('crc32b', $key);
        if (!$lDef_NowThemeInfo) {
            $lDef_NowThemeInfo = json_decode(@file_get_contents('./theme/index/info.ini'), true);
        }

        $lDef_ThemeDirectoryList = array_map('basename', array_filter(glob('./theme/*'), 'is_dir'));
        sort($lDef_ThemeDirectoryList);
        $lDef_ThemeConfigList = array();
        for ($i = 0; $i < count($lDef_ThemeDirectoryList); $i++) {
            $tDef_ThemeBasePath = './theme/' . $lDef_ThemeDirectoryList[$i];
            if (count(glob($tDef_ThemeBasePath . '/*')) > 0) {
                $lDef_ThemeConfigList[$i] = json_decode(@file_get_contents($tDef_ThemeBasePath . '/info.ini'), true);
                $lDef_ThemeConfigList[$i]['DirectoryName'] = $lDef_ThemeDirectoryList[$i];
                $key = $lDef_ThemeConfigList[$i]['Name'] . $lDef_ThemeConfigList[$i]['Version'] . $lDef_ThemeConfigList[$i]['DirectoryName'];
                $hash = hash('crc32b', $key);
                $lDef_ThemeConfigList[$i]['Hash'] = $hash;
                $lDef_ThemeConfigList[$i]['Cover'] = Request::scheme() . '://' . Request::host() . '/theme/' . $lDef_ThemeDirectoryList[$i] . '/show.png';
                if ($lDef_NowThemeInfo['Config']) {
                    $lDef_ThemeConfigList[$i]['Config'] = true;
                } else {
                    $lDef_ThemeConfigList[$i]['Config'] = false;
                }
                if ($hash == $lDef_NowThemeInfo['Hash']) {
                    $lDef_ThemeConfigList[$i]['Status'] = true;
                } else {
                    $lDef_ThemeConfigList[$i]['Status'] = false;
                }
            }
        }

        $tDef_NowThemeConfig = Theme::mResultGetThemeConfig(Theme::mArrayGetThemeDirectory()['N'], true);
        if ($tDef_NowThemeConfig) {
            if (!empty($tDef_NowThemeConfig['Text'])) {
                foreach ($tDef_NowThemeConfig['Text'] as $key => $value) {
                    $tDef_NowThemeConfig['Text'][$key]['Default'] = urldecode($value['Default']);
                }
            }
        }

        $result = [
            "theme_list" => $lDef_ThemeConfigList,
            "theme_config" => $tDef_NowThemeConfig
        ];
        return ApiResponse::createOk($result);
    }

    public function themeSet()
    {
        $tReq_ThemeDirectoryName = Request::param('dir');
        $tReq_ThemeInfo = json_decode(@file_get_contents('./theme/' . $tReq_ThemeDirectoryName . '/info.ini'), true);
        if (!$tReq_ThemeInfo) {
            return ApiResponse::createBadRequest('修改失败，主题信息不存在');
        }
        $tDef_LCVersionInfo = Common::mArrayGetLCVersionInfo();

        if (!($tDef_LCVersionInfo['VerS'] >= $tReq_ThemeInfo['SysVersionL'] && $tDef_LCVersionInfo['VerS'] < $tReq_ThemeInfo['SysVersionR'])) {
            return ApiResponse::createBadRequest('修改失败，该主题不适用当前版本');
        }

        ConfigService::set('core.theme_directory', $tReq_ThemeDirectoryName);

        return ApiResponse::createNoContent();
    }

    public function themeConfig()
    {
        $tDef_ThemeDirectory = ConfigService::get('core.theme_directory', 'index') ?: 'index';

        $lReq_ParamSelect = json_decode(Request::param('select'));
        $lReq_ParamText = json_decode(Request::param('text'));

        $tDef_ThemeConfig = Theme::mResultGetThemeConfig($tDef_ThemeDirectory, true);

        $lDef_ParamThemeConfig = [];
        if (!empty($lReq_ParamSelect)) {
            foreach ($lReq_ParamSelect as $key => $value) {
                if (count($tDef_ThemeConfig['Select'][$key]['Element']) < $value) {
                    return ApiResponse::createBadRequest('修改失败，Select存在非法元素');
                }
                $lDef_ParamThemeConfig['Select' . $key] = $value;
            }
        }

        if (!empty($lReq_ParamText)) {
            foreach ($lReq_ParamText as $key => $value) {
                if (empty($tDef_ThemeConfig['Text'][$key]['Name'])) {
                    return ApiResponse::createBadRequest('修改失败，Text存在非法元素');
                }
                $lDef_ParamThemeConfig['Text' . $key] = $value;
            }
        }

        $tDef_Result = Theme::mBoolCoverThemeConfig($tDef_ThemeDirectory, $lDef_ParamThemeConfig);

        if ($tDef_Result) {
            return ApiResponse::createNoContent();
        } else {
            return ApiResponse::createBadRequest('修改失败，请重试');
        }
    }

    public function update()
    {
        $result = [
            'ver' => Common::mArrayGetLCVersionInfo(),
            'latest' => $this->getLatestVer(),
            'verlog' => $this->getUpdata()
        ];
        return ApiResponse::createOk($result);
    }

    private function getUpdata()
    {
        return CacheManager::get('system', 'Updata', function () {
            $url  = 'https://proxy.gitwarp.com/https://raw.githubusercontent.com/zhiguai/LoveCards/main/VerLog.md';
            $ctx  = stream_context_create([
                'http' => [
                    'method'  => 'GET',
                    'timeout' => 5,
                    'header'  => "User-Agent: PHP\r\n",
                ],
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $jsonStr = @file_get_contents($url, false, $ctx);
            if ($jsonStr === false) {
                return [];
            }

            return $jsonStr;
        }, CacheManager::TTL_LONG * 3);
    }

    private function getLatestVer()
    {
        return CacheManager::get('system', 'LatestVer', function () {
            $url  = 'https://api.github.com/repositories/582292948/releases/latest';
            $ctx  = stream_context_create([
                'http' => [
                    'method'  => 'GET',
                    'timeout' => 5,
                    'header'  => "User-Agent: PHP\r\n",
                ],
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $jsonStr = @file_get_contents($url, false, $ctx);
            if ($jsonStr === false) {
                return [];
            }

            return json_decode($jsonStr, true);
        }, CacheManager::TTL_LONG * 3);
    }
}
