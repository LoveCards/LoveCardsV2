<?php

namespace app\api\controller\System;

use think\facade\Request;
use app\common\infra\CacheManager;

use app\api\service\System\Theme;
use app\api\service\System\VersionService;
use app\api\service\System\Config as ConfigService;

use app\api\ApiResponse;
use app\api\controller\BaseController;

class System extends BaseController
{
    public function themes()
    {
        $currentThemeDir = Theme::getThemeDirectory()['N'];
        $currentThemeInfo = json_decode(@file_get_contents('./theme/' . $currentThemeDir . '/info.ini'), true);
        $currentThemeInfo['Config'] = Theme::getThemeConfig($currentThemeDir);
        $currentThemeInfo['DirectoryName'] = $currentThemeDir;
        $hashKey = $currentThemeInfo['Name'] . $currentThemeInfo['Version'] . $currentThemeInfo['DirectoryName'];
        $currentThemeInfo['Hash'] = hash('crc32b', $hashKey);
        if (!$currentThemeInfo) {
            $currentThemeInfo = json_decode(@file_get_contents('./theme/index/info.ini'), true);
        }

        $themeDirList = array_map('basename', array_filter(glob('./theme/*'), 'is_dir'));
        sort($themeDirList);
        $themeConfigList = [];
        foreach ($themeDirList as $i => $dirName) {
            $basePath = './theme/' . $dirName;
            if (count(glob($basePath . '/*')) <= 0) continue;

            $info = json_decode(@file_get_contents($basePath . '/info.ini'), true);
            if (!$info) continue;

            $info['DirectoryName'] = $dirName;
            $key = $info['Name'] . $info['Version'] . $info['DirectoryName'];
            $hash = hash('crc32b', $key);
            $info['Hash'] = $hash;
            $info['Cover'] = Request::scheme() . '://' . Request::host() . '/theme/' . $dirName . '/show.png';
            $info['Config'] = $currentThemeInfo['Config'] ? true : false;
            $info['Status'] = ($hash == $currentThemeInfo['Hash']);

            $themeConfigList[$i] = $info;
        }

        $themeConfig = Theme::getThemeConfig(Theme::getThemeDirectory()['N'], true);
        if ($themeConfig && !empty($themeConfig['Text'])) {
            foreach ($themeConfig['Text'] as $key => $value) {
                $themeConfig['Text'][$key]['Default'] = urldecode($value['Default']);
            }
        }

        return ApiResponse::createOk([
            'theme_list' => $themeConfigList,
            'theme_config' => $themeConfig
        ]);
    }

    public function themeSet()
    {
        $themeDir = Request::param('dir');
        $themeInfo = json_decode(@file_get_contents('./theme/' . $themeDir . '/info.ini'), true);
        if (!$themeInfo) {
            return ApiResponse::createBadRequest('修改失败，主题信息不存在');
        }

        $version = VersionService::info()['version'] ?? '0.0.0';
        if (!($version >= $themeInfo['SysVersionL'] && $version < $themeInfo['SysVersionR'])) {
            return ApiResponse::createBadRequest('修改失败，该主题不适用当前版本');
        }

        ConfigService::set('core.theme_directory', $themeDir);

        return ApiResponse::createNoContent();
    }

    public function themeConfig()
    {
        $themeDir = ConfigService::get('core.theme_directory', 'index') ?: 'index';

        $selectData = json_decode(Request::param('select'), true);
        $textData = json_decode(Request::param('text'), true);

        $themeConfig = Theme::getThemeConfig($themeDir, true);

        $config = [];
        if (!empty($selectData)) {
            foreach ($selectData as $key => $value) {
                if (count($themeConfig['Select'][$key]['Element']) < $value) {
                    return ApiResponse::createBadRequest('修改失败，Select存在非法元素');
                }
                $config['Select' . $key] = $value;
            }
        }

        if (!empty($textData)) {
            foreach ($textData as $key => $value) {
                if (empty($themeConfig['Text'][$key]['Name'])) {
                    return ApiResponse::createBadRequest('修改失败，Text存在非法元素');
                }
                $config['Text' . $key] = $value;
            }
        }

        $result = Theme::coverThemeConfig($themeDir, $config);

        if ($result) {
            return ApiResponse::createNoContent();
        }
        return ApiResponse::createBadRequest('修改失败，请重试');
    }

    public function update()
    {
        return ApiResponse::createOk([
            'ver' => VersionService::public(),
            'latest' => $this->getLatestVer(),
            'verlog' => $this->getUpdata()
        ]);
    }

    private function getUpdata()
    {
        return CacheManager::get('system', 'Updata', function () {
            $url = 'https://proxy.gitwarp.com/https://raw.githubusercontent.com/zhiguai/LoveCards/main/VerLog.md';
            $ctx = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 5,
                    'header' => "User-Agent: PHP\r\n",
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $result = @file_get_contents($url, false, $ctx);
            return $result === false ? [] : $result;
        }, CacheManager::TTL_LONG * 3);
    }

    private function getLatestVer()
    {
        return CacheManager::get('system', 'LatestVer', function () {
            $url = 'https://api.github.com/repositories/582292948/releases/latest';
            $ctx = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 5,
                    'header' => "User-Agent: PHP\r\n",
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $result = @file_get_contents($url, false, $ctx);
            return $result === false ? [] : json_decode($result, true);
        }, CacheManager::TTL_LONG * 3);
    }
}
