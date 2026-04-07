<?php

namespace app\common;

use think\facade\Config as ThinkConfig;

class ConfigHelper
{
    public static function get(string $name = null, $default = null)
    {
        return ThinkConfig::get($name, $default);
    }

    public static function save(string $file, array $config): bool
    {
        $configPath = self::getConfigPath($file);
        if (!$configPath) {
            return false;
        }

        $content = '<?php' . PHP_EOL . PHP_EOL . 'return ' . var_export($config, true) . ';' . PHP_EOL;

        $result = file_put_contents($configPath, $content);
        if ($result === false) {
            return false;
        }

        ThinkConfig::set($config, $file);

        if ($file === 'mail') {
            self::clearMailerCache();
        }

        return true;
    }

    protected static function getConfigPath(string $file): string
    {
        $configPath = config_path();
        if (strpos($file, '/') === false && strpos($file, '\\') === false) {
            return $configPath . $file . '.php';
        }
        return $file;
    }

    protected static function clearMailerCache(): void
    {
        if (class_exists('\mailer\lib\Config')) {
            $reflClass = new \ReflectionClass('\mailer\lib\Config');
            $configProp = $reflClass->getProperty('config');
            $configProp->setAccessible(true);
            $isInitProp = $reflClass->getProperty('isInit');
            $isInitProp->setAccessible(true);
            
            $mailConfig = ThinkConfig::get('mail', []);
            $configProp->setValue(null, $mailConfig);
            $isInitProp->setValue(null, true);
        }
    }
}
