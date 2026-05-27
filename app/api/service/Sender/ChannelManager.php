<?php

namespace app\api\service\Sender;

use app\api\service\System\Config;
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
                ->where('group', 'like', 'sender_%')
                ->distinct()
                ->column('group');

            foreach ($groups as $group) {
                $type = str_replace('sender_', '', $group);
                if ($type === '' || $type === 'sender') continue;
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
            self::$settings = Config::getGroup('sender');
        }
        return self::$settings;
    }

    public static function getBySlug(string $slug): array
    {
        $channels = self::loadChannels();

        if (!isset($channels[$slug])) {
            throw new \app\api\ApiException('发送渠道不存在: ' . $slug);
        }

        $channel = $channels[$slug];
        $channel['slug'] = $slug;

        return $channel;
    }

    public static function getDefault(string $channelType = ''): string
    {
        $settings = self::loadSettings();

        if ($channelType !== '') {
            $key = 'default_' . $channelType;
            $defaultSlug = $settings[$key] ?? '';
            if (!empty($defaultSlug) && self::exists($defaultSlug)) {
                return $defaultSlug;
            }

            $channels = self::loadChannels();
            foreach ($channels as $slug => $channel) {
                if (($channel['channelType'] ?? $slug) === $channelType) {
                    return $slug;
                }
            }

            throw new \app\api\ApiException("未配置 {$channelType} 类型的发送渠道");
        }

        return $settings['default'] ?? 'email';
    }

    public static function getDefaultChannel(): array
    {
        $slug = self::getDefault();
        return self::getBySlug($slug);
    }

    public static function exists(string $slug): bool
    {
        $channels = self::loadChannels();
        return isset($channels[$slug]);
    }
}
