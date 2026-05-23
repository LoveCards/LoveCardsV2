<?php

namespace app\system\utils;

use app\common\VersionService;

class Environment
{

    protected static function EStruct($value, $status)
    {
        return [
            'value' => $value,
            'status' => $status
        ];
    }

    //验证安装环境
    public static function Check()
    {
        $requirements = VersionService::requirements();
        $data = [
            'php' => self::EStruct(phpversion(), (phpversion() >= $requirements['php']['min'] && phpversion() < $requirements['php']['max'])),
            'pdo_mysql' => self::EStruct(-1, extension_loaded('pdo')),
            'openssl' => self::EStruct(-1, extension_loaded('openssl')),
        ];
        return $data;
    }
}
