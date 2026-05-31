<?php

namespace app\api\service\Storage;

use app\common\service\Config;
use think\facade\Db;

class ChannelManager
{
    private static ?array $channels = null;
    private static ?array $settings = null;

    public static function loadChannels(): array
    {
        if (self::$channels === null) {
            self::$channels = [];

            $groups = Db::table('configs')
                ->where('group', 'like', 'storage_%')
                ->distinct()
                ->column('group');

            foreach ($groups as $group) {
                $type = str_replace('storage_', '', $group);
                if ($type === '' || $type === 'storage') continue;
                self::$channels[$type] = array_merge(
                    ['type' => $type],
                    Config::getGroup($group)
                );
            }
        }
        return self::$channels;
    }

    public static function loadSettings(): array
    {
        if (self::$settings === null) {
            self::$settings = Config::getGroup('storage');
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
        $type = $config['type'] ?? $slug;

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
                    && !empty($config['bucket'])
                    && !empty($config['domain']);

            default:
                return !empty($config);
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
            'max' => (int) ($settings['rate_limit_max'] ?? 10),
            'window' => (int) ($settings['rate_limit_window'] ?? 60),
        ];
    }

    public static function getDirectUploadExpire(): int
    {
        $settings = self::loadSettings();
        return (int) ($settings['direct_upload_expire'] ?? 3600);
    }
}
