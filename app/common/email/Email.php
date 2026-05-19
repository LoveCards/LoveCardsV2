<?php

namespace app\common\email;

use think\facade\Cache;
use think\facade\Config as ThinkConfig;
use app\api\ApiException;
use mailer\tp6\Mailer;

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
        if (Cache::has($key)) {
            return true;
        } else {
            Cache::set($key, 1, $time);
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
                throw ApiException::error('发送失败: ' . $e->getMessage(), ApiException::CODE_SYSTEM_ERROR);
            }
            return;
        }
        throw ApiException::badRequest('刚刚发出，请稍后再试', ApiException::CODE_PARAM_INVALID);
    }
}
