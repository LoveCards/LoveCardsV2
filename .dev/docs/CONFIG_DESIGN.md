# 配置模块设计文档

> 版本：v2.0 | 日期：2026-05-21 | 状态：已实施

---

## 一、设计目标

1. **ConfigService 只提供能力，不依赖任何模块** — 纯粹的注册/读写/缓存接口
2. **config 文件定位为 schema 模板** — 安装时的数据源，运行时不读取
3. **运行时零文件扫描** — 只读 SQL + 缓存，LNMP 友好
4. **子级能力配置通过 API 注册** — Driver 等子模块不依赖外部文件
5. **CacheManager 缓存层** — 24h TTL，set/reload 时主动失效

---

## 二、配置分层

```
┌─────────────────────────────────────────────────────────────┐
│  第一层：环境变量 (.env)                                      │
│  优先级：最高                                                  │
│  用途：部署覆盖，敏感信息                                      │
├─────────────────────────────────────────────────────────────┤
│  第二层：内存缓存                                              │
│  用途：请求级热缓存                                            │
├─────────────────────────────────────────────────────────────┤
│  第三层：CacheManager (config 域)                             │
│  TTL：24h                                                     │
│  用途：跨请求持久化缓存                                        │
├─────────────────────────────────────────────────────────────┤
│  第四层：SQL (configs 表)                                     │
│  用途：运行时配置值，前端管理页面写入                            │
├─────────────────────────────────────────────────────────────┤
│  第五层：Schema 保底                                          │
│  来源：注册时 seed 到 SQL 的默认值                              │
│  用途：SQL 无记录时的兜底                                      │
└─────────────────────────────────────────────────────────────┘
```

**读取优先级**：`.env` → 内存缓存 → CacheManager → SQL → schema 保底

---

## 三、核心设计

### 3.1 Schema 模板格式

`config/apps/*.php` 采用统一 schema 格式：

```php
// config/apps/cards.php
return [
    'approve'         => ['type' => 'bool',    'default' => false, 'description' => '卡片发布审核'],
    'picture_limit'   => ['type' => 'int',     'default' => 15,    'description' => '图片数量限制'],
    'tag_limit'       => ['type' => 'int',     'default' => 3,     'description' => '标签数量限制'],
    'image_size'      => ['type' => 'int',     'default' => 3,     'description' => '图片大小限制(MB)'],
    'comments_status' => ['type' => 'bool',    'default' => true,  'description' => '评论状态'],
];
```

字段说明：

| 字段 | 类型 | 说明 |
|------|------|------|
| `type` | string | 数据类型：`string` / `int` / `bool` / `json` |
| `default` | mixed | 默认值（敏感字段为空字符串） |
| `description` | string | 字段描述（可选） |

### 3.2 Schema 注册机制

ConfigService 的 schema 通过 `register()` 接口注入，不通过文件扫描。

**两个注册来源**：

| 来源 | 机制 | 时机 |
|------|------|------|
| 应用级配置 | `init()` 扫描 `config/apps/*.php` → 批量 `register()` | 安装时调用一次 |
| 子级能力配置 | 模块提供 API（如 `POST /api/all/storage/install`）→ 调用 `register()` | 安装/加载驱动时调用 |

**注册行为**：
- 存入内存 schema 缓存
- SQL 检查：已有记录跳过，没有的 INSERT（seed）
- 清除 CacheManager(config) 缓存

### 3.3 运行时完全不依赖文件

注册时 schema 已 seed 到 SQL，运行时 ConfigService 只读 SQL + 缓存：

```
Config::get('storage_cos.secret_key')
  .env → 内存缓存 → CacheManager(config) → SQL → null

Config::getGroup('storage_cos')
  CacheManager(config) → SQL → .env 覆盖 → 写入缓存 → return

Config::getSchema('storage_cos')
  内存缓存 → SQL (distinct group) → 缓存 → return
```

---

## 四、目录结构

```
config/
├── apps/                        ← Schema 模板目录（安装时数据源）
│   ├── core.php                 ← 站点信息
│   ├── cards.php                ← 卡片配置
│   ├── comments.php             ← 评论配置
│   ├── user.php                 ← 用户配置
│   ├── upload.php               ← 上传配置
│   ├── geetest.php              ← 极验配置
│   ├── version.php              ← 版本信息
│   └── roles.php                ← 框架配置（不归 ConfigService 管）
│
├── jwt.php                      ← 框架配置（不动）
├── database.php                 ← 框架配置（不动）
├── cache.php                    ← 框架配置（不动）
├── ...

app/api/service/
├── Config.php                   ← ConfigService（配置系统核心）
└── Storage/                     ← 存储模块（通过 API 注册配置）
    └── ...

app/api/controller/
├── Config.php                   ← 配置管理 API 出口
└── Storage.php                  ← 存储管理 API 出口（meta/install/types）

app/api/route/
└── system.php                   ← 配置 + 存储路由

app/common/cache/
└── CacheManager.php             ← 统一缓存管理（config 域）
```

**注意**：`config/apps/storage/` 目录已删除。Driver 配置通过 API 注册，不依赖外部文件。

---

## 五、ConfigService 接口

### 5.1 注册接口

```php
// 扫描 config/apps/*.php 并批量注册（安装时调用一次）
$result = Config::init();

// 注册单个 group 的 schema + seed SQL
$result = Config::register('storage_cos', [
    'secret_id'  => ['type' => 'string', 'default' => '', 'description' => 'SecretId'],
    'secret_key' => ['type' => 'string', 'default' => '', 'description' => 'SecretKey'],
    'bucket'     => ['type' => 'string', 'default' => '', 'description' => 'Bucket'],
    // ...
]);
// 返回: ['group' => 'storage_cos', 'seeded' => [...], 'skipped' => [...]]
```

### 5.2 读取接口

```php
// 读取单个配置值
$value = Config::get('cards.approve', false);

// 读取分组配置（返回 key => value 平铺数组）
$config = Config::getGroup('storage_cos');
// 返回: ['secret_id' => '...', 'secret_key' => '...', ...]

// 获取分组 schema（含 type/default/description）
$schema = Config::getSchema('storage_cos');
// 返回: ['secret_id' => ['type' => 'string', 'default' => '', 'description' => '...'], ...]

// 列出所有已注册的 group
$groups = Config::getSchemaGroups();
// 返回: ['core', 'cards', 'comments', 'storage', 'storage_cos', ...]
```

### 5.3 写入接口

```php
// 设置单个配置值
Config::set('cards.approve', true);

// 批量设置配置
Config::setGroup('cards', [
    'approve' => true,
    'picture_limit' => 20,
]);
```

### 5.4 管理接口

```php
// 重载配置缓存（清内存 + CacheManager）
Config::reload();              // 重载所有
Config::reload('storage_cos'); // 重载单个 group
```

---

## 六、API 端点

### 6.1 配置管理

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/api/all/config` | 读取配置（支持 ?group=cards 参数） |
| POST | `/api/all/config` | 保存配置 |
| GET | `/api/all/config/groups` | 列出所有已注册 group |
| POST | `/api/all/config/init` | 初始化配置系统（扫描 config/apps/ → register → seed） |
| POST | `/api/all/config/register` | 注册单个 group schema |
| POST | `/api/all/config/reload` | 重载配置缓存 |

### 6.2 存储管理

| 方法 | 路径 | 说明 |
|------|------|------|
| GET | `/api/all/storage/types` | 列出所有 Driver 类型 |
| GET | `/api/all/storage/{type}/meta` | 读取 Driver meta（转换为 schema 格式） |
| POST | `/api/all/storage/install` | 扫描所有 Driver → 注册配置 + seed SQL |

### 6.3 /api/config/register 请求格式

```json
POST /api/all/config/register
{
    "group": "storage_cos",
    "schema": {
        "secret_id":  {"type": "string", "default": "", "description": "SecretId"},
        "secret_key": {"type": "string", "default": "", "description": "SecretKey"},
        "bucket":     {"type": "string", "default": "", "description": "Bucket"},
        "region":     {"type": "string", "default": "ap-guangzhou", "description": "Region"}
    }
}
```

---

## 七、数据库设计

### 7.1 configs 表结构

```sql
CREATE TABLE `configs` (
  `id`          int(11) NOT NULL AUTO_INCREMENT,
  `group`       varchar(50) NOT NULL COMMENT '分组',
  `key`         varchar(100) NOT NULL COMMENT '配置键',
  `value`       text COMMENT '配置值',
  `type`        varchar(20) DEFAULT 'string' COMMENT '类型: string/bool/int/json',
  `description` varchar(255) DEFAULT NULL COMMENT '配置说明',
  `created_at`  datetime NOT NULL,
  `updated_at`  datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_key` (`group`, `key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 7.2 当前数据分组

| group | 来源 | 说明 |
|-------|------|------|
| `core` | config/apps/core.php → init() | 站点信息 |
| `cards` | config/apps/cards.php → init() | 卡片配置 |
| `comments` | config/apps/comments.php → init() | 评论配置 |
| `user` | config/apps/user.php → init() | 用户配置 |
| `upload` | config/apps/upload.php → init() | 上传配置 |
| `geetest` | config/apps/geetest.php → init() | 极验配置 |
| `version` | config/apps/version.php → init() | 版本信息 |
| `storage` | Storage::install() API | 存储全局设置 |
| `storage_local` | Storage::install() API | 本地存储配置 |
| `storage_oss` | Storage::install() API | 阿里云 OSS 配置 |
| `storage_cos` | Storage::install() API | 腾讯云 COS 配置 |
| `storage_qiniu` | Storage::install() API | 七牛云配置 |
| `mail` | (历史数据) | 邮件配置 |

---

## 八、CacheManager 集成

### 8.1 config 域

```php
// CacheManager.php
const DOMAIN_TAGS = [
    'rbac'     => 'rbac',
    'jwt'      => 'jwt',
    'captcha'  => 'captcha',
    'email'    => 'email',
    'system'   => 'system',
    'storage'  => 'storage',
    'config'   => 'config',    // 配置模块
];

const TTL_DAY = 86400;  // 24h
```

### 8.2 缓存策略

| 操作 | 缓存行为 |
|------|---------|
| `getGroup()` | 结果缓存到 CacheManager(config)，key = `group:{group}`，TTL 24h |
| `set()` / `setGroup()` | `CacheManager::clearDomain('config')` |
| `reload()` | 清内存 `$cache` + `$schema` + `CacheManager::clearDomain('config')` |

---

## 九、配置模板列表

### 9.1 core 组（站点信息）

| key | type | default | description |
|-----|------|---------|-------------|
| url | string | '' | 站点URL |
| name | string | 'LoveCards' | 站点名称 |
| title | string | 'LoveCards' | 站点标题 |
| icp_id | string | '' | ICP备案号 |
| keywords | string | '' | SEO关键词 |
| description | string | '' | SEO描述 |
| copyright | string | '' | 版权信息 |
| footer | string | '' | 页脚信息 |
| theme_directory | string | 'index' | 主题目录 |
| visitor_mode | bool | true | 访客模式 |

### 9.2 cards 组（卡片配置）

| key | type | default | description |
|-----|------|---------|-------------|
| approve | bool | false | 卡片发布审核 |
| picture_limit | int | 15 | 图片数量限制 |
| tag_limit | int | 3 | 标签数量限制 |
| image_size | int | 3 | 图片大小限制(MB) |
| comments_status | bool | true | 评论状态 |

### 9.3 comments 组（评论配置）

| key | type | default | description |
|-----|------|---------|-------------|
| approve | bool | false | 评论发布审核 |
| picture_limit | int | 9 | 评论图片数量限制 |

### 9.4 user 组（用户配置）

| key | type | default | description |
|-----|------|---------|-------------|
| captcha | bool | false | 登录验证码 |

### 9.5 upload 组（上传配置）

| key | type | default | description |
|-----|------|---------|-------------|
| user_image_size | int | 2 | 用户图片大小限制(MB) |
| user_image_ext | string | 'jpg,png,gif,webp,jpeg' | 允许的图片扩展名 |

### 9.6 geetest 组（极验配置）

| key | type | default | description |
|-----|------|---------|-------------|
| status | bool | false | 极验验证码状态 |
| id | string | '' | 极验验证码ID |
| key | string | '' | 极验验证码密钥 |

### 9.7 version 组（版本信息）

| key | type | default | description |
|-----|------|---------|-------------|
| name | string | 'LoveCards' | 系统名称 |
| url | string | '//lovecards.cn' | 官网地址 |
| vers | string | '2.4.1' | 版本号 |
| ver | string | '21' | 版本序号 |
| github_url | string | '//github.com/LoveCards/LoveCardsV2' | GitHub地址 |
| qgroup_url | string | '//jq.qq.com/?_wv=1027&k=qM8f2RMg' | QQ群地址 |
| install_environment | json | {...} | 安装环境要求 |

### 9.8 storage 组（存储全局设置，通过 API 注册）

| key | type | default | description |
|-----|------|---------|-------------|
| default | string | 'local' | 默认存储渠道 |
| rate_limit_max | int | 10 | 限流次数 |
| rate_limit_window | int | 60 | 限流窗口(秒) |
| direct_upload_expire | int | 3600 | 直传过期时间(秒) |

### 9.9 storage_{type} 组（Driver 配置，通过 API 注册）

由各 Driver 的 `meta()['fields']` 转换而来，通过 `POST /api/all/storage/install` 注册。

---

## 十、安装流程

### 10.1 应用级配置初始化

```
POST /api/all/config/init
  ↓
扫描 config/apps/*.php（排除 roles.php）
  ↓
逐个 register($group, $schema)
  ↓
每个 group：schema → INSERT SQL（已有记录跳过）
  ↓
返回结果
```

### 10.2 Storage Driver 配置注册

```
POST /api/all/storage/install
  ↓
StorageFactory::getRegisteredTypes() 扫描 Driver
  ↓
每个 Driver：meta() → 转换 schema → register('storage_' . type, $schema)
  ↓
注册全局 storage 设置
  ↓
返回结果
```

### 10.3 前端桥接流程

```
前端"加载驱动"按钮：
  ① GET /api/all/storage/{type}/meta → 拿到 schema
  ② POST /api/all/config/register {group, schema} → 注册
  ③ 展示结果
```

---

## 十一、ConfigService 完整实现

```php
<?php

namespace app\api\service;

use think\facade\Db;
use app\common\cache\CacheManager;

class Config
{
    protected static array $cache = [];
    protected static array $schema = [];

    // ═══ 注册接口 ═══

    public static function init(): array
    {
        $results = [];
        $files = glob(config_path() . 'apps/*.php');

        foreach ($files as $file) {
            $group = pathinfo($file, PATHINFO_FILENAME);
            if ($group === 'roles') continue;

            $schema = include $file;
            if (!is_array($schema)) continue;

            $results[$group] = self::register($group, $schema);
        }

        return $results;
    }

    public static function register(string $group, array $schema): array
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
        CacheManager::clearDomain('config');

        return ['group' => $group, 'seeded' => $seeded, 'skipped' => $skipped];
    }

    // ═══ 读取接口 ═══

    public static function get(string $key, $default = null)
    {
        if (strpos($key, '.') === false) {
            $group = $key;
            $name = null;
        } else {
            [$group, $name] = explode('.', $key, 2);
        }

        if ($name !== null) {
            $envValue = self::getEnvValue($group, $name);
            if ($envValue !== null) {
                return self::castValue($envValue, self::getTypeFromSchema($group, $name));
            }
        }

        $cacheKey = $name !== null ? "{$group}.{$name}" : $group;
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        if ($name === null) {
            return self::getGroup($group);
        }

        $row = Db::table('configs')
            ->where('group', $group)
            ->where('key', $name)
            ->find();

        if ($row) {
            $value = self::castValue($row['value'], $row['type']);
            self::$cache[$cacheKey] = $value;
            return $value;
        }

        $schema = self::getSchema($group);
        if (isset($schema[$name])) {
            return self::castValue($schema[$name]['default'] ?? '', $schema[$name]['type'] ?? 'string');
        }

        return $default;
    }

    public static function getGroup(string $group): array
    {
        $cached = CacheManager::get('config', "group:{$group}");
        if ($cached !== null) {
            return $cached;
        }

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

        foreach ($result as $key => $value) {
            $envValue = self::getEnvValue($group, $key);
            if ($envValue !== null) {
                $result[$key] = self::castValue($envValue, self::getTypeFromSchema($group, $key));
            }
        }

        CacheManager::set('config', "group:{$group}", $result, CacheManager::TTL_DAY);

        return $result;
    }

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

    public static function getSchemaGroups(): array
    {
        return Db::table('configs')
            ->distinct(true)
            ->column('group');
    }

    // ═══ 写入接口 ═══

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

    public static function setGroup(string $group, array $config): bool
    {
        foreach ($config as $key => $value) {
            self::set("{$group}.{$key}", $value);
        }
        return true;
    }

    // ═══ 管理接口 ═══

    public static function reload(?string $group = null): void
    {
        if ($group === null) {
            self::$cache = [];
            self::$schema = [];
            CacheManager::clearDomain('config');
        } else {
            unset(self::$cache[$group]);
            unset(self::$cache["group:{$group}"]);
            unset(self::$schema[$group]);
            CacheManager::delete("group:{$group}");
        }
    }

    // ═══ 内部方法 ═══

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

    protected static function getTypeFromSchema(string $group, string $name): string
    {
        $schema = self::getSchema($group);
        return $schema[$name]['type'] ?? 'string';
    }

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

    protected static function detectType($value): string
    {
        if (is_bool($value)) return 'bool';
        if (is_int($value)) return 'int';
        if (is_array($value)) return 'json';
        return 'string';
    }
}
```

---
