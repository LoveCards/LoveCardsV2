<?php

namespace app\api\service\Captcha;

use app\api\service\System\Config as ConfigService;

class ChannelManager
{
    private static ?array $settings = null;

    public static function loadSettings(): array
    {
        if (self::$settings === null) {
            self::$settings = ConfigService::getGroup('captcha');
        }
        return self::$settings;
    }

    public static function defaultDriver(string $type): string
    {
        $settings   = self::loadSettings();
        $defaultKey = 'default_' . $type;
        $default    = $settings[$defaultKey] ?? '';

        if (!empty($default)) {
            return $default;
        }

        return match ($type) {
            'code'    => 'smtp_code',
            'captcha' => 'geetest_v4',
            default   => '',
        };
    }

    public static function isEnabled(string $type): bool
    {
        $settings = self::loadSettings();
        $key      = $type . '_enabled';
        return (bool) ($settings[$key] ?? false);
    }

    public static function getCodeChannel(): string
    {
        $settings = self::loadSettings();
        return $settings['code_channel'] ?? 'smtp';
    }
}
