<?php

namespace app\system\utils;

use app\common\Common as ThinkCommon;

class Common
{

    //安装锁位置
    public static $InstallLockPath = "../lock.txt";

    /**
     * 创建安装锁
     *
     * @return array
     */
    public static function InstallLock()
    {
        if (@fopen(self::$InstallLockPath, 'r')) {
            return Common::mArrayEasyReturnStruct('安装锁已存在', true);
        } else {
            if (file_put_contents(self::$InstallLockPath, "LoveCards.cn")) {
                return Common::mArrayEasyReturnStruct('安装锁已创建', true);
            } else {
                return Common::mArrayEasyReturnStruct('安装锁创建失败，请检查权限或手动添加lock.txt文件到根目录！', false);
            }
        }
    }

    /**
     * 检查安装锁
     *
     * @return array
     */
    public static function CheckInstallLock()
    {
        if (@fopen(self::$InstallLockPath, 'r')) {
            return Common::mArrayEasyReturnStruct('安装锁已存在', true);
        }
        return Common::mArrayEasyReturnStruct('安装锁不存在', false);
    }

    //继承
    public static function mArrayEasyReturnStruct(string $msg = null, bool $status = true, $data = null)
    {
        return ThinkCommon::mArrayEasyReturnStruct($msg, $status, $data);
    }
    public static function mArrayGetLCVersionInfo()
    {
        return ThinkCommon::mArrayGetLCVersionInfo();
    }
}
