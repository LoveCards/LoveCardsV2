<?php

namespace app\common;

use think\facade\Config;

class VersionService
{
    public static function info(): array
    {
        return Config::get('system') ?: [];
    }

    public static function compare(string $remote): int
    {
        $local = self::info()['version'] ?? '0.0.0';
        return version_compare($local, $remote);
    }

    public static function requirements(): array
    {
        $info = self::info();
        return [
            'php'   => ['min' => $info['php_min'] ?? '7.2.5', 'max' => $info['php_max'] ?? '8.0.99'],
            'mysql' => ['min' => $info['mysql_min'] ?? '5.7', 'max' => $info['mysql_max'] ?? '9999'],
        ];
    }

    public static function public(): array
    {
        $info = self::info();
        return [
            'app_name' => $info['app_name'] ?? '',
            'homepage' => $info['homepage'] ?? '',
            'version'  => $info['version'] ?? '',
            'build'    => $info['build'] ?? 0,
            'github'   => $info['github'] ?? '',
            'qgroup'   => $info['qgroup'] ?? '',
        ];
    }
}
