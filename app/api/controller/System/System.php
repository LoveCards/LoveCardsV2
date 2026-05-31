<?php

namespace app\api\controller\System;

use think\facade\Request;
use app\common\infra\CacheManager;

use app\api\service\System\VersionService;
use app\common\service\Config as ConfigService;

use app\api\ApiResponse;
use app\api\controller\BaseController;

class System extends BaseController
{
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
