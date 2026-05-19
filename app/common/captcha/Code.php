<?php

namespace app\common\captcha;

use think\facade\Cache;

class Code
{
    protected static function getCode($l): string
    {
        $length = $l;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        return strtoupper($code);
    }

    public static function CreateCaptcha($key, $app = 'public', $time = 300): array
    {
        $code = self::getCode(6);
        $msg = '验证码创建成功';
        $status = true;
        $key = 'Captcha_' . $app . '_' . $key;

        if (!Cache::has($key)) {
            Cache::set($key, $code, $time);
        } else {
            $msg = '验证码未过期';
            $status = false;
            $code = Cache::get($key);
        }

        return [
            'status' => $status,
            'msg' => $msg,
            'data' => $code,
        ];
    }

    public static function CheckCaptcha($key, $code, $app = 'public'): bool
    {
        $result = Cache::get('Captcha_' . $app . '_' . $key);
        if ($result) {
            if ($result == $code) {
                return true;
            }
        }
        return false;
    }

    public static function DeleteCaptcha($key, $app = 'public'): bool
    {
        Cache::delete('Captcha_' . $app . '_' . $key);
        return true;
    }
}
