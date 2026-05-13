<?php

namespace app\api\service\Storage;

use app\api\service\Storage\Driver\LocalStorage;
use app\api\service\Storage\Driver\OssStorage;
use app\api\service\Storage\Driver\CosStorage;
use app\api\service\Storage\Driver\QiniuStorage;
use app\api\service\Storage\Driver\SmmsStorage;
use app\api\service\Storage\Contract\StorageInterface;

class StorageFactory
{
    private static array $drivers = [
        'local' => LocalStorage::class,
        'oss' => OssStorage::class,
        'cos' => CosStorage::class,
        'qiniu' => QiniuStorage::class,
        'smms' => SmmsStorage::class,
    ];

    public static function make(string $slug): StorageInterface
    {
        $channel = ChannelManager::getBySlug($slug);

        $driverClass = self::$drivers[$slug] ?? null;

        if ($driverClass === null) {
            throw new \app\api\ApiException('不支持的存储渠道: ' . $slug);
        }

        return new $driverClass($slug, $channel);
    }

    public static function register(string $slug, string $driverClass): void
    {
        if (!in_array(StorageInterface::class, class_implements($driverClass))) {
            throw new \app\api\ApiException('驱动类必须实现 StorageInterface 接口');
        }

        self::$drivers[$slug] = $driverClass;
    }

    public static function getDrivers(): array
    {
        return array_keys(self::$drivers);
    }

    public static function has(string $slug): bool
    {
        return isset(self::$drivers[$slug]);
    }
}