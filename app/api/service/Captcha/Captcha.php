<?php

namespace app\api\service\Captcha;

use app\api\service\Captcha\Contract\CaptchaInterface;

class Captcha
{
    public static function generate(string $type, array $params = [], ?string $driverSlug = null): array
    {
        if (!self::isEnabled($type)) {
            return ['status' => false, 'msg' => "{$type} 验证已关闭"];
        }

        $driver = self::resolve($type, $driverSlug);
        return $driver->generate($params);
    }

    public static function verify(string $type, array $params, ?string $driverSlug = null): bool
    {
        if (!self::isEnabled($type)) {
            return true;
        }

        $driver = self::resolve($type, $driverSlug);
        return $driver->verify($params);
    }

    public static function driver(string $type, ?string $driverSlug = null): CaptchaInterface
    {
        return self::resolve($type, $driverSlug);
    }

    public static function isEnabled(string $type): bool
    {
        return ChannelManager::isEnabled($type);
    }

    private static function resolve(string $type, ?string $driverSlug): CaptchaInterface
    {
        if ($driverSlug) {
            return CaptchaFactory::make($driverSlug);
        }

        $defaultSlug = ChannelManager::defaultDriver($type);
        return CaptchaFactory::make($defaultSlug);
    }
}
