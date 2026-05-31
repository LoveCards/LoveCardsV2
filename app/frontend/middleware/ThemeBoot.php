<?php
namespace app\frontend\middleware;

use think\Response;
use think\Request;

class ThemeBoot
{
    public function handle(Request $request, \Closure $next)
    {
        $pathinfo = '/' . ltrim($request->pathinfo(), '/');

        $passPrefixes = $this->getPassPrefixes();
        foreach ($passPrefixes as $prefix) {
            if (strpos($pathinfo, $prefix) === 0) {
                return $next($request);
            }
        }

        // 主题引擎接管所有前台请求（含静态资源）。
        // ThemeEngine::boot() 内部区分 isAssetRequest 和页面请求：
        //   - 静态资源 → serveAsset() 从主题目录的 /out/、/dist/、根目录查找文件
        //   - 页面请求 → bootSPA() 或 bootSSR()
        // 此处不调 $next($request)，有意短路中间件链，
        // 下游中间件（CORS、RateLimit 等）仅对 API 生效。
        try {
            $controller = app(\app\frontend\controller\Theme::class);
            return $controller->index();
        } catch (\Throwable $e) {
            \think\facade\Log::error('[ThemeBoot] ' . $e->getMessage());
            return Response::create('<h1>Internal Server Error</h1>', 'html', 500);
        }
    }

    private function getPassPrefixes(): array
    {
        $defaults = ['/api', '/all', '/theme'];
        $custom = \app\common\service\Config::get('frontend.pass_prefixes', []);
        return array_unique(array_merge($defaults, $custom));
    }
}
