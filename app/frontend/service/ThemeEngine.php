<?php

namespace app\frontend\service;

use think\Response;
use think\Request;
use app\common\service\Config as ConfigService;
use app\common\infra\CacheManager;

class ThemeEngine
{
    // SSR 预加载数据集定义（与 SDK PUBLIC_API 保持同步）
    const PUBLIC_API = [
        'cards.hot'      => ['method' => 'GET', 'path' => '/api/cards/hot'],
        'cards.list'     => ['method' => 'GET', 'path' => '/api/cards'],
        'cards.get'      => ['method' => 'GET', 'path' => '/api/cards/:id'],
        'cards.search'   => ['method' => 'GET', 'path' => '/api/cards/search'],
        'tags.list'      => ['method' => 'GET', 'path' => '/api/tags'],
        'tags.get'       => ['method' => 'GET', 'path' => '/api/tags/:id'],
        'comments.list'  => ['method' => 'GET', 'path' => '/api/cards/:id/comments'],
        'users.me'       => ['method' => 'GET', 'path' => '/api/users/me'],
        'system.theme'   => ['method' => 'GET', 'path' => '/api/theme/config'],
        'captcha.config' => ['method' => 'GET', 'path' => '/api/captcha/config'],
    ];

    private const MIME_MAP = [
        'js'   => 'application/javascript',
        'mjs'  => 'application/javascript',
        'css'  => 'text/css',
        'json' => 'application/json',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf',
        'otf'  => 'font/otf',
        'webp' => 'image/webp',
        'html' => 'text/html',
        'map'  => 'application/json',
    ];

    // 本请求内 theme.json 缓存，避免重复读取
    private static array $themeCache = [];

    /**
     * 引导加载主题
     */
    public static function boot(string $uri): Response
    {
        if (self::isAssetRequest($uri)) {
            return self::serveAsset($uri);
        }

        $theme = self::getActive();

        if (empty($theme)) {
            return Response::create('<h1>No active theme</h1><p>Please install and activate a theme.</p>', 'html', 200);
        }

        $mode = $theme['mode'] ?? 'spa';

        return match ($mode) {
            'spa' => self::bootSPA($theme),
            'ssr' => self::bootSSR($theme, $uri),
            default => Response::create('<h1>Unknown theme mode</h1>', 'html', 500),
        };
    }

    /**
     * 判断是否为静态资产请求
     */
    private static function isAssetRequest(string $uri): bool
    {
        return preg_match('/\.(js|mjs|css|json|png|jpg|jpeg|gif|svg|ico|woff2?|ttf|otf|webp|map)$/i', $uri) === 1;
    }

    /**
     * 服务静态资产
     */
    private static function serveAsset(string $uri): Response
    {
        $theme = self::getActive();
        if (empty($theme)) {
            return Response::create('Not found', 'html', 404);
        }

        if (strpos($uri, '..') !== false || strpos($uri, "\0") !== false) {
            return Response::create('Forbidden', 'html', 403);
        }

        $normalizedUri = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $uri);

        foreach (['/out', '/dist', ''] as $prefix) {
            $candidate = $theme['path'] . $prefix . $normalizedUri;
            $realFile = realpath($candidate);

            if ($realFile !== false && strpos($realFile, $theme['path'] . DIRECTORY_SEPARATOR) === 0 && is_file($realFile)) {
                return self::sendFile($realFile);
            }
        }

        return Response::create('Not found', 'html', 404);
    }

    /**
     * 发送静态文件
     */
    private static function sendFile(string $filePath): Response
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mime = self::MIME_MAP[$ext] ?? 'application/octet-stream';

        $content = file_get_contents($filePath);
        if ($content === false) {
            return Response::create('Internal Server Error', 'html', 500);
        }

        return Response::create($content, 'html', 200)
            ->header([
                'Content-Type' => $mime,
                'Cache-Control' => 'public, max-age=31536000',
                'X-Content-Type-Options' => 'nosniff',
            ]);
    }

    /**
     * 获取当前活跃主题信息
     */
    public static function getActive(): array
    {
        $activeTheme = ConfigService::get('frontend.active_theme', 'default');
        $themePath = self::getThemePath($activeTheme);

        if ($themePath === null) {
            $themePath = self::getThemePath('default');
            if ($themePath === null) {
                return [];
            }
            $activeTheme = 'default';
        }

        $manifest = $themePath . '/theme.json';
        if (!file_exists($manifest)) {
            return [];
        }

        $theme = self::readManifest($manifest);
        if (!$theme) {
            return [];
        }

        $theme['path'] = $themePath;
        $theme['name'] = $activeTheme;
        return $theme;
    }

    /**
     * SPA 引导：返回 dist/index.html + 注入变量
     */
    private static function bootSPA(array $theme): Response
    {
        $themePath = $theme['path'];
        $candidates = [
            '/out/index.html',
            '/dist/index.html',
            '/index.html',
        ];

        foreach ($candidates as $relative) {
            $candidate = $themePath . $relative;
            $realFile = realpath($candidate);

            if ($realFile !== false && strpos($realFile, $themePath . DIRECTORY_SEPARATOR) === 0 && is_file($realFile)) {
                $html = file_get_contents($realFile);
                if ($html === false) {
                    return Response::create('<h1>Theme index.html unreadable</h1>', 'html', 500);
                }
                $html = self::injectGlobals($html, $theme);
                // Debug: save rendered HTML
                file_put_contents(runtime_path() . 'temp/theme/spa-debug.html', $html);
                return Response::create($html, 'html', 200);
            }
        }

        return Response::create('<h1>Theme index.html not found</h1>', 'html', 404);
    }

    /**
     * SSR 引导：路由匹配 + 内部调度 + 渲染模板
     */
    private static function bootSSR(array $theme, string $uri): Response
    {
        $routes = $theme['routes'] ?? [];

        if (empty($routes)) {
            return Response::create('<h1>No routes defined in theme.json</h1>', 'html', 500);
        }

        $route = self::matchRoute($routes, $uri);

        if (!$route) {
            return self::render404($theme);
        }

        if (($route['auth'] ?? false) && !self::checkAuth()) {
            return Response::create('', 'html', 302)->header(['Location' => '/login']);
        }

        $data = self::fetchSSRData($route['data'] ?? [], $route['params'] ?? []);

        $config = self::getThemeConfig($theme['name']);
        $globals = [
            'apiUrl' => '/api',
            'theme'  => $theme['name'],
            'config' => $config,
        ];

        $templatePath = $theme['path'] . '/templates';
        $templateFile = $route['template'] ?? 'index.html';
        $fullPath = $templatePath . '/' . $templateFile;
        $realTemplatePath = realpath($templatePath);
        $realPath = realpath($fullPath);

        if ($realTemplatePath === false || $realPath === false || strpos($realPath, $realTemplatePath . DIRECTORY_SEPARATOR) !== 0) {
            $safeName = htmlspecialchars($templateFile, ENT_QUOTES, 'UTF-8');
            return Response::create("<h1>Template not found: {$safeName}</h1>", 'html', 500);
        }

        $tpl = new \think\Template([
            'view_path'    => $templatePath . '/',
            'cache_path'   => runtime_path() . 'temp/theme/',
            'cache_suffix' => 'php',
            'tpl_cache'    => true,
            'view_suffix'  => 'html',
        ]);

        $vars = array_merge($globals, [
            '__LC__'      => $globals,
            '__LC_DATA__' => $data,
        ]);

        $html = file_get_contents($realPath);
        if ($html === false) {
            return Response::create('<h1>Template unreadable</h1>', 'html', 500);
        }
        $html = self::processIncludes($html, $templatePath);

        ob_start();
        try {
            $tpl->display($html, $vars);
        } catch (\Throwable $e) {
            ob_end_clean();
            \think\facade\Log::error('[ThemeEngine] Template render error: ' . $e->getMessage());
            return Response::create('<h1>Internal Server Error</h1>', 'html', 500);
        }
        $html = ob_get_clean();

        return Response::create($html, 'html', 200);
    }

    /**
     * 渲染 404 页面
     */
    private static function render404(array $theme): Response
    {
        $template404 = $theme['path'] . '/templates/404.html';
        if (!file_exists($template404)) {
            return Response::create('<h1>404 - Page Not Found</h1>', 'html', 404);
        }

        try {
            $config = self::getThemeConfig($theme['name']);
            $globals = [
                'apiUrl' => '/api',
                'theme'  => $theme['name'],
                'config' => $config,
            ];
            $tpl = new \think\Template([
                'cache_path'   => runtime_path() . 'temp/theme/',
                'cache_suffix' => 'php',
                'tpl_cache'    => true,
            ]);
            $raw = file_get_contents($template404);
            if ($raw === false) {
                return Response::create('<h1>404 - Page Not Found</h1>', 'html', 404);
            }
            $html = self::processIncludes($raw, $theme['path'] . '/templates');
            $vars = array_merge($globals, ['__LC__' => $globals, '__LC_DATA__' => []]);
            ob_start();
            try {
                $tpl->display($html, $vars);
            } catch (\Throwable $e) {
                ob_end_clean();
                \think\facade\Log::error('[ThemeEngine] 404 template render error: ' . $e->getMessage());
                return Response::create('<h1>404 - Page Not Found</h1>', 'html', 404);
            }
            $rendered = ob_get_clean();
            return Response::create($rendered, 'html', 404);
        } catch (\Throwable $e) {
            \think\facade\Log::error('[ThemeEngine] 404 render error: ' . $e->getMessage());
            return Response::create('<h1>404 - Page Not Found</h1>', 'html', 404);
        }
    }

    /**
     * 注入全局变量到 HTML（SPA 模式）
     */
    private static function injectGlobals(string $html, array $theme): string
    {
        $config = self::getThemeConfig($theme['name']);

        $globals = json_encode([
            'apiUrl' => '/api',
            'theme'  => $theme['name'],
            'config' => $config,
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

        $script = "<script>window.__LC__={$globals}</script>";
        return str_replace('</head>', "  {$script}\n</head>", $html);
    }

    /**
     * 获取主题配置值
     */
    public static function getThemeConfig(string $themeName): array
    {
        $dbConfig = ConfigService::get('frontend.theme_config');
        if (!empty($dbConfig) && is_array($dbConfig)) {
            return $dbConfig;
        }

        $themePath = self::getThemePath($themeName);
        if ($themePath === null) {
            return [];
        }

        $manifest = $themePath . '/theme.json';
        if (!file_exists($manifest)) {
            return [];
        }

        $theme = self::readManifest($manifest);
        $config = [];

        foreach (($theme['config'] ?? []) as $key => $def) {
            $config[$key] = $def['default'] ?? '';
        }

        return $config;
    }

    /**
     * 获取主题目录路径（安全校验）
     * @return string|null 返回规范化的绝对路径，不存在或非法时返回 null
     */
    public static function getThemePath(string $name): ?string
    {
        if (!self::isValidThemeName($name)) {
            return null;
        }

        $base = root_path() . 'public' . DIRECTORY_SEPARATOR . 'theme';
        $realBase = realpath($base);
        $path = realpath($base . DIRECTORY_SEPARATOR . $name);

        if ($realBase === false || $path === false || strpos($path, $realBase . DIRECTORY_SEPARATOR) !== 0) {
            return null;
        }

        return $path;
    }

    /**
     * 获取主题目录路径（抛异常版本，用于需要明确错误的场景）
     */
    public static function getThemePathOrFail(string $name): string
    {
        $path = self::getThemePath($name);
        if ($path === null) {
            throw new \app\api\ApiException('主题路径不合法');
        }
        return $path;
    }

    /**
     * 校验主题名合法性（只允许字母数字横杠下划线）
     */
    private static function isValidThemeName(string $name): bool
    {
        return (bool) preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_-]{0,63}$/', $name);
    }

    /**
     * 路由匹配（支持 :id 参数）
     */
    public static function matchRoute(array $routes, string $uri): ?array
    {
        $uri = '/' . trim($uri, '/');

        if (isset($routes[$uri])) {
            return $routes[$uri] + ['params' => [], 'matched_path' => $uri];
        }

        foreach ($routes as $pattern => $route) {
            $quotedPattern = preg_quote($pattern, '~');
            $regex = preg_replace_callback('/\\\\:(\w+)/', function ($m) {
                return '(?P<' . $m[1] . '>[^/]+)';
            }, $quotedPattern);
            $regex = str_replace('\\/', '/', $regex);
            if (preg_match("~^{$regex}$~", $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return $route + ['params' => $params, 'matched_path' => $pattern];
            }
        }

        return null;
    }

    /**
     * 检查鉴权（JWT token 验证）
     */
    private static function checkAuth(): bool
    {
        $token = request()->cookie('token') ?: request()->header('Authorization', '');
        $token = preg_replace('/^Bearer\s+/i', '', trim($token));

        if (empty($token)) {
            return false;
        }

        try {
            \app\common\infra\Jwt::verify($token);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * SSR 数据获取：直接调用 Service 层
     */
    private static function fetchSSRData(array $dataKeys, array $routeParams = []): array
    {
        $result = [];
        $errors = [];

        foreach ($dataKeys as $key) {
            $api = self::PUBLIC_API[$key] ?? null;
            if (!$api) {
                $errors[$key] = ['code' => 400, 'message' => "Unknown data key: {$key}"];
                continue;
            }

            if ($key === 'users.me') {
                continue;
            }

            $path = $api['path'];
            foreach ($routeParams as $paramName => $paramValue) {
                $path = str_replace(':' . $paramName, $paramValue, $path);
            }

            try {
                $parsed = parse_url($path);
                $query = $parsed['query'] ?? '';
                $params = [];
                if ($query) {
                    parse_str($query, $params);
                }

                $data = self::callService($key, $params, $routeParams);
                if ($data !== null) {
                    $result[$key] = ['code' => 200, 'message' => 'ok', 'data' => $data];
                } else {
                    $errors[$key] = ['code' => 500, 'message' => "Service returned null for key: {$key}"];
                }
            } catch (\Throwable $e) {
                \think\facade\Log::error("[ThemeEngine] fetchSSRData({$key}): " . $e->getMessage());
                $errors[$key] = ['code' => 500, 'message' => 'Data fetch failed'];
            }
        }

        if (!empty($errors)) {
            $result['_errors'] = $errors;
        }

        return $result;
    }

    /**
     * Direct service call dispatcher
     */
    private static function callService(string $key, array $params, array $routeParams): ?array
    {
        switch ($key) {
            case 'cards.hot':
                return \app\api\service\Content\Cards::hotList();
            case 'cards.list':
                return \app\api\service\Content\Cards::list($params);
            case 'cards.get':
                $id = $routeParams['id'] ?? 0;
                return \app\api\service\Content\Cards::get((int) $id);
            case 'cards.search':
                return \app\api\service\Content\Cards::list($params);
            case 'tags.list':
                return \app\api\service\Content\Tags::Index($params);
            case 'tags.get':
                $id = $routeParams['id'] ?? 0;
                return \app\api\service\Content\Tags::get((int) $id);
            case 'comments.list':
                $cardId = $routeParams['id'] ?? 0;
                $params['where'] = ['pid' => (int) $cardId];
                return \app\api\service\Content\Comments::listAll($params);
            case 'system.theme':
                $config = self::getThemeConfig(ConfigService::get('frontend.active_theme', 'default'));
                return ['config' => $config];
            default:
                return null;
        }
    }

    /**
     * 列出所有已安装主题
     */
    public static function listThemes(): array
    {
        $themeDir = root_path() . 'public' . DIRECTORY_SEPARATOR . 'theme';
        $themes = [];

        if (!is_dir($themeDir)) {
            return [];
        }

        $dirs = scandir($themeDir);
        $activeTheme = ConfigService::get('frontend.active_theme', 'default');

        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }

            $manifest = $themeDir . DIRECTORY_SEPARATOR . $dir . '/theme.json';
            if (!file_exists($manifest)) {
                continue;
            }

            $meta = self::readManifest($manifest);
            if (!$meta) {
                continue;
            }

            $themes[] = [
                'name'        => $dir,
                'version'     => $meta['version'] ?? '0.0.0',
                'description' => $meta['description'] ?? '',
                'author'      => $meta['author'] ?? '',
                'mode'        => $meta['mode'] ?? 'spa',
                'active'      => $dir === $activeTheme,
            ];
        }

        return $themes;
    }

    /**
     * 激活主题
     */
    public static function activateTheme(string $name): bool
    {
        if (!self::isValidThemeName($name)) {
            throw new \app\api\ApiException('主题名称不合法');
        }

        $themePath = self::getThemePath($name);

        if ($themePath === null) {
            throw new \app\api\ApiException('主题不存在');
        }

        $manifest = $themePath . '/theme.json';

        if (!file_exists($manifest)) {
            throw new \app\api\ApiException('主题不存在');
        }

        $theme = self::readManifest($manifest);
        if (!$theme) {
            throw new \app\api\ApiException('主题配置文件格式错误');
        }

        $config = [];
        foreach (($theme['config'] ?? []) as $key => $def) {
            $config[$key] = $def['default'] ?? '';
        }

        ConfigService::set('frontend.active_theme', $name);
        ConfigService::set('frontend.theme_config', json_encode($config, JSON_UNESCAPED_UNICODE));

        CacheManager::clearDomain('config');

        return true;
    }

    /**
     * 更新主题配置
     */
    public static function updateThemeConfig(array $newValues): bool
    {
        $activeTheme = ConfigService::get('frontend.active_theme', 'default');
        $themePath = self::getThemePath($activeTheme);
        $manifest = $themePath ? $themePath . '/theme.json' : null;

        $allowedKeys = [];
        if ($manifest && file_exists($manifest)) {
            $theme = self::readManifest($manifest);
            $allowedKeys = array_keys($theme['config'] ?? []);
        }

        if (!empty($allowedKeys)) {
            $invalidKeys = array_diff(array_keys($newValues), $allowedKeys);
            if (!empty($invalidKeys)) {
                throw new \app\api\ApiException('不允许的配置项: ' . implode(', ', $invalidKeys));
            }
        } else {
            // 主题无 config schema 时拒绝任意 key 注入
            throw new \app\api\ApiException('当前主题未定义配置项，无法更新');
        }

        $current = self::getThemeConfig($activeTheme);
        $merged = array_merge($current, $newValues);

        ConfigService::set('frontend.theme_config', json_encode($merged, JSON_UNESCAPED_UNICODE));
        CacheManager::clearDomain('config');

        return true;
    }

    /**
     * 固化配置到 theme.json
     */
    public static function freezeConfig(): bool
    {
        $activeTheme = ConfigService::get('frontend.active_theme', 'default');
        $themePath = self::getThemePath($activeTheme);

        if ($themePath === null) {
            throw new \app\api\ApiException('主题路径不合法');
        }

        $manifest = $themePath . '/theme.json';

        if (!file_exists($manifest)) {
            throw new \app\api\ApiException('主题不存在');
        }

        $theme = self::readManifest($manifest);
        if (!$theme) {
            throw new \app\api\ApiException('主题配置文件格式错误');
        }

        $currentConfig = self::getThemeConfig($activeTheme);

        foreach ($currentConfig as $key => $value) {
            if (isset($theme['config'][$key])) {
                $theme['config'][$key]['default'] = $value;
            }
        }

        $json = json_encode($theme, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (file_put_contents($manifest, $json) === false) {
            throw new \app\api\ApiException('写入 theme.json 失败，请检查文件权限');
        }

        self::$themeCache[$manifest] = null;

        return true;
    }

    /**
     * 安装主题（从 ZIP 解压）
     */
    public static function installTheme(string $zipPath): array
    {
        $zip = new \ZipArchive();
        $result = $zip->open($zipPath);

        if ($result !== true) {
            throw new \app\api\ApiException('无法打开 ZIP 文件');
        }

        try {
            $themeJsonContent = null;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = $zip->getNameIndex($i);
                if (basename($entryName) === 'theme.json' && strpos($entryName, '..') === false) {
                    $themeJsonContent = $zip->getFromIndex($i);
                    break;
                }
            }

            if (!$themeJsonContent) {
                throw new \app\api\ApiException('ZIP 中未找到 theme.json');
            }

            $meta = json_decode($themeJsonContent, true);
            if (!$meta || empty($meta['name'])) {
                throw new \app\api\ApiException('theme.json 格式错误或缺少 name 字段');
            }

            $themeName = $meta['name'];

            if (!self::isValidThemeName($themeName)) {
                throw new \app\api\ApiException('theme.json 中的 name 不合法，只允许字母数字横杠下划线');
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = $zip->getNameIndex($i);
                if (strpos($entryName, '..') !== false) {
                    throw new \app\api\ApiException('ZIP 中包含不安全的路径: ' . $entryName);
                }
            }

            $destPath = root_path() . 'public' . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . $themeName;
            $createdDir = false;

            if (!is_dir($destPath)) {
                if (!mkdir($destPath, 0755, true)) {
                    throw new \app\api\ApiException('创建主题目录失败');
                }
                $createdDir = true;
            }

            if ($zip->extractTo($destPath) !== true) {
                if ($createdDir) {
                    self::removeDirectory($destPath);
                }
                throw new \app\api\ApiException('ZIP 解压失败');
            }

            if (!file_exists($destPath . '/theme.json')) {
                if ($createdDir) {
                    self::removeDirectory($destPath);
                }
                throw new \app\api\ApiException('解压后未找到 theme.json');
            }

            self::$themeCache[$destPath . '/theme.json'] = null;

            return [
                'name'        => $themeName,
                'version'     => $meta['version'] ?? '0.0.0',
                'description' => $meta['description'] ?? '',
                'author'      => $meta['author'] ?? '',
                'mode'        => $meta['mode'] ?? 'spa',
            ];
        } finally {
            $zip->close();
        }
    }

    /**
     * 递归删除目录
     */
    private static function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            $realPath = $file->getRealPath();
            if ($file->isDir()) {
                @rmdir($realPath);
            } else {
                @unlink($realPath);
            }
        }
        @rmdir($dir);
    }

    /**
     * 删除主题
     */
    public static function deleteTheme(string $name): bool
    {
        if ($name === 'default') {
            throw new \app\api\ApiException('不能删除默认主题');
        }

        $activeTheme = ConfigService::get('frontend.active_theme', 'default');
        if ($name === $activeTheme) {
            throw new \app\api\ApiException('不能删除当前活跃主题');
        }

        if (!self::isValidThemeName($name)) {
            throw new \app\api\ApiException('主题名称不合法');
        }

        $themePath = self::getThemePath($name);
        if ($themePath === null || !is_dir($themePath)) {
            throw new \app\api\ApiException('主题不存在');
        }

        self::removeDirectory($themePath);

        self::$themeCache[$themePath . '/theme.json'] = null;

        return true;
    }

    /**
     * Process {include file="..." /} directives（安全校验）
     */
    private static function processIncludes(string $html, string $templatePath): string
    {
        return preg_replace_callback('/\{include\s+file="([^"]+)"\s*\/\}/', function ($matches) use ($templatePath) {
            $includeName = $matches[1];

            if (strpos($includeName, '..') !== false || strpos($includeName, "\0") !== false) {
                return '<!-- include blocked: path traversal -->';
            }

            $file = $templatePath . '/' . $includeName . '.html';
            $realTemplatePath = realpath($templatePath);
            $realFile = realpath($file);

            if ($realFile === false || $realTemplatePath === false || strpos($realFile, $realTemplatePath . DIRECTORY_SEPARATOR) !== 0) {
                return '<!-- include blocked: outside template dir -->';
            }

            $content = file_get_contents($realFile);
            if ($content === false) {
                return '<!-- include unreadable: ' . htmlspecialchars($includeName, ENT_QUOTES, 'UTF-8') . ' -->';
            }
            return self::processIncludes($content, $templatePath);
        }, $html);
    }

    /**
     * 读取 theme.json（带内存缓存）
     */
    private static function readManifest(string $path): ?array
    {
        if (isset(self::$themeCache[$path])) {
            return self::$themeCache[$path];
        }

        if (!file_exists($path)) {
            self::$themeCache[$path] = null;
            return null;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            \think\facade\Log::error("[ThemeEngine] Failed to read manifest: {$path}");
            self::$themeCache[$path] = null;
            return null;
        }

        $data = json_decode($content, true);
        if (!is_array($data)) {
            \think\facade\Log::error("[ThemeEngine] Invalid JSON in manifest: {$path}");
            self::$themeCache[$path] = null;
            return null;
        }

        self::$themeCache[$path] = $data;
        return $data;
    }
}
