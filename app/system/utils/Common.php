<?php

namespace app\system\utils;

class Common
{
    public static $InstallLockPath = "../lock.txt";

    public static function InstallLock(): bool
    {
        if (@fopen(self::$InstallLockPath, 'r')) {
            return true;
        }
        if (file_put_contents(self::$InstallLockPath, "LoveCards.cn")) {
            return true;
        }
        throw new \RuntimeException('安装锁创建失败，请检查权限或手动添加lock.txt文件到根目录！');
    }

    public static function CheckInstallLock(): bool
    {
        return @fopen(self::$InstallLockPath, 'r') !== false;
    }
}
