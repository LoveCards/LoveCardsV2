<?php

namespace app\api\service;

use think\facade\Db;

class Config
{
    protected static array $cache = [];
    protected static array $defaults = [];

    /**
     * 获取配置值
     * 优先级：.env > 数据库 > 模板默认值
     */
    public static function get(string $key, $default = null)
    {
        // 支持不带 . 的 key（如 'mail'）
        if (strpos($key, '.') === false) {
            $group = $key;
            $name = null;
        } else {
            [$group, $name] = explode('.', $key, 2);
        }

        // 1. 检查 .env（最高优先级）
        // ThinkPHP 解析 .env 分组格式：[COS] SECRET_ID -> COS_SECRET_ID
        // ConfigService 的 key 格式：storage_cos.secret_id
        // 需要尝试多种 env key 格式
        $envKey = $name !== null ? "{$group}.{$name}" : $group;
        $envKeyUpper = strtoupper($envKey);
        $envKeyFlat = strtoupper(str_replace('.', '_', $envKey));

        // 对于 storage_xxx 分组，尝试去掉 storage_ 前缀
        // storage_cos.secret_id -> COS_SECRET_ID
        $envKeyGroup = '';
        if (strpos($group, 'storage_') === 0) {
            $shortGroup = substr($group, 8); // 'cos'
            $envKeyGroup = strtoupper($shortGroup . '_' . ($name ?? ''));
        }

        $envValue = null;
        foreach ([$envKeyUpper, $envKeyFlat, $envKeyGroup] as $tryKey) {
            if (empty($tryKey)) continue;
            $val = env($tryKey);
            if ($val !== null && $val !== '') {
                $envValue = $val;
                break;
            }
        }

        if ($envValue !== null && $envValue !== '') {
            return self::castValue($envValue, self::getType($group, $name ?? ''));
        }

        // 2. 检查缓存
        $cacheKey = $name !== null ? "{$group}.{$name}" : $group;
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        // 3. 如果没有 name，尝试从数据库读取整个分组
        if ($name === null) {
            $rows = Db::table('configs')
                ->where('group', $group)
                ->select()
                ->toArray();

            if (!empty($rows)) {
                $result = [];
                foreach ($rows as $row) {
                    $result[$row['key']] = self::castValue($row['value'], $row['type']);
                }
                self::$cache[$cacheKey] = $result;
                return $result;
            }

            // fallback: 尝试从框架配置读取
            $frameworkConfig = \think\facade\Config::get($group);
            if (is_array($frameworkConfig)) {
                self::$cache[$cacheKey] = $frameworkConfig;
                return $frameworkConfig;
            }

            return $default;
        }

        // 4. 从数据库读取单个配置
        $row = Db::table('configs')
            ->where('group', $group)
            ->where('key', $name)
            ->find();

        if ($row) {
            $value = self::castValue($row['value'], $row['type']);
            self::$cache[$cacheKey] = $value;
            return $value;
        }

        // 5. 从模板读取默认值
        $defaults = self::getDefaults($group);
        if (isset($defaults[$name])) {
            return $defaults[$name];
        }

        return $default;
    }

    /**
     * 设置配置值
     */
    public static function set(string $key, $value): bool
    {
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
                    'value' => (string) $value,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
        } else {
            $result = Db::table('configs')->insert([
                'group' => $group,
                'key' => $name,
                'value' => (string) $value,
                'type' => self::detectType($value),
                'description' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        // 清除缓存
        unset(self::$cache["{$group}.{$name}"]);

        return (bool) $result;
    }

    /**
     * 获取分组配置
     */
    public static function getGroup(string $group): array
    {
        // 1. 读取模板默认值
        $result = self::getDefaults($group);

        // 2. 读取数据库值覆盖
        $rows = Db::table('configs')
            ->where('group', $group)
            ->select()
            ->toArray();

        foreach ($rows as $row) {
            $result[$row['key']] = self::castValue($row['value'], $row['type']);
        }

        // 3. .env 覆盖
        foreach ($result as $key => $value) {
            $envValue = null;
            // 尝试 GROUP.KEY 格式
            $tryKeys = [
                strtoupper("{$group}.{$key}"),
                strtoupper(str_replace('.', '_', "{$group}.{$key}")),
            ];
            // 对于 storage_xxx 分组，尝试去掉 storage_ 前缀
            if (strpos($group, 'storage_') === 0) {
                $shortGroup = substr($group, 8);
                $tryKeys[] = strtoupper("{$shortGroup}_{$key}");
            }
            foreach ($tryKeys as $tryKey) {
                $val = env($tryKey);
                if ($val !== null && $val !== '') {
                    $envValue = $val;
                    break;
                }
            }
            if ($envValue !== null && $envValue !== '') {
                $result[$key] = self::castValue($envValue, self::getType($group, $key));
            }
        }

        return $result;
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
     * 获取模板默认值
     * 支持 config/apps/storage/ 子目录
     */
    protected static function getDefaults(string $group): array
    {
        if (isset(self::$defaults[$group])) {
            return self::$defaults[$group];
        }

        // 尝试多个路径
        $paths = [
            config_path() . "apps/{$group}.php",
            config_path() . "apps/" . str_replace('_', '/', $group) . ".php",
        ];

        foreach ($paths as $file) {
            if (file_exists($file)) {
                self::$defaults[$group] = include $file;
                return self::$defaults[$group];
            }
        }

        self::$defaults[$group] = [];
        return self::$defaults[$group];
    }

    /**
     * 获取配置类型
     */
    protected static function getType(string $group, string $name): string
    {
        $row = Db::table('configs')
            ->where('group', $group)
            ->where('key', $name)
            ->find();

        return $row['type'] ?? 'string';
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
