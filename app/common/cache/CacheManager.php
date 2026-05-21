<?php

namespace app\common\cache;

use think\facade\Cache;

class CacheManager
{
    // ========== TTL 常量 ==========
    const TTL_SHORT    = 60;           // 1 分钟
    const TTL_MEDIUM   = 300;          // 5 分钟
    const TTL_LONG     = 3600;         // 1 小时
    const TTL_DAY      = 86400;        // 1 天
    const TTL_3_DAYS   = 259200;       // 3 天

    // ========== 业务域 → Tag 映射 ==========
    const DOMAIN_TAGS = [
        'rbac'     => 'rbac',
        'jwt'      => 'jwt',
        'captcha'  => 'captcha',
        'email'    => 'email',
        'system'   => 'system',
        'storage'  => 'storage',
        'config'   => 'config',
    ];

    /**
     * 读取缓存（支持 loader 自动回源）
     *
     * @param string $domain 业务域
     * @param string $key 缓存 key
     * @param callable|null $loader 缓存 miss 时的回源函数
     * @param int $ttl TTL（秒），0 = 使用驱动默认
     * @return mixed
     */
    public static function get(string $domain, string $key, ?callable $loader = null, int $ttl = 0): mixed
    {
        $value = Cache::get($key);
        if ($value !== null) {
            return $value;
        }

        if ($loader !== null) {
            $value = $loader();
            self::set($domain, $key, $value, $ttl);
            return $value;
        }

        return null;
    }

    /**
     * 写入缓存（自动打 tag）
     */
    public static function set(string $domain, string $key, mixed $value, int $ttl = 0): void
    {
        $tag = self::DOMAIN_TAGS[$domain] ?? $domain;
        Cache::tag($tag)->set($key, $value, $ttl ?: null);
    }

    /**
     * 删除单个缓存
     */
    public static function delete(string $key): void
    {
        Cache::delete($key);
    }

    /**
     * 检查缓存是否存在
     */
    public static function has(string $key): bool
    {
        return Cache::has($key);
    }

    /**
     * 按业务域清除（精确清除，不影响其他业务）
     */
    public static function clearDomain(string $domain): void
    {
        $tag = self::DOMAIN_TAGS[$domain] ?? $domain;
        Cache::tag($tag)->clear();
    }

    /**
     * 统一 key 构建（冒号分层）
     */
    public static function key(string ...$parts): string
    {
        return implode(':', $parts);
    }
}
