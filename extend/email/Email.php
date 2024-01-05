<?php

namespace email;

use think\facade\Cache;
use app\common\Common;
use mailer\tp6\Mailer;

class Email
{
    /**
     * 限制发送间隔
     *
     * @param string $key 键名
     * @param integer $time 过期时间
     * @return boolean
     */
    protected static function cacheLog($key, $time = 60): bool
    {
        if (Cache::has($key)) {
            return true;
        } else {
            Cache::set($key, 1, $time);
            return false;
        }
    }

    /**
     * 验证码发送(待优化)
     *
     * @param string $code 验证码
     * @param string $email 邮箱
     * @return array
     */
    public static function SendCaptcha($code, $email): array
    {
        //限制发送间隔
        $key = hash('md5', $code . $email);
        if (!self::cacheLog($key)) { //满足发送间隔
            $mailer = Mailer::instance();
            $mailer->to($email)
                ->subject('验证码')
                ->text('【' . $code . '】5分钟内有效，请勿泄露')
                ->send();
            if ($mailer) {
                return Common::mArrayEasyReturnStruct('验证码发送成功', true);
            }
            return Common::mArrayEasyReturnStruct('发送失败', false);
        }
        return Common::mArrayEasyReturnStruct('刚刚发出，请稍后再试', false);
    }
}
