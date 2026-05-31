<?php
namespace app\frontend\controller;

use app\frontend\service\ThemeEngine;
use think\Response;

class Theme
{
    public function index(): Response
    {
        $uri = request()->pathinfo() ?? '';
        return ThemeEngine::boot($uri);
    }

    /**
     * 服务主题资产（已废弃，由 ThemeEngine::serveAsset() 处理）
     * @deprecated 2.1.0 资产服务已内置到 ThemeEngine::boot() 中
     */
    public function asset(string $path): Response
    {
        $uri = '/' . ltrim($path, '/');
        return ThemeEngine::boot($uri);
    }
}
