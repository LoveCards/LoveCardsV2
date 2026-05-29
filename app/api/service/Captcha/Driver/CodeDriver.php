<?php

namespace app\api\service\Captcha\Driver;

use app\api\service\Captcha\Contract\AbstractDriver;
use app\api\service\Sender\Sender;
use app\common\infra\CacheManager;

class CodeDriver extends AbstractDriver
{
    public static function type(): string
    {
        return 'code';
    }

    public static function meta(): array
    {
        return [
            'slug'   => 'smtp_code',
            'type'   => 'code',
            'name'   => '邮箱/短信验证码',
            'icon'   => 'mdi-email-check-outline',
            'fields' => [],
        ];
    }

    public function generate(array $params): array
    {
        $to    = $params['to'];
        $scene = $params['scene'] ?? 'default';
        $ttl   = $params['ttl'] ?? 300;

        $code = $this->createCode(6);
        $key  = 'Captcha_' . $scene . '_' . $to;

        if (CacheManager::has($key)) {
            return ['status' => false, 'msg' => '验证码未过期'];
        }

        CacheManager::set('captcha', $key, $code, $ttl);

        $channelType = $this->config['code_channel'] ?? 'smtp';
        Sender::code($channelType, $to, $code, (int) ceil($ttl / 60));

        return ['status' => true, 'msg' => '验证码已发送'];
    }

    public function verify(array $params): bool
    {
        $key   = $params['key'];
        $code  = $params['code'];
        $scene = $params['scene'] ?? 'default';

        $cacheKey = 'Captcha_' . $scene . '_' . $key;
        $cached   = CacheManager::get('captcha', $cacheKey);

        if ($cached && strtoupper($cached) === strtoupper($code)) {
            CacheManager::delete($cacheKey);
            return true;
        }

        return false;
    }

    public function delete(string $key, string $scene = 'default'): bool
    {
        CacheManager::delete('Captcha_' . $scene . '_' . $key);
        return true;
    }

    private function createCode(int $length): string
    {
        $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $code  = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
    }
}
