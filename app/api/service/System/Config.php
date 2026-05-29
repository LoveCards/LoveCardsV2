<?php

namespace app\api\service\System;

use think\facade\Db;
use app\common\infra\CacheManager;

class Config
{
    protected static array $cache = [];
    protected static array $schema = [];

    // ═══════════════════════════════
    //  注册接口
    // ═══════════════════════════════

    /**
     * 扫描 config/apps/*.php 并批量注册
     * 安装时调用一次
     */
    public static function init(): array
    {
        $results = [];
        $files = glob(config_path() . 'apps/*.php');

        foreach ($files as $file) {
            $group = pathinfo($file, PATHINFO_FILENAME);

            $schema = include $file;
            if (!is_array($schema)) continue;

            $results[$group] = self::register($group, $schema, false);
        }

        CacheManager::clearDomain('config');

        return $results;
    }

    /**
     * 注册 schema + seed SQL
     * 安装/升级时调用，运行时不调用
     *
     * @param string $group  配置组名
     * @param array  $schema ['key' => ['type' => 'string', 'default' => '', 'description' => '']]
     * @param bool   $clearCache 是否立即清除缓存（init 批量调用时传 false）
     * @return array ['group', 'seeded', 'skipped']
     */
    public static function register(string $group, array $schema, bool $clearCache = true): array
    {
        self::$schema[$group] = $schema;

        $seeded = [];
        $skipped = [];

        foreach ($schema as $key => $def) {
            $exists = Db::table('configs')
                ->where('group', $group)
                ->where('key', $key)
                ->find();

            if ($exists) {
                $skipped[] = "{$group}.{$key}";
                continue;
            }

            Db::table('configs')->insert([
                'group'       => $group,
                'key'         => $key,
                'value'       => (string)($def['default'] ?? ''),
                'type'        => $def['type'] ?? 'string',
                'description' => $def['description'] ?? '',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
            $seeded[] = "{$group}.{$key}";
        }

        unset(self::$cache[$group]);
        if ($clearCache) {
            CacheManager::clearDomain('config');
        }

        return ['group' => $group, 'seeded' => $seeded, 'skipped' => $skipped];
    }

    // ═══════════════════════════════
    //  读取接口
    // ═══════════════════════════════

    /**
     * 获取单个配置值
     * 优先级：.env → 内存缓存 → CacheManager → SQL → schema 保底
     */
    public static function get(string $key, $default = null)
    {
        if (strpos($key, '.') === false) {
            $group = $key;
            $name = null;
        } else {
            [$group, $name] = explode('.', $key, 2);
        }

        // 1. .env（最高优先级）
        if ($name !== null) {
            $envValue = self::getEnvValue($group, $name);
            if ($envValue !== null) {
                return self::castValue($envValue, self::getTypeFromSchema($group, $name));
            }
        }

        // 2. 内存缓存
        $cacheKey = $name !== null ? "{$group}.{$name}" : $group;
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        // 3. 无 name 时读取整个 group
        if ($name === null) {
            return self::getGroup($group);
        }

        // 4. SQL 查询
        $row = Db::table('configs')
            ->where('group', $group)
            ->where('key', $name)
            ->find();

        if ($row) {
            $value = self::castValue($row['value'], $row['type']);
            self::$cache[$cacheKey] = $value;
            return $value;
        }

        // 5. Schema 保底
        $schema = self::getSchema($group);
        if (isset($schema[$name])) {
            return self::castValue($schema[$name]['default'] ?? '', $schema[$name]['type'] ?? 'string');
        }

        return $default;
    }

    /**
     * 获取分组配置
     * 优先级：CacheManager → SQL → .env 覆盖 → 写入缓存
     */
    public static function getGroup(string $group): array
    {
        // 1. CacheManager
        $cached = CacheManager::get('config', "group:{$group}");
        if ($cached !== null) {
            return $cached;
        }

        // 2. SQL 读取
        $rows = Db::table('configs')
            ->where('group', $group)
            ->select()
            ->toArray();

        if (empty($rows)) {
            return [];
        }

        $result = [];
        foreach ($rows as $row) {
            $result[$row['key']] = self::castValue($row['value'], $row['type']);
        }

        // 3. .env 覆盖
        foreach ($result as $key => $value) {
            $envValue = self::getEnvValue($group, $key);
            if ($envValue !== null) {
                $result[$key] = self::castValue($envValue, self::getTypeFromSchema($group, $key));
            }
        }

        // 4. 写入 CacheManager
        CacheManager::set('config', "group:{$group}", $result, CacheManager::TTL_DAY);

        return $result;
    }

    /**
     * 获取分组 schema
     * 从 SQL 读取该 group 的字段定义
     */
    public static function getSchema(string $group): array
    {
        if (isset(self::$schema[$group])) {
            return self::$schema[$group];
        }

        $rows = Db::table('configs')
            ->where('group', $group)
            ->select()
            ->toArray();

        $schema = [];
        foreach ($rows as $row) {
            $schema[$row['key']] = [
                'type'        => $row['type'] ?? 'string',
                'default'     => $row['value'] ?? '',
                'description' => $row['description'] ?? '',
            ];
        }

        if (!empty($schema)) {
            self::$schema[$group] = $schema;
        }

        return $schema;
    }

    /**
     * 列出所有已注册的 group
     */
    public static function getSchemaGroups(): array
    {
        $rows = Db::table('configs')
            ->distinct(true)
            ->column('group');

        return $rows;
    }

    // ═══════════════════════════════
    //  写入接口
    // ═══════════════════════════════

    /**
     * 设置单个配置值
     */
    public static function set(string $key, $value): bool
    {
        if (strpos($key, '.') === false) {
            throw new \InvalidArgumentException("Config key must be 'group.name', got: {$key}");
        }

        [$group, $name] = explode('.', $key, 2);

        $exists = Db::table('configs')
            ->where('group', $group)
            ->where('key', $name)
            ->find();

        if ($exists) {
            $result = Db::table('configs')
                ->where('group', $group)
                ->where('key', $name)
                ->update([
                    'value'      => (string) $value,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
        } else {
            $result = Db::table('configs')->insert([
                'group'       => $group,
                'key'         => $name,
                'value'       => (string) $value,
                'type'        => self::detectType($value),
                'description' => '',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        unset(self::$cache["{$group}.{$name}"]);
        unset(self::$cache[$group]);
        CacheManager::clearDomain('config');

        return (bool) $result;
    }

    /**
     * 批量设置配置
     */
    public static function setGroup(string $group, array $config): bool
    {
        foreach ($config as $key => $value) {
            self::set("{$group}.{$key}", $value);
        }
        return true;
    }

    /**
     * 删除配置组
     */
    public static function deleteGroup(string $group): bool
    {
        Db::table('configs')->where('group', $group)->delete();
        unset(self::$cache[$group]);
        unset(self::$cache["group:" . $group]);
        unset(self::$schema[$group]);
        CacheManager::clearDomain('config');
        return true;
    }

    /**
     * 删除配置键
     */
    public static function deleteKey(string $group, string $key): bool
    {
        Db::table('configs')->where('group', $group)->where('key', $key)->delete();
        unset(self::$cache["{$group}.{$key}"]);
        unset(self::$cache[$group]);
        unset(self::$schema[$group]);
        CacheManager::clearDomain('config');
        return true;
    }

    // ═══════════════════════════════
    //  管理接口
    // ═══════════════════════════════

    /**
     * 重载配置缓存
     */
    public static function reload(?string $group = null): void
    {
        if ($group === null) {
            self::$cache = [];
            self::$schema = [];
        } else {
            unset(self::$cache[$group]);
            unset(self::$cache["group:{$group}"]);
            unset(self::$schema[$group]);
        }
        CacheManager::clearDomain('config');
    }

    // ═══════════════════════════════
    //  内部方法
    // ═══════════════════════════════

    /**
     * 从 .env 读取值
     */
    protected static function getEnvValue(string $group, string $name): ?string
    {
        $tryKeys = [
            strtoupper("{$group}.{$name}"),
            strtoupper(str_replace('.', '_', "{$group}.{$name}")),
        ];

        if (strpos($group, 'storage_') === 0) {
            $shortGroup = substr($group, 8);
            $tryKeys[] = strtoupper("{$shortGroup}_{$name}");
        }

        foreach ($tryKeys as $tryKey) {
            $val = env($tryKey);
            if ($val !== null && $val !== '') {
                return $val;
            }
        }

        return null;
    }

    /**
     * 从 schema 获取字段类型
     */
    protected static function getTypeFromSchema(string $group, string $name): string
    {
        $schema = self::getSchema($group);
        return $schema[$name]['type'] ?? 'string';
    }

    /**
     * 类型转换
     */
    protected static function castValue($value, string $type)
    {
        switch ($type) {
            case 'bool':
                return in_array($value, ['1', 'true', 'yes'], true);
            case 'int':
                if ($value === '' || $value === null) return 0;
                return (int) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    /**
     * 自动检测类型
     */
    protected static function detectType($value): string
    {
        if (is_bool($value)) return 'bool';
        if (is_int($value)) return 'int';
        if (is_array($value)) return 'json';
        return 'string';
    }
}
