<?php

namespace app\api\service\Sender;

use app\api\service\Sender\Contract\SenderInterface;

class SenderFactory
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
        $files = glob($driverDir . '*Driver.php');

        foreach ($files as $file) {
            $className = 'app\\api\\service\\Sender\\Driver\\' . basename($file, '.php');

            if (!is_subclass_of($className, SenderInterface::class)) {
                continue;
            }

            $meta = $className::meta();
            $type = $meta['type'] ?? strtolower(str_replace('Driver', '', basename($file, '.php')));
            self::$drivers[$type] = $className;
        }
    }

    public static function make(string $slug): SenderInterface
    {
        self::scanDrivers();

        $channel = ChannelManager::getBySlug($slug);
        $type = $channel['driver'] ?? $slug;

        $driverClass = self::$drivers[$type] ?? null;

        if ($driverClass === null) {
            throw new \app\api\ApiException('不支持的发送渠道: ' . $type);
        }

        $meta = $driverClass::meta();
        $channelType = $meta['channelType'] ?? $type;

        return new $driverClass($slug, $channel, $channelType);
    }

    public static function getRegisteredTypes(): array
    {
        self::scanDrivers();
        return array_keys(self::$drivers);
    }

    public static function getDriverClass(string $type): ?string
    {
        self::scanDrivers();
        return self::$drivers[$type] ?? null;
    }

    public static function register(string $type, string $driverClass): void
    {
        self::$drivers[$type] = $driverClass;
    }

    public static function has(string $type): bool
    {
        self::scanDrivers();
        return isset(self::$drivers[$type]);
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
