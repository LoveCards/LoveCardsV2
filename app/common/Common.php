<?php

namespace app\common;

use think\facade\Config;

class Common
{
    public static function getVersionInfo(): array
    {
        $config = Config::get('apps.version');
        if ($config === null) {
            Config::load(config_path() . 'apps/version.php', 'apps');
            $config = Config::get('apps.version');
        }
        return $config ?: [];
    }
}
