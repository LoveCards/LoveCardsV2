# Storage 模块设计文档

> 本文档基于实际代码实现生成，描述 Storage 模块的完整架构与使用方法。
> 最后更新：2026-05-18

---

## 一、模块概述

Storage 是系统级文件存储抽象层，为整个系统提供统一的文件上传、删除、查询和权限控制能力。

### 核心职责

1. 封装多渠道文件存储（本地、云存储）
2. 统一代理上传和客户端直传两种模式
3. 支持插件化驱动扩展（新驱动自动识别加载）
4. 文件元数据管理（files 表）
5. 权限控制（RBAC + 所有权）
6. 软删除/硬删除机制
7. 统一批量操作接口（batchOperate）

### 设计原则

- **抽象层**：Storage 是底层存储抽象层，统一上游接口能力，调用方不关心后端是对象存储还是本地存储
- **高内聚**：一个渠道一个文件，所有能力（代理上传、直传、删除、URL）内聚在一个 Driver 中
- **能力声明**：驱动通过实现可选接口（`HasDirectUpload`、`HasPresignedUrl`）声明能力，管理层通过 `instanceof` 检测
- **插件化**：新驱动只需实现接口 + 放进目录，StorageFactory 自动扫描识别
- **约定式同步**：配置与驱动通过 `type` 命名约定自动关联（`type` → `storage_{type}` → 配置文件）
- **前端动态化**：admin 配置页从 API 动态获取渠道元数据和字段定义，零硬编码

---

## 二、目录结构

```
app/api/service/Storage/
├── Contract/
│   ├── DriverInterface.php           # 驱动核心接口
│   ├── AbstractDriver.php            # 驱动抽象基类（通用校验）
│   ├── HasDirectUpload.php           # 可选能力：直传凭证生成
│   ├── HasPresignedUrl.php           # 可选能力：预签名 URL
│   ├── DirectUploadCredential.php    # 直传凭证值对象
│   └── StorageResult.php             # 代理上传结果值对象
├── Driver/
│   ├── LocalDriver.php               # 本地存储 (type=local)
│   ├── OssDriver.php                 # 阿里云 OSS (type=oss)
│   ├── CosDriver.php                 # 腾讯云 COS (type=cos)
│   └── QiniuDriver.php               # 七牛云 (type=qiniu)
├── ChannelManager.php                # 渠道配置管理
├── StorageFactory.php                # 驱动工厂（自动扫描）
├── StorageManager.php                # 代理上传入口
├── DirectUploadManager.php           # 直传入口
└── PathGenerator.php                 # 统一路径生成

app/api/model/
└── Files.php                         # 文件模型（SoftDelete）

config/apps/storage/
├── settings.php                      # 全局设置
├── local.php                         # 本地存储默认配置
├── oss.php                           # OSS 默认配置
├── cos.php                           # COS 默认配置
└── qiniu.php                         # 七牛默认配置
```

---

## 三、接口设计

### 3.1 核心接口（DriverInterface）

所有驱动必须实现：

```php
interface DriverInterface
{
    public function getType(): string;           // 'local' / 'oss' / 'cos' / 'qiniu'
    public function upload(UploadedFile $file, string $path): StorageResult;
    public function delete(string $driverPath): bool;
    public function getUrl(string $driverPath): string;
    public static function meta(): array;        // 元数据（名称、图标、字段定义）
}
```

### 3.2 可选能力接口

驱动通过实现可选接口声明能力，管理层通过 `instanceof` 检测：

```php
// 直传凭证生成（客户端拿到凭证后自行上传到云端）
interface HasDirectUpload
{
    public function getDirectUploadUrl(): string;
    public function getUploadCredential(
        string $filename, string $mime, int $size, string $path, int $expire = 3600
    ): DirectUploadCredential;
}

// 预签名 URL（私有文件的临时访问链接）
interface HasPresignedUrl
{
    public function getPresignedUrl(string $driverPath, int $expire = 3600): string;
}
```

### 3.3 能力检测方式

```php
$driver = StorageFactory::make($slug);

if ($driver instanceof HasDirectUpload) {
    $credential = $driver->getUploadCredential(...);
} else {
    throw new ApiException('该渠道不支持直传');
}
```

### 3.4 值对象

```php
// 直传凭证
class DirectUploadCredential
{
    public function __construct(
        public readonly string $url,       // 上传目标 URL
        public readonly string $method,    // 'PUT' 或 'POST'
        public readonly array $headers,    // 需要的请求头
        public readonly array $formData,   // 表单字段
        public readonly int $expire,
    ) {}
}

// 代理上传结果
class StorageResult
{
    public int $id;
    public string $url;
    public string $path;
    public string $driverPath;
    public int $size;
    public string $mimeType;
    public string $originalName;
    public string $channelSlug;
}
```

---

## 四、架构分层

```
┌──────────────────────────────────────────────────────────────────────────────┐
│                           Controller（Upload.php）                            │
│                     调用方，不关心后端存储细节，只调统一接口                    │
└────────────────────────────────┬─────────────────────────────────────────────┘
                                 │
          ┌──────────────────────┼──────────────────────┐
          ▼                                             ▼
 ┌────────────────────────┐                 ┌────────────────────────────────┐
 │  StorageManager        │                 │  DirectUploadManager           │
 │  服务端代理上传         │                 │  客户端直传                     │
 └───────────┬────────────┘                 └───────────┬────────────────────┘
             │                                          │
             ▼                                          ▼
 ┌────────────────────────┐                 ┌────────────────────────────────┐
 │  PathGenerator         │  ← 共享 ──→    │  instanceof HasDirectUpload?   │
 │  统一路径生成           │                 │  → getUploadCredential()       │
 └───────────┬────────────┘                 └───────────┬────────────────────┘
             │                                          │
             ▼                                          │
 ┌────────────────────────┐                             │
 │  ChannelManager        │  ← 共享配置 ────────────────┘
 │  渠道配置管理           │
 │  配置来源：             │
 │  文件默认 → DB → .env  │
 └───────────┬────────────┘
             │
             ▼
 ┌────────────────────────┐
 │  StorageFactory        │
 │  自动扫描 Driver/ 目录 │
 │  make($slug) → Driver  │
 └───────────┬────────────┘
             │
             ▼
 ┌────────────────────────────────────────────────────────────────────────────┐
 │                     DriverInterface（核心接口）                             │
 │   upload() / delete() / getUrl() / meta()                                 │
 │   + 可选：HasDirectUpload / HasPresignedUrl                                │
 └────────────────────────────────┬───────────────────────────────────────────┘
                                  │
              ┌───────────────────┼───────────────────┐
              ▼                   ▼                   ▼
    ┌───────────────┐   ┌───────────────┐   ┌───────────────┐
    │ LocalDriver    │   │  OssDriver    │   │  CosDriver    │
    │ 仅代理上传     │   │ 代理 + 直传   │   │ 代理 + 直传   │
    └───────────────┘   └───────────────┘   └───────────────┘
                                                    │
                                                    ▼
                                          ┌───────────────┐
                                          │  QiniuDriver   │
                                          │ 代理 + 直传    │
                                          └───────────────┘
```

---

## 五、配置说明

### 5.1 配置体系

渠道配置使用三层优先级：**文件默认值 → 数据库覆盖 → .env 最高优先**

| 层 | 来源 | 说明 |
|----|------|------|
| Layer 1 | `config/apps/storage/{type}.php` | 文件默认值，随代码部署 |
| Layer 2 | `configs` 表（group=`storage_{type}`） | admin 后台编辑，通用 key-value |
| Layer 3 | `.env` | 环境变量，最高优先级 |

**约定式同步**：Driver 的 `getType()` 返回值 = 配置 group 后缀 = 配置文件名。

例：`CosDriver::getType()` → `'cos'` → `storage_cos` → `config/apps/storage/cos.php`

### 5.2 渠道配置字段

每个渠道的配置文件定义默认值：

```php
// 示例：config/apps/storage/cos.php
return [
    'secret_id' => '',
    'secret_key' => '',
    'bucket' => '',
    'region' => 'ap-guangzhou',
    'cdn_url' => '',
    'allow_mime_types' => 'image/jpeg,image/png,image/gif,image/webp',
    'max_file_size' => 52428800,
    'path_template' => 'storage/{date}/{uuid}.{ext}',
];
```

**通用字段（所有渠道都有）：**

| 字段 | 说明 |
|------|------|
| `allow_mime_types` | 允许的 MIME 类型（逗号分隔），白名单模式 |
| `max_file_size` | 最大文件大小（字节） |
| `path_template` | 路径模板，支持 `{date}`、`{uuid}`、`{ext}` 占位符 |

### 5.3 settings.php - 全局设置

```php
// config/apps/storage/settings.php
return [
    'default' => 'local',          // 默认存储渠道
    'rate_limit_max' => 10,        // 每窗口最大请求数
    'rate_limit_window' => 60,     // 时间窗口（秒）
    'direct_upload_expire' => 3600,// 直传凭证有效期（秒）
];
```

---

## 六、数据库设计

### 6.1 files 表结构

```sql
CREATE TABLE `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_slug` varchar(50) NOT NULL COMMENT '存储渠道标识',
  `user_id` int(11) DEFAULT NULL COMMENT '上传者ID',
  `is_public` tinyint(1) DEFAULT 0 COMMENT '是否公开：0=私有 1=公开',
  `scene` varchar(32) DEFAULT 'direct' COMMENT '业务场景：card/comment/avatar/direct',
  `ref_type` varchar(32) DEFAULT NULL COMMENT '关联类型：card/comment',
  `ref_id` int(11) DEFAULT NULL COMMENT '关联ID',
  `original_name` varchar(255) DEFAULT NULL COMMENT '原始文件名',
  `file_path` varchar(500) NOT NULL COMMENT '存储路径（含扩展名）',
  `file_url` varchar(1000) NOT NULL COMMENT '访问URL',
  `file_size` int(11) NOT NULL COMMENT '文件大小(字节)',
  `file_ext` varchar(20) NOT NULL COMMENT '文件扩展名',
  `mime_type` varchar(100) DEFAULT NULL COMMENT 'MIME类型',
  `driver_path` varchar(500) DEFAULT NULL COMMENT '驱动路径（= file_path，API 响应中隐藏）',
  `metadata` json DEFAULT NULL COMMENT '额外元数据（预留字段）',
  `status` tinyint(1) DEFAULT 0 COMMENT '业务状态: 0=正常 1=封禁',
  `upload_status` tinyint(1) DEFAULT 1 COMMENT '上传状态: 0=上传中 1=已完成 2=失败',
  `expire_at` datetime DEFAULT NULL COMMENT '过期时间(用于直传)',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `channel_slug` (`channel_slug`),
  KEY `driver_path` (`driver_path`),
  KEY `user_id` (`user_id`),
  KEY `scene` (`scene`),
  KEY `ref` (`ref_type`, `ref_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 6.2 业务状态（status 字段）

| 状态值 | 常量 | 说明 |
|--------|------|------|
| 0 | STATUS_NORMAL | 正常 |
| 1 | STATUS_BANNED | 封禁 |

### 6.3 上传状态（upload_status 字段）

| 状态值 | 常量 | 说明 |
|--------|------|------|
| 0 | UPLOAD_PENDING | 上传中（仅直传场景） |
| 1 | UPLOAD_COMPLETED | 上传完成（代理上传始终为此状态） |
| 2 | UPLOAD_FAILED | 上传失败（直传过期/异常） |

---

## 七、API 接口（RESTful）

### 7.1 文件资源（public.php 路由组）

| 方法 | 路由 | 控制器方法 | 说明 |
|------|------|-----------|------|
| `POST` | `/api/storage/files` | `upload()` | 代理上传（创建文件） |
| `GET` | `/api/storage/files` | `files()` | 文件列表（分页/筛选/回收站） |
| `GET` | `/api/storage/files/{id}` | `getFile($id)` | 获取单个文件 |
| `POST` | `/api/storage/files/batch` | `batchOperate()` | 批量操作 |
| `DELETE` | `/api/storage/files/expired` | `cleanup()` | 清理过期记录 |

### 7.2 直传（public.php 路由组）

| 方法 | 路由 | 控制器方法 | 说明 |
|------|------|-----------|------|
| `POST` | `/api/storage/files/direct` | `getDirectUploadCredential()` | 获取直传凭证 |
| `PATCH` | `/api/storage/files/{id}/confirm` | `confirmDirectUpload($id)` | 确认直传完成 |

### 7.3 渠道管理（admin.php 路由组）

| 方法 | 路由 | 控制器方法 | 说明 |
|------|------|-----------|------|
| `GET` | `/api/storage/channels` | `storageChannels()` | 渠道列表（含元数据） |
| `POST` | `/api/storage/channels/{channel}/test` | `testChannel($channel)` | 测试渠道连通性 |
| `GET` | `/api/storage/channels/stats` | `channelStats()` | 渠道文件统计 |

### 7.4 配置管理（admin.php 路由组）

| 方法 | 路由 | 控制器方法 | 说明 |
|------|------|-----------|------|
| `GET` | `/api/system/config` | `admin.Config/index` | 获取配置（支持 group 参数） |
| `POST` | `/api/system/config` | `admin.Config/save` | 保存配置 |

---

## 八、驱动实现

### 8.1 驱动能力矩阵

| 驱动 | type | 代理上传 | 直传 | 删除 |
|------|------|---------|------|------|
| LocalDriver | local | ✅ | ❌ | ✅ |
| OssDriver | oss | ✅ | ✅ | ✅ |
| CosDriver | cos | ✅ | ✅ | ✅ |
| QiniuDriver | qiniu | ✅ | ✅ | ✅ |

### 8.2 AbstractDriver 基类

所有驱动继承 `Contract/AbstractDriver`（位于 Contract 目录，不在 Driver 目录中，避免被 StorageFactory 自动扫描误识别），自动获得：

- MIME 类型校验（支持通配符 `image/*`）
- 文件大小校验
- `validateFile()` — 代理上传校验
- `validateDirectUpload()` — 直传校验

### 8.3 驱动元数据（meta）

每个驱动通过 `static meta()` 返回渠道信息（含 `type` 键），供 StorageFactory 注册和 admin 后台动态渲染：

```php
public static function meta(): array
{
    return [
        'type' => 'cos',
        'name' => '腾讯云 COS',
        'icon' => 'mdi-cloud',
        'fields' => [
            ['key' => 'secret_id', 'label' => 'SecretId', 'type' => 'text'],
            ['key' => 'secret_key', 'label' => 'SecretKey', 'type' => 'password'],
            ['key' => 'bucket', 'label' => 'Bucket', 'type' => 'text'],
            ['key' => 'region', 'label' => 'Region', 'type' => 'text'],
            ['key' => 'cdn_url', 'label' => 'CDN域名', 'type' => 'text'],
            ['key' => 'allow_mime_types', 'label' => '允许的MIME类型', 'type' => 'text'],
            ['key' => 'max_file_size', 'label' => '最大文件大小(字节)', 'type' => 'number'],
            ['key' => 'path_template', 'label' => '路径模板', 'type' => 'text'],
        ],
    ];
}
```

---

## 九、直传模式

### 流程

```
客户端                    服务器                       第三方存储
  │                          │                            │
  │  1.请求直传凭证           │                            │
  │─────────────────────────>│                            │
  │                          │  2.创建 PENDING 记录       │
  │                          │  3.生成上传凭证             │
  │  4.返回凭证               │                            │
  │<─────────────────────────│                            │
  │                          │                            │
  │  5.使用凭证直接上传        │                            │
  │─────────────────────────────────────────────────────>│
  │                          │                            │
  │  6.确认上传完成           │                            │
  │─────────────────────────>│                            │
  │                          │  7.driver->getUrl()        │
  │                          │     upload_status=COMPLETED│
```

### 两种直传模式

| 模式 | 说明 | 驱动 |
|------|------|------|
| POST + policy | 服务端生成 policy + signature，客户端 POST 表单 | COS、OSS |
| POST + token | 服务端生成 upload token，客户端 POST 表单 | 七牛 |

---

## 十、新驱动扩展流程

```
1. 创建 Driver/MyCloudDriver.php
   - extends Contract/AbstractDriver
   - 实现 getType() → 'mycloud'
   - 实现 static meta() → 返回 type、name、icon、fields
   - 实现 upload() / delete() / getUrl()
   - 可选：implements HasDirectUpload → 实现 getDirectUploadUrl() + getUploadCredential()

2. 创建 config/apps/storage/mycloud.php
   - 返回默认配置数组

3. 完成。
   - StorageFactory 自动扫描识别
   - ChannelManager 自动加载配置（storage_mycoud）
   - admin 后台自动出现该渠道配置页（API 动态返回）
   - 前端 admin 自动渲染表单（从 API 拿字段定义）
```

---

## 十一、安全设计

| 措施 | 说明 |
|------|------|
| 速率限制 | ThinkPHP Cache 持久化滑动窗口 |
| driver_path 隐藏 | model `$hidden` + StorageResult `toArray()` 不含 driver_path |
| driver_path 服务端推导 | `confirmUpload` 不接受客户端 driver_path |
| 路径遍历防护 | `LocalDriver::delete()` 使用 `realpath()` 校验 |
| MIME 检测 | 使用 `finfo` 读文件魔数，支持通配符 |
| ids 校验 | `batchOperate` 用 `array_map('intval')` + `array_filter` |
| toggle_public 原子操作 | 使用 `Db::raw('1 - is_public')` 避免竞态 |
| 路径统一生成 | PathGenerator 统一收口，模板可配置，UUID v4 |
| status/upload_status 分离 | status 仅控制业务封禁，upload_status 仅控制上传流程 |
| RBAC Route Name 匹配 | 权限表存路由标识（如 `storage.files.show`），不存 URL 路径。通过 `request()->rule()->getName()` 匹配，天然支持动态参数 |

---

## 十二、RBAC 权限系统

### 12.1 设计原则

权限匹配使用**路由标识（Route Name）**而非 URL 路径：

| 维度 | 旧方案（URL 路径） | 新方案（Route Name） |
|------|-------------------|---------------------|
| 存储 | `/api/storage/files/42` | `storage.files.show` |
| 匹配 | 精确 URL 匹配 | 路由名精确匹配 |
| 动态参数 | 不支持 | 天然支持 |
| URL 重构 | 全部作废 | 不受影响 |
| 语义 | 技术细节 | 业务语义 |

### 12.2 路由命名规范

所有路由使用 `.` 分隔的语义化名称：`{模块}.{资源}.{操作}`

| 操作 | 后缀 | HTTP 方法 |
|------|------|----------|
| 列表 | `.index` | GET |
| 详情 | `.show` | GET (带参数) |
| 创建 | `.store` | POST |
| 更新 | `.update` | PATCH/PUT |
| 删除 | `.destroy` | DELETE |
| 批量 | `.batch` | POST |

### 12.3 权限校验流程

```
请求 → JwtAuthCheck 中间件
  → 解析 JWT token，注入 uid/rolesId

请求 → PermissionCheck 中间件
  → request()->rule()->getName() 获取路由名 (如 storage.channels.index)
  → request()->method() 获取 HTTP 方法 (如 GET)
  → RBAC::checkAccess(rolesId, routeName, method)
     - root(1) in rolesId? → true 直接放行
     - hash = md5(routeName:method)
     - 查 role_permissions 单表 (带缓存)
     - in_array(hash, set)? → 放行
     - 不匹配 → 403 Forbidden
```

### 12.4 配置管理路由

| 方法 | 路由 | 路由标识 | 说明 |
|------|------|---------|------|
| `GET` | `/api/system/config` | `system.config.index` | 获取配置 |
| `POST` | `/api/system/config` | `system.config.save` | 保存配置 |
| `GET` | `/api/storage/channels` | `storage.channels.index` | 渠道列表 |
| `GET` | `/api/storage/channels/stats` | `storage.channels.stats` | 渠道统计 |
| `POST` | `/api/storage/channels/{channel}/test` | `storage.channels.test` | 测试连通性 |

---
