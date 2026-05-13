<?php

namespace app\api\service\Storage;

class ChannelManager
{
    private static ?array $channels = null;
    private static ?array $settings = null;

    public static function loadChannels(): array
    {
        if (self::$channels === null) {
            // 优先使用框架配置加载
            $config = \think\facade\Config::get('core.storage.channels');
            if (is_array($config)) {
                self::$channels = $config;
            } else {
                // 手动加载：尝试多个可能路径
                $basePath = app()->getConfigPath();
                $possiblePaths = [
                    $basePath . 'core' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'channels.php',
                    $basePath . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'channels.php',
                ];

                self::$channels = [];
                foreach ($possiblePaths as $configFile) {
                    $configFile = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $configFile);
                    if (file_exists($configFile)) {
                        self::$channels = include $configFile;
                        break;
                    }
                }
            }
        }
        return self::$channels;
    }

    public static function loadSettings(): array
    {
        if (self::$settings === null) {
            $config = \think\facade\Config::get('core.storage.settings');
            if (is_array($config)) {
                self::$settings = $config;
            } else {
                $basePath = app()->getConfigPath();
                $possiblePaths = [
                    $basePath . 'core' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'settings.php',
                    $basePath . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'settings.php',
                ];

                self::$settings = [];
                foreach ($possiblePaths as $configFile) {
                    $configFile = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $configFile);
                    if (file_exists($configFile)) {
                        self::$settings = include $configFile;
                        break;
                    }
                }
            }
        }
        return self::$settings;
    }

    public static function getBySlug(string $slug): array
    {
        $channels = self::loadChannels();

        if (!isset($channels[$slug])) {
            throw new \app\api\ApiException('存储渠道不存在: ' . $slug);
        }

        $channel = $channels[$slug];
        $channel['slug'] = $slug;

        return $channel;
    }

    public static function getDefault(): array
    {
        $settings = self::loadSettings();
        $defaultSlug = $settings['default'] ?? '';

        if (!empty($defaultSlug) && self::exists($defaultSlug)) {
            $config = self::getBySlug($defaultSlug);
            $config['slug'] = $defaultSlug;
            return $config;
        }

        $channels = self::loadChannels();

        if (empty($channels)) {
            throw new \app\api\ApiException('未配置任何存储渠道');
        }

        $firstChannel = reset($channels);
        $firstSlug = key($channels);
        $firstChannel['slug'] = $firstSlug;

        return $firstChannel;
    }

    public static function getAll(): array
    {
        $channels = self::loadChannels();
        $result = [];

        foreach ($channels as $slug => $config) {
            $config['slug'] = $slug;
            $result[] = $config;
        }

        return $result;
    }

    public static function getAllEnabled(): array
    {
        return self::getAll();
    }

    public static function exists(string $slug): bool
    {
        $channels = self::loadChannels();
        return isset($channels[$slug]);
    }

    public static function isAvailable(string $slug): bool
    {
        if (!self::exists($slug)) {
            return false;
        }

        $config = self::getBySlug($slug);
        $type = $config['type'] ?? '';

        switch ($type) {
            case 'local':
                return !empty($config['root']);

            case 'oss':
                return !empty($config['access_key'])
                    && !empty($config['secret_key'])
                    && !empty($config['bucket'])
                    && !empty($config['endpoint']);

            case 'cos':
                return !empty($config['secret_id'])
                    && !empty($config['secret_key'])
                    && !empty($config['bucket'])
                    && !empty($config['region']);

            case 'qiniu':
                return !empty($config['access_key'])
                    && !empty($config['secret_key'])
                    && !empty($config['bucket']);

            case 'api':
                return !empty($config['api_key']);

            default:
                return false;
        }
    }

    public static function getAvailableChannels(): array
    {
        $channels = self::loadChannels();
        $result = [];

        foreach ($channels as $slug => $config) {
            if (self::isAvailable($slug)) {
                $config['slug'] = $slug;
                $result[] = $config;
            }
        }

        return $result;
    }

    public static function getDefaultChannel(): array
    {
        $default = self::getDefault();

        if (!self::isAvailable($default['slug'])) {
            throw new \app\api\ApiException('默认存储通道不可用，请联系管理员');
        }

        return $default;
    }

    public static function getRateLimitSettings(): array
    {
        $settings = self::loadSettings();
        return [
            'max' => (int) ($settings['rate_limit']['max'] ?? 10),
            'window' => (int) ($settings['rate_limit']['window'] ?? 60),
        ];
    }

    public static function getDirectUploadExpire(): int
    {
        $settings = self::loadSettings();
        return (int) ($settings['direct_upload']['expire'] ?? 3600);
    }
}