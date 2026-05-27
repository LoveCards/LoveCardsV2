<?php

namespace app\common\email;

use app\common\cache\CacheManager;
use think\facade\Config as ThinkConfig;
use mailer\tp6\Mailer;

/**
 * @deprecated 使用 app\api\service\Sender\SenderManager 替代
 */
class Email
{
    protected static function resetMailerConfig(): void
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

    protected static function cacheLog($key, $time = 60): bool
    {
        if (CacheManager::has($key)) {
            return true;
        } else {
            CacheManager::set('email', $key, 1, $time);
            return false;
        }
    }

    public static function SendCaptcha($code, $email): void
    {
        $key = hash('md5', $code . $email);
        if (!self::cacheLog($key)) {
            self::resetMailerConfig();
            
            try {
                $mailer = new Mailer();
                $mailer->to($email)
                    ->subject('验证码')
                    ->text('【' . $code . '】5分钟内有效，请勿泄露')
                    ->send();
            } catch (\Throwable $e) {
                throw new \RuntimeException('发送失败: ' . $e->getMessage());
            }
            return;
        }
        throw new \RuntimeException('刚刚发出，请稍后再试');
    }
}
