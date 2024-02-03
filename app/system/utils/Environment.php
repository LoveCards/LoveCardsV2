<?php

namespace app\system\utils;

use app\system\utils\Common;

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
        $extensions = get_loaded_extensions();
        $IE = Common::mArrayGetLCVersionInfo()['InstallEnvironment'];
        $data = [
            'php' => self::EStruct(phpversion(), (($IE['php']['['] <= phpversion()) && (phpversion() <= $IE['php'][')']))),
            'pdo_mysql' => self::EStruct(-1, extension_loaded('pdo')),
            'openssl' => self::EStruct(-1, extension_loaded('openssl')),
        ];
        return $data;
    }
}
