<?php

namespace app\api\service\Captcha;

use app\api\service\Captcha\Contract\CaptchaInterface;

class CaptchaFactory
{
    private static array $drivers = [];
    private static bool $scanned = false;

    private static function scanDrivers(): void
    {
        if (self::$scanned) {
            return;
        }
        self::$scanned = true;

        $driverDir = __DIR__ . '/Driver/';
        $files     = glob($driverDir . '*Driver.php');

        foreach ($files as $file) {
            $className = 'app\\api\\service\\Captcha\\Driver\\' . basename($file, '.php');

            if (!is_subclass_of($className, CaptchaInterface::class)) {
                continue;
            }

            $meta = $className::meta();
            $slug = $meta['slug'] ?? strtolower(str_replace('Driver', '', basename($file, '.php')));
            self::$drivers[$slug] = $className;
        }
    }

    public static function make(string $slug, array $config = []): CaptchaInterface
    {
        self::scanDrivers();

        $driverClass = self::$drivers[$slug] ?? null;

        if ($driverClass === null) {
            throw new \app\api\ApiException('不支持的验证驱动: ' . $slug);
        }

        $meta     = $driverClass::meta();
        $type     = $meta['type'] ?? 'captcha';

        return new $driverClass($slug, $config, $type);
    }

    public static function getRegisteredSlugs(): array
    {
        self::scanDrivers();
        return array_keys(self::$drivers);
    }

    public static function getDriverClass(string $slug): ?string
    {
        self::scanDrivers();
        return self::$drivers[$slug] ?? null;
    }

    public static function register(string $slug, string $driverClass): void
    {
        self::$drivers[$slug] = $driverClass;
    }

    public static function has(string $slug): bool
    {
        self::scanDrivers();
        return isset(self::$drivers[$slug]);
    }

    public static function mapMetaType(string $uiType): string
    {
        return match ($uiType) {
            'text', 'password', 'select', 'textarea' => 'string',
            'number'                                  => 'int',
            'checkbox', 'toggle', 'switch'            => 'bool',
            'json'                                    => 'json',
            default                                   => 'string',
        };
    }
}
