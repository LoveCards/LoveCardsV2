<?php

namespace captcha;

use think\facade\Cache;
use app\common\Common;

class Code
{

    /**
     * 生成随机验证码
     *
     * @param int $l
     * @return string
     */
    protected static function getCode($l): string
    {
        // 设置验证码长度
        $length = $l;
        // 生成随机验证码
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        return strtoupper($code);
    }

    /**
     * 生成验证码
     *
     * @param string $key 键名
     * @param string $app 键名前缀
     * @param integer $time 默认3*60秒
     * @return array
     */
    public static function CreateCaptcha($key, $app = 'public', $time = 300): array
    {
        $code = self::getCode(6);
        $msg = '验证码创建成功';
        $status = true;
        $key = 'Captcha_' . $app . '_' . $key;

        if (!Cache::has($key)) {
            Cache::set($key, $code, $time); //设置缓存
        } else {
            $msg = '验证码未过期';
            $status = false;
            $code = Cache::get($key);
        }

        return common::mArrayEasyReturnStruct($msg, $status, $code);
    }

    /**
     * 校验验证码
     *
     * @param string $key 键名
     * @param string $code 验证码
     * @param string $app 键名前缀
     * @return boolean
     */
    public static function CheckCaptcha($key, $code, $app = 'public'): bool
    {
        $result = Cache::get('Captcha_' . $app . '_' . $key);
        if ($result) {
            if ($result == $code) {
                //校验通过
                return true;
            }
        }
        //验证码不存在
        return false;
    }

    /**
     * 清除验证码缓存
     *
     * @param string $key 键名
     * @param string $app 键名前缀
     * @return boolean
     */
    public static function DeleteCaptcha($key, $app = 'public'): bool
    {
        Cache::delete('Captcha_' . $app . '_' . $key);
        return true;
    }
}
