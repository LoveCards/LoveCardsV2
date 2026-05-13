# 配置体系设计文档

> 本文档描述 LoveCards 后端配置体系的完整设计方案。

---

## 一、设计目标

1. **统一存储**：应用级配置全部迁移到数据库（configs 表）
2. **统一接口**：ConfigService 提供统一的 API 读写所有配置
3. **优先级机制**：`.env` > 数据库 > 模板默认值
4. **模块化**：`config/apps/` 目录下按业务拆分模板文件
5. **向后兼容**：升级时自动新增配置项，不丢失已有配置

---

## 二、配置分层

```
┌─────────────────────────────────────────────────────────────┐
│  第一层：环境变量 (.env)                                      │
│  存储位置：.env 文件                                          │
│  用途：敏感信息，不进版本控制                                  │
│  内容：数据库密码、API 密钥等                                  │
│  优先级：最高，可覆盖一切                                      │
├─────────────────────────────────────────────────────────────┤
│  第二层：业务配置 (数据库 configs 表)                          │
│  存储位置：数据库                                              │
│  用途：业务逻辑配置，可后台修改                                │
│  内容：站点信息、功能开关、业务参数等                           │
├─────────────────────────────────────────────────────────────┤
│  第三层：配置模板 (config/apps/*.php)                         │
│  存储位置：文件                                                │
│  用途：配置默认值，升级时自动新增配置项                         │
│  内容：所有配置项的默认值                                      │
├─────────────────────────────────────────────────────────────┤
│  第四层：框架配置 (config/*.php)                              │
│  存储位置：文件                                                │
│  用途：框架运行必需配置，很少变动                              │
│  内容：数据库连接、缓存驱动、中间件等                           │
└─────────────────────────────────────────────────────────────┘
```

---

## 三、目录结构

```
config/
├── apps/                        ← 配置模板目录
│   ├── site.php                 ← 站点配置模板
│   ├── app.php                  ← 应用配置模板
│   ├── upload.php               ← 上传配置模板
│   ├── cards.php                ← 卡片配置模板
│   ├── comments.php             ← 评论配置模板
│   ├── user.php                 ← 用户配置模板
│   └── geetest.php              ← 极验配置模板
│
├── app.php                      ← 框架配置（不动）
├── cache.php                    ← 框架配置（不动）
├── database.php                 ← 框架配置（不动）
├── filesystem.php               ← 框架配置（不动）
├── middleware.php                ← 框架配置（不动）
├── route.php                    ← 框架配置（不动）
├── session.php                  ← 框架配置（不动）
├── view.php                     ← 框架配置（不动）
│
├── core/                        ← Storage 模块配置
│   └── storage/
│       ├── channels.php
│       └── settings.php
│
└── rsa/                         ← JWT 密钥
    ├── private.pem
    └── public.pem

app/api/service/
└── Config.php                   ← ConfigService 服务
```

---

## 四、配置分组

### 4.1 分组列表

| 分组 | 文件 | 内容 |
|------|------|------|
| `site` | `config/apps/site.php` | 站点信息（域名、名称、标题...） |
| `app` | `config/apps/app.php` | 应用配置（主题、访客模式） |
| `upload` | `config/apps/upload.php` | 上传配置（图片大小、扩展名） |
| `cards` | `config/apps/cards.php` | 卡片配置（审核、数量限制） |
| `comments` | `config/apps/comments.php` | 评论配置（审核、数量限制） |
| `user` | `config/apps/user.php` | 用户配置（验证码） |
| `geetest` | `config/apps/geetest.php` | 极验配置（开关、ID、Key） |

### 4.2 配置项详细列表

#### site 组（站点信息）

| key | type | 默认值 | 说明 |
|-----|------|--------|------|
| url | string | '' | 站点域名 |
| name | string | 'LoveCards' | 站点名称 |
| title | string | 'LoveCards' | 站点标题 |
| icp_id | string | '' | ICP备案号 |
| keywords | string | '' | 关键词 |
| description | string | '' | 站点描述 |
| copyright | string | '' | 版权信息 |
| footer | string | '' | 页脚信息 |

#### app 组（应用配置）

| key | type | 默认值 | 说明 |
|-----|------|--------|------|
| theme_directory | string | 'index' | 主题目录 |
| visitor_mode | bool | true | 访客模式 |

#### upload 组（上传配置）

| key | type | 默认值 | 说明 |
|-----|------|--------|------|
| user_image_size | int | 2 | 用户图片大小(MB) |
| user_image_ext | string | 'jpg,png,gif,webp,jpeg' | 允许的图片扩展名 |

#### cards 组（卡片配置）

| key | type | 默认值 | 说明 |
|-----|------|--------|------|
| approve | bool | false | 卡片审核开关 |
| picture_limit | int | 15 | 卡片图片限制 |
| tag_limit | int | 3 | 卡片标签限制 |
| image_size | int | 3 | 卡片图片大小(MB) |
| comments_status | bool | true | 卡片评论开关 |

#### comments 组（评论配置）

| key | type | 默认值 | 说明 |
|-----|------|--------|------|
| approve | bool | false | 评论审核开关 |
| picture_limit | int | 9 | 评论图片限制 |

#### user 组（用户配置）

| key | type | 默认值 | 说明 |
|-----|------|--------|------|
| captcha | bool | false | 验证码开关 |

#### geetest 组（极验配置）

| key | type | 默认值 | 说明 |
|-----|------|--------|------|
| status | bool | false | 极验开关 |
| id | string | '' | 极验ID |
| key | string | '' | 极验Key |

---

## 五、配置加载流程

```
Config::get('cards.approve')
         │
         ▼
┌─────────────────┐
│ 检查 .env       │  env('CARDS.APPROVE')
│ CARDS.APPROVE   │
└────────┬────────┘
         │ 存在？
    ┌────┴────┐
    │ 是      │ 否
    ▼         ▼
  返回值    ┌─────────────────┐
            │ 查询数据库      │
            │ configs 表      │
            │ group=cards     │
            │ key=approve     │
            └────────┬────────┘
                     │ 存在？
                ┌────┴────┐
                │ 是      │ 否
                ▼         ▼
              返回值    ┌─────────────────┐
                        │ 读取模板        │
                        │ config/apps/    │
                        │ cards.php       │
                        └────────┬────────┘
                                 │ 存在？
                            ┌────┴────┐
                            │ 是      │ 否
                            ▼         ▼
                          返回值    返回默认值
```

---

## 六、数据库设计

### 6.1 configs 表结构

```sql
CREATE TABLE `configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group` varchar(50) NOT NULL COMMENT '分组：site/app/upload/cards/comments/user/geetest',
  `key` varchar(100) NOT NULL COMMENT '配置键',
  `value` text COMMENT '配置值',
  `type` varchar(20) DEFAULT 'string' COMMENT '类型：string/bool/int/json',
  `description` varchar(255) DEFAULT NULL COMMENT '配置说明',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_key` (`group`, `key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 6.2 初始化数据

```sql
-- 迁移 system 表数据到 site 组
INSERT INTO configs (`group`, `key`, `value`, `type`, `description`, `created_at`, `updated_at`)
SELECT 
    'site',
    CASE name
        WHEN 'siteUrl' THEN 'url'
        WHEN 'siteName' THEN 'name'
        WHEN 'siteTitle' THEN 'title'
        WHEN 'siteICPId' THEN 'icp_id'
        WHEN 'siteKeywords' THEN 'keywords'
        WHEN 'siteDes' THEN 'description'
        WHEN 'siteCopyright' THEN 'copyright'
        WHEN 'siteFooter' THEN 'footer'
        ELSE name
    END,
    value,
    'string',
    CASE name
        WHEN 'siteUrl' THEN '站点域名'
        WHEN 'siteName' THEN '站点名称'
        WHEN 'siteTitle' THEN '站点标题'
        WHEN 'siteICPId' THEN 'ICP备案号'
        WHEN 'siteKeywords' THEN '关键词'
        WHEN 'siteDes' THEN '站点描述'
        WHEN 'siteCopyright' THEN '版权信息'
        WHEN 'siteFooter' THEN '页脚信息'
        ELSE name
    END,
    NOW(),
    NOW()
FROM system;

-- 初始化业务配置
INSERT INTO configs (`group`, `key`, `value`, `type`, `description`, `created_at`, `updated_at`) VALUES
-- app 组
('app', 'theme_directory', 'index', 'string', '主题目录', NOW(), NOW()),
('app', 'visitor_mode', '1', 'bool', '访客模式', NOW(), NOW()),

-- upload 组
('upload', 'user_image_size', '2', 'int', '用户图片大小(MB)', NOW(), NOW()),
('upload', 'user_image_ext', 'jpg,png,gif,webp,jpeg', 'string', '允许的图片扩展名', NOW(), NOW()),

-- cards 组
('cards', 'approve', '0', 'bool', '卡片审核开关', NOW(), NOW()),
('cards', 'picture_limit', '15', 'int', '卡片图片限制', NOW(), NOW()),
('cards', 'tag_limit', '3', 'int', '卡片标签限制', NOW(), NOW()),
('cards', 'image_size', '3', 'int', '卡片图片大小(MB)', NOW(), NOW()),
('cards', 'comments_status', '1', 'bool', '卡片评论开关', NOW(), NOW()),

-- comments 组
('comments', 'approve', '0', 'bool', '评论审核开关', NOW(), NOW()),
('comments', 'picture_limit', '9', 'int', '评论图片限制', NOW(), NOW()),

-- user 组
('user', 'captcha', '0', 'bool', '验证码开关', NOW(), NOW()),

-- geetest 组
('geetest', 'status', '0', 'bool', '极验开关', NOW(), NOW()),
('geetest', 'id', '', 'string', '极验ID', NOW(), NOW()),
('geetest', 'key', '', 'string', '极验Key', NOW(), NOW());
```

---

## 七、ConfigService 接口

### 7.1 读取配置

```php
// 读取单个配置
$value = Config::get('cards.approve', false);

// 读取分组配置
$config = Config::getGroup('cards');
// 返回: ['approve' => false, 'picture_limit' => 15, ...]
```

### 7.2 写入配置

```php
// 设置单个配置
Config::set('cards.approve', true);

// 批量设置配置
Config::setGroup('cards', [
    'approve' => true,
    'picture_limit' => 20,
]);
```

### 7.3 优先级机制

```php
// .env 覆盖数据库
// 如果 .env 中有 CARDS.APPROVE=1，则返回 true
// 否则返回数据库中的值
// 否则返回模板默认值
$value = Config::get('cards.approve');
```

---

## 八、代码改动清单

### 8.1 新建文件

| 文件 | 说明 |
|------|------|
| `app/api/service/Config.php` | ConfigService 服务 |
| `config/apps/site.php` | 站点配置模板 |
| `config/apps/app.php` | 应用配置模板 |
| `config/apps/upload.php` | 上传配置模板 |
| `config/apps/cards.php` | 卡片配置模板 |
| `config/apps/comments.php` | 评论配置模板 |
| `config/apps/user.php` | 用户配置模板 |
| `config/apps/geetest.php` | 极验配置模板 |

### 8.2 修改文件

| 文件 | 改动说明 |
|------|----------|
| `app/api/controller/admin/System.php` | 更新配置读写方法 |
| `app/api/controller/user/Auth.php` | 更新配置读取 |
| `app/api/controller/user/Cards.php` | 更新配置读取 |
| `app/api/controller/admin/Cards.php` | 更新配置读取 |
| `app/api/validate/Upload.php` | 更新配置读取 |
| `app/api/validate/Cards.php` | 更新配置读取 |
| `app/api/middleware/JwtAuthCheck.php` | 更新配置读取 |
| `app/common/Theme.php` | 更新配置读取 |
| `app/index/common/Theme.php` | 更新配置读取 |
| `app/index/common/Common.php` | 更新配置读取 |
| `app/api/controller/public/Theme.php` | 更新配置读取 |

### 8.3 配置读取映射

| 旧代码 | 新代码 |
|--------|--------|
| `Config::get('master.Cards.Approve')` | `Config::get('cards.approve')` |
| `Config::get('master.Cards.PictureLimit')` | `Config::get('cards.picture_limit')` |
| `Config::get('master.Cards.TagLimit')` | `Config::get('cards.tag_limit')` |
| `Config::get('master.Cards.ImageSize')` | `Config::get('cards.image_size')` |
| `Config::get('master.Cards.CommentsStatus')` | `Config::get('cards.comments_status')` |
| `Config::get('master.Comments.Approve')` | `Config::get('comments.approve')` |
| `Config::get('master.Comments.PictureLimit')` | `Config::get('comments.picture_limit')` |
| `Config::get('master.System.ThemeDirectory')` | `Config::get('app.theme_directory')` |
| `Config::get('master.System.VisitorMode')` | `Config::get('app.visitor_mode')` |
| `Config::get('master.Upload.UserImageSize')` | `Config::get('upload.user_image_size')` |
| `Config::get('master.Upload.UserImageExt')` | `Config::get('upload.user_image_ext')` |
| `Config::get('master.UserAuth.Captcha')` | `Config::get('user.captcha')` |
| `Db::table('system')->where('name', 'siteUrl')->value('value')` | `Config::get('site.url')` |
| `Db::table('system')->where('name', 'siteName')->value('value')` | `Config::get('site.name')` |

---

## 九、ConfigService 实现

```php
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
        [$group, $name] = explode('.', $key, 2);
        
        // 1. 检查 .env（最高优先级）
        $envKey = strtoupper("{$group}.{$name}");
        $envValue = env($envKey);
        if ($envValue !== null && $envValue !== '') {
            return self::castValue($envValue, self::getType($group, $name));
        }
        
        // 2. 检查缓存
        $cacheKey = "{$group}.{$name}";
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }
        
        // 3. 从数据库读取
        $row = Db::table('configs')
            ->where('group', $group)
            ->where('key', $name)
            ->find();
        
        if ($row) {
            $value = self::castValue($row['value'], $row['type']);
            self::$cache[$cacheKey] = $value;
            return $value;
        }
        
        // 4. 从模板读取默认值
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
            $envKey = strtoupper("{$group}.{$key}");
            $envValue = env($envKey);
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
     */
    protected static function getDefaults(string $group): array
    {
        if (isset(self::$defaults[$group])) {
            return self::$defaults[$group];
        }
        
        $file = config_path() . "apps/{$group}.php";
        if (file_exists($file)) {
            self::$defaults[$group] = include $file;
        } else {
            self::$defaults[$group] = [];
        }
        
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
```

---

## 十、配置模板示例

### 10.1 config/apps/site.php

```php
<?php
return [
    'url' => '',
    'name' => 'LoveCards',
    'title' => 'LoveCards',
    'icp_id' => '',
    'keywords' => '',
    'description' => '',
    'copyright' => '',
    'footer' => '',
];
```

### 10.2 config/apps/app.php

```php
<?php
return [
    'theme_directory' => 'index',
    'visitor_mode' => true,
];
```

### 10.3 config/apps/cards.php

```php
<?php
return [
    'approve' => false,
    'picture_limit' => 15,
    'tag_limit' => 3,
    'image_size' => 3,
    'comments_status' => true,
];
```

### 10.4 config/apps/comments.php

```php
<?php
return [
    'approve' => false,
    'picture_limit' => 9,
];
```

### 10.5 config/apps/upload.php

```php
<?php
return [
    'user_image_size' => 2,
    'user_image_ext' => 'jpg,png,gif,webp,jpeg',
];
```

### 10.6 config/apps/user.php

```php
<?php
return [
    'captcha' => false,
];
```

### 10.7 config/apps/geetest.php

```php
<?php
return [
    'status' => false,
    'id' => '',
    'key' => '',
];
```

---

## 十一、实施步骤

| 步骤 | 内容 | 执行方式 |
|------|------|----------|
| 1 | 创建 configs 表 | 手动 SQL |
| 2 | 初始化数据 | 手动 SQL |
| 3 | 创建 ConfigService | 代码修改 |
| 4 | 创建 config/apps/*.php 模板 | 代码修改 |
| 5 | 更新 System 控制器 | 代码修改 |
| 6 | 更新业务代码（21 处） | 代码修改 |
| 7 | 测试配置读写功能 | 测试验证 |
| 8 | 清理旧配置文件（可选） | 后续处理 |

---

## 十二、升级机制

### 新版本新增配置项

1. 新版本更新 `config/apps/*.php` 模板（新增配置项）
2. 用户升级代码文件
3. 运行时自动读取模板默认值（数据库没有的配置项使用模板默认值）
4. 无需手动执行 SQL

### 配置项迁移

1. 旧配置项自动迁移到新表（一次性）
2. 旧表保留作为备份（可选）
3. 旧代码逐步替换为新接口

---

## 十三、与旧系统对比

| 项目 | 旧系统 | 新系统 |
|------|--------|--------|
| 存储方式 | 文件 + 数据库混合 | 统一数据库 |
| 读取接口 | `Config::get()` + `Db::table()` | `Config::get()` |
| 写入接口 | `ConfigHelper::save()` + `Db::table()` | `Config::set()` |
| 缓存机制 | 无 | 内存缓存 |
| 配置分组 | 不统一 | 统一分组 |
| 后台管理 | 部分支持 | 全部支持 |
| 升级机制 | 手动迁移 | 自动读取模板 |

---

## 十四、版本历史

| 版本 | 日期 | 说明 |
|------|------|------|
| 1.0 | 2026-05-13 | 初始版本，配置体系设计 |
