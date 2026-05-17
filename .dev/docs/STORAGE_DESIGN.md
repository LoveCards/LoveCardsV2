# Storage 模块设计文档

> 本文档基于实际代码实现生成，描述 Storage 模块的完整架构与使用方法。
> 最后更新：2026-05-17

---

## 一、模块概述

Storage 是系统级文件存储模块，为整个系统提供统一的文件上传、删除、查询和权限控制能力。

### 核心职责

1. 封装多渠道文件存储（本地、云存储、图床 API）
2. 支持存储渠道热切换（通过配置文件）
3. 提供直链上传能力（客户端直传第三方存储）
4. 文件元数据管理（files 表）
5. 权限控制（RBAC + 所有权）
6. 软删除/硬删除机制
7. 统一批量操作接口（batchOperate）

### 设计原则

- **抽象层**：Storage 是底层存储抽象层，统一上游接口能力，调用方不关心后端是对象存储、图床还是本地存储
- **配置驱动**：渠道配置通过 admin 后台管理，存储在 `configs` 表（通用 key-value），每个渠道可独立配置
- **路径统一**：通过 `PathGenerator` 统一生成存储路径，模板可在渠道配置中自定义
- **驱动可扩展**：新增存储渠道只需实现驱动，无需修改上层代码
- **接口统一**：StorageManager 提供统一的 API 入口
- **记录追踪**：所有文件操作都有数据库记录
- **权限分离**：用户只能操作自己的文件，管理员可操作所有文件

---

## 二、目录结构

```
app/api/service/Storage/
├── Contract/
│   ├── StorageInterface.php      # 驱动接口契约
│   └── StorageResult.php         # 返回数据结构（不含 driver_path）
├── Driver/
│   ├── AbstractStorage.php       # 驱动抽象基类
│   ├── LocalStorage.php          # 本地存储 (type=local)
│   ├── OssStorage.php           # 阿里云 OSS (type=oss)
│   ├── CosStorage.php           # 腾讯云 COS (type=cos)
│   └── QiniuStorage.php         # 七牛云 (type=qiniu)
├── DirectUpload/
│   ├── DirectUploadManager.php   # 直传管理器
│   ├── DirectUploadProvider.php  # 直传提供者接口
│   ├── AbstractDirectUpload.php  # 直传抽象基类
│   ├── OssDirectUpload.php       # OSS 直传
│   ├── CosDirectUpload.php       # COS 直传
│   └── QiniuDirectUpload.php     # 七牛直传
├── ChannelManager.php            # 渠道配置管理
├── StorageFactory.php            # 驱动工厂
├── PathGenerator.php             # 统一路径生成
└── StorageManager.php            # 统一入口

app/api/model/
└── Files.php                     # 文件模型（SoftDelete）

config/apps/storage/
├── settings.php                  # 全局设置
├── local.php                     # 本地存储默认配置
├── oss.php                       # OSS 默认配置
├── cos.php                       # COS 默认配置
└── qiniu.php                     # 七牛默认配置
```

---

## 三、架构分层

```
┌─────────────────────────────────────────────────────────────┐
│                    Controller (Upload.php)                    │
│         调用方，不关心后端存储细节，只调统一接口                │
└───────────────────────────┬─────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    StorageManager (门面)                      │
│              统一入口，编排上传/删除/查询/批量操作逻辑            │
└───────────────────────────┬─────────────────────────────────┘
                            │
              ┌─────────────┼─────────────┐
              ▼                           ▼
┌───────────────────────────┐ ┌───────────────────────────────┐
│  PathGenerator (路径生成)  │ │  ChannelManager (配置)         │
│  统一模板：                │ │  加载渠道配置，可用性检查        │
│  storage/{date}/{uuid}.{ext}│ │  配置来源：文件默认→DB→.env    │
└───────────────────────────┘ └───────────────┬───────────────┘
                                              │
                                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    StorageFactory (工厂)                    │
│              根据渠道 slug 实例化对应驱动                      │
└───────────────────────────┬─────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                  StorageInterface (接口契约)                   │
│              定义所有驱动必须实现的方法                        │
└───────────────────────────┬─────────────────────────────────┘
                            │
        ┌───────────────────┼───────────────────┐
        ▼                   ▼                   ▼
┌───────────────┐   ┌───────────────┐   ┌───────────────┐
│ LocalStorage   │   │ CloudStorage  │   │             │
│  (type=local) │   │(oss/cos/qiniu)│   │             │
└───────────────┘   └───────────────┘   └───────────────┘

两条上传路径：
  代理上传：Controller → StorageManager → Driver->doUpload() → 文件由服务端传到云端
  客户端直传：Controller → DirectUploadManager → Provider->getUploadCredential() → 前端自行上传 → confirmUpload()
```

---

## 四、配置说明

### 4.1 配置体系

渠道配置使用三层优先级：**文件默认值 → 数据库覆盖 → .env 最高优先**

| 层 | 来源 | 说明 |
|----|------|------|
| Layer 1 | `config/apps/storage/{slug}.php` | 文件默认值，随代码部署 |
| Layer 2 | `configs` 表（group=`storage_{slug}`） | admin 后台编辑，通用 key-value |
| Layer 3 | `.env` | 环境变量，最高优先级 |

### 4.2 渠道配置字段

每个渠道的配置文件（`config/apps/storage/{slug}.php`）定义默认值：

```php
// 示例：config/apps/storage/oss.php
return [
    'access_key' => '',
    'secret_key' => '',
    'bucket' => '',
    'endpoint' => '',
    'url_prefix' => '',           // 访问 URL 前缀
    'allow_mime_types' => 'image/jpeg,image/png,image/gif,image/webp',
    'max_file_size' => 52428800,
    'path_template' => 'storage/{date}/{uuid}.{ext}',  // 路径模板
];
```

**通用字段（所有渠道都有）：**

| 字段 | 说明 |
|------|------|
| `allow_mime_types` | 允许的 MIME 类型（逗号分隔），白名单模式 |
| `max_file_size` | 最大文件大小（字节） |
| `path_template` | 路径模板，支持 `{date}`、`{uuid}`、`{ext}` 占位符 |

**OSS 特有：** `access_key`、`secret_key`、`bucket`、`endpoint`、`url_prefix`
**COS 特有：** `secret_id`、`secret_key`、`bucket`、`region`、`cdn_url`
**七牛特有：** `access_key`、`secret_key`、`bucket`、`domain`
**本地特有：** `root`、`url_prefix`

### 4.3 settings.php - 全局设置

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

## 五、数据库设计

### 5.1 files 表结构

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
  `file_path` varchar(500) NOT NULL COMMENT '存储路径（含扩展名，如 storage/20260517/uuid.jpg）',
  `file_url` varchar(1000) NOT NULL COMMENT '访问URL（完整可访问地址）',
  `file_size` int(11) NOT NULL COMMENT '文件大小(字节)',
  `file_ext` varchar(20) NOT NULL COMMENT '文件扩展名',
  `mime_type` varchar(100) DEFAULT NULL COMMENT 'MIME类型',
  `driver_path` varchar(500) DEFAULT NULL COMMENT '驱动路径（= file_path，API 响应中隐藏）',
  `metadata` json DEFAULT NULL COMMENT '额外元数据（预留字段）',
  `status` tinyint(1) DEFAULT 0 COMMENT '业务状态: 0=正常 1=封禁（仅控制业务层封禁）',
  `upload_status` tinyint(1) DEFAULT 1 COMMENT '上传状态: 0=上传中 1=已完成 2=失败（仅控制上传流程）',
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

> `file_path` 和 `driver_path` 统一存储相对路径+扩展名（如 `storage/20260517/a1b2c3d4-e5f6-4c89-abcd-ef1234567890.jpg`）。
> `file_url` 存完整可访问 URL（如 `https://cdn.example.com/storage/20260517/uuid.jpg`）。
> `driver_path` 在 API 响应中隐藏（`Files` 模型 `$hidden`），不暴露给前端。

### 5.2 业务状态（status 字段）

| 状态值 | 常量 | 说明 |
|--------|------|------|
| 0 | STATUS_NORMAL | 正常 |
| 1 | STATUS_BANNED | 封禁 |

> `status` 仅控制业务层封禁，与上传流程无关。

### 5.3 上传状态（upload_status 字段）

| 状态值 | 常量 | 说明 |
|--------|------|------|
| 0 | UPLOAD_PENDING | 上传中（仅直传场景：已获取凭证但未确认） |
| 1 | UPLOAD_COMPLETED | 上传完成（代理上传始终为此状态） |
| 2 | UPLOAD_FAILED | 上传失败（直传过期/异常） |

> `upload_status` 仅控制上传流程，与业务封禁无关。
> **代理上传**（服务端上传）：`upload_status` 始终为 `COMPLETED`，因为文件已经由服务端传到云端。
> **客户端直传**：先 `PENDING` → 前端上传成功后 `confirmUpload()` → `COMPLETED`。

### 5.4 场景说明

| 场景值 | 常量 | 说明 |
|--------|------|------|
| card | SCENE_CARD | 卡片图片 |
| comment | SCENE_COMMENT | 评论图片 |
| avatar | SCENE_AVATAR | 用户头像 |
| direct | SCENE_DIRECT | 直接上传 |

### 5.5 软删除机制

- `deleted_at` 为 NULL → 正常记录
- `deleted_at` 有值 → 已移入回收站
- 前端"文件列表"视图 → 默认查询（SoftDelete 自动过滤 `deleted_at IS NULL`）
- 前端"回收站"视图 → `onlyTrashed()` 查询（`deleted_at IS NOT NULL`）
- 恢复操作 → `$file->restore()`（清除 `deleted_at`）
- 永久删除 → `Db::table('files')->where('id', $id)->delete()`（物理删除）

---

## 六、接口契约

### 6.1 StorageInterface

```php
interface StorageInterface
{
    public function upload(UploadedFile $file, string $path): StorageResult;
    public function delete(string $driverPath): bool;
    public function exists(string $driverPath): bool;
    public function getUrl(string $driverPath): string;
    public function supportedMimeTypes(): array;
    public function maxFileSize(): int;
    public function getConfig(): array;
    public function getType(): string;
}
```

### 6.2 StorageResult

```php
class StorageResult
{
    public int $id;               // files.id
    public string $url;           // 完整访问URL
    public string $path;          // 存储路径
    public string $driverPath;    // 驱动特定标识（内部使用，不暴露给前端）
    public int $size;             // 文件大小
    public string $mimeType;      // MIME类型
    public string $originalName;  // 原始文件名
    public string $channelSlug;   // 渠道标识
}

// toArray() 输出（不含 driver_path）：
// ['id', 'url', 'path', 'size', 'mime_type', 'original_name', 'channel_slug']
```

---

## 七、API 接口

### 7.1 统一接口（public.php 路由组，需 JwtAuthCheck + PermissionCheck）

| 方法 | 路由 | 控制器方法 | 说明 | 权限要求 |
|------|------|-----------|------|---------|
| POST | /api/upload/upload | upload() | 上传文件 | 登录用户 |
| GET | /api/upload/files | files() | 文件列表（分页/筛选/回收站） | 登录用户 |
| GET | /api/upload/get-file | getFile() | 获取单个文件 | 登录用户 |
| POST | /api/upload/batch-operate | batchOperate() | 统一批量操作 | 管理员 |
| POST | /api/upload/direct-upload-credential | getDirectUploadCredential() | 获取直传凭证 | 登录用户 |
| POST | /api/upload/direct-upload-confirm | confirmDirectUpload() | 确认直传完成 | 登录用户 |
| POST | /api/upload/cleanup | cleanup() | 清理过期记录 | 管理员 |

### 7.2 batchOperate 统一操作

所有状态变更操作统一通过 `POST /api/upload/batch-operate` 接口：

```json
{
  "ids": "[1,2,3]",
  "method": "approve"
}
```

| method | 说明 | 后端行为 |
|--------|------|---------|
| `approve` | 审核通过 | status → STATUS_NORMAL(0) |
| `ban` | 封禁 | status → STATUS_BANNED(1) |
| `toggle_public` | 切换公开 | is_public = 1 - is_public（原子 SQL） |
| `trash` | 移入回收站 | $file->delete()（SoftDelete） |
| `restore` | 恢复 | $file->restore() |
| `hard_delete` | 永久删除 | 删除存储文件 + 物理删除 DB 记录 |

> 单条操作 = ids 只有一个元素的批量操作。前端不需要区分单条/批量。

### 7.3 files() 查询参数

| 参数 | 类型 | 说明 |
|------|------|------|
| page | int | 页码（默认 1） |
| list_rows | int | 每页条数（默认 15） |
| search_value | string | 搜索关键词（匹配 original_name） |
| search_keys | JSON array | 搜索字段 |
| order_key | string | 排序字段 |
| order_desc | string | 排序方向 |
| show_deleted | int | 1=显示回收站（已删除记录），0=正常列表 |
| status | int | 按审核状态筛选 |
| upload_status | int | 按上传状态筛选 |
| scene | string | 按场景筛选 |

### 7.4 权限标识

| 权限 ID | 权限标识 | 路由 | 说明 |
|---------|----------|------|------|
| 73 | storage-view | /api/upload/get-file | 查看文件 |
| 74 | storage-list | /api/upload/files | 文件列表 |
| 82 | storage-batch-operate | /api/upload/batch-operate | 批量操作 |
| 80 | storage-cleanup | /api/upload/cleanup | 清理过期 |

> 注：旧权限 75-80（toggle-delete/toggle-public/review/batch-review/hard-delete/batch-hard-delete）已废弃，被统一的 batch-operate(82) 替代。

---

## 八、驱动实现

### 8.1 驱动类型对比

| 渠道 | 类型 | 上传方式 | 直传支持 | 备注 |
|------|------|----------|----------|------|
| local | local | 流式写入本地 | ❌ | 默认驱动 |
| oss | oss | OSS SDK | ✅ | 阿里云 |
| cos | cos | cURL + V4签名 | ✅ | 腾讯云 |
| qiniu | qiniu | 七牛 SDK | ✅ | 七牛云 |

### 8.2 AbstractStorage 基类

所有驱动继承 `AbstractStorage`，自动获得：

- MIME 类型校验（基于 `allow_mime_types` 配置，使用 `finfo` 检测真实 MIME）
- 文件大小校验（基于 `max_file_size` 配置）
- 文件记录创建（`files` 表）

### 8.3 driver_path 统一规范

所有驱动的 `doUpload()` 返回值统一：

```php
return [
    'path' => $path,           // 相对路径+扩展名（如 storage/20260517/uuid.jpg）
    'url'  => $this->getUrl($path),  // 完整访问 URL
    'driver_path' => $path,    // = path，统一语义
];
```

- `path` 和 `driver_path` 存相对路径+扩展名，不含域名/前缀
- `url` 存完整可访问 URL（通过 `getUrl()` 从 driver_path + url_prefix 拼接）
- SM.MS 特殊：`url` 存 API 返回的 URL，`driver_path` 存我们生成的路径，`getUrl()` 查数据库取 `file_url`

### 8.3 LocalStorage 路径安全

`doDelete()` 使用 `realpath()` 校验防止路径遍历攻击：

```php
$basePath = app()->getRootPath() . 'public/storage/';
$fullPath = realpath($basePath . $driverPath);
if ($fullPath === false || strpos($fullPath, realpath($basePath)) !== 0) {
    return false; // 路径遍历，拒绝
}
```

---

## 九、直传模式

直传模式允许客户端直接上传文件到第三方存储，适用于大文件场景。

### 流程

```
客户端                    服务器                       第三方存储
  │                          │                            │
  │  1.请求直传凭证           │                            │
  │─────────────────────────>│                            │
  │                          │  2.创建待处理记录           │
  │                          │     status=NORMAL          │
  │                          │     upload_status=PENDING  │
  │                          │  3.生成上传凭证             │
  │  4.返回凭证               │                            │
  │<─────────────────────────│                            │
  │                          │                            │
  │  5.使用凭证直接上传        │                            │
  │─────────────────────────────────────────────────────>│
  │                          │                            │
  │  6.返回上传结果           │                            │
  │<─────────────────────────────────────────────────────│
  │                          │                            │
  │  7.确认上传完成           │                            │
  │─────────────────────────>│                            │
  │                          │  8.服务端推导 driver_path   │
  │                          │     upload_status=COMPLETED│
  │                          │                            │
```

### API 调用

```php
// 1. 获取直传凭证
$credential = DirectUploadManager::createPendingRecord(
    $filename, $mime, $size, $path, $userId
);
// 返回: { record_id, upload_url, form_data, expire }

// 2. 客户端使用凭证上传到第三方存储

// 3. 确认上传完成（driver_path 由服务端推导，不接受客户端传入）
DirectUploadManager::confirmUpload($recordId);
```

### 安全设计

- `driver_path` 由服务端推导：直接使用 `file_path`（已含扩展名），不信任客户端
- `confirmUpload` 内部校验 `upload_status == UPLOAD_PENDING`，防止重复确认
- 过期记录通过 `cleanupExpired()` 标记为 `UPLOAD_FAILED`

### PathGenerator - 统一路径生成

```php
// app/api/service/Storage/PathGenerator.php
PathGenerator::generate($channelConfig, $originalFilename);
// 返回如：storage/20260517/a1b2c3d4-e5f6-4c89-abcd-ef1234567890.jpg
```

- 模板从渠道配置 `path_template` 读取，默认 `storage/{date}/{uuid}.{ext}`
- `{date}` → `date('Ymd')`，`{uuid}` → UUID v4，`{ext}` → 文件扩展名
- 代理上传和直传共用同一个 PathGenerator，Controller 层统一调用

---

## 十、权限机制

### 10.1 RBAC 权限控制

- 基于 `roles`、`permissions`、`role_permissions` 表
- `PermissionCheck` 中间件检查用户角色是否有访问路由的权限
- JWT 中包含 `uid`（用户ID）

### 10.2 所有权控制

- 普通用户只能操作自己的文件（`user_id = 当前用户ID`）
- 管理员可操作所有文件
- `isAdmin()` 判断逻辑：JWT 中有 `aid` 字段（旧系统兼容）或 roles_id 包含角色 1（超级管理员）或 2（管理员）

### 10.3 删除机制

| 类型 | 操作 | 谁能用 | 效果 |
|------|------|--------|------|
| **移入回收站** | SoftDelete（`$file->delete()`） | 管理员（batchOperate trash） | 记录标记 deleted_at，用户端不显示，存储文件保留 |
| **恢复** | `$file->restore()` | 管理员（batchOperate restore） | 清除 deleted_at，记录恢复正常 |
| **永久删除** | 物理删除 DB + 删除存储文件 | 管理员（batchOperate hard_delete） | 彻底删除，不可恢复 |

### 10.4 速率限制

- 使用 ThinkPHP `cache()` 助手实现持久化滑动窗口计数器
- 默认：每 60 秒最多 10 次请求
- 应用于：`upload()` 和 `getDirectUploadCredential()`
- 缓存 key：`rate_limit_upload_{uid}`

---

## 十一、使用示例

### 11.1 基本上传（代理上传）

```php
use app\api\service\Storage\StorageManager;
use app\api\service\Storage\PathGenerator;
use app\api\service\Storage\ChannelManager;

$file = request()->file('file');
$channelConfig = ChannelManager::getDefaultChannel();
$path = PathGenerator::generate($channelConfig, $file->getOriginalName());

$result = StorageManager::upload($file, $path, [
    'user_id' => $userId,
    'scene' => 'card',        // 业务场景
    'status' => 0,            // STATUS_NORMAL（业务状态）
    'upload_status' => 1,     // UPLOAD_COMPLETED（代理上传始终为 COMPLETED）
]);

echo $result->id;        // 文件记录ID
echo $result->url;       // 访问URL
```

### 11.2 批量操作

```php
use app\api\service\Storage\StorageManager;

// 审核通过
StorageManager::batchOperate('approve', [1, 2, 3]);

// 封禁
StorageManager::batchOperate('ban', [4, 5]);

// 切换公开状态
StorageManager::batchOperate('toggle_public', [1, 2]);

// 移入回收站
StorageManager::batchOperate('trash', [6]);

// 恢复
StorageManager::batchOperate('restore', [6]);

// 永久删除
StorageManager::batchOperate('hard_delete', [7, 8]);
```

### 11.3 文件列表查询（含回收站）

```php
// 正常文件列表
$result = StorageManager::list([
    'page' => 1,
    'list_rows' => 20,
], $userId, $isAdmin);

// 回收站
$result = StorageManager::list([
    'page' => 1,
    'list_rows' => 20,
    'show_deleted' => 1,
], $userId, $isAdmin);
```

---

## 十二、驱动扩展

### 新增驱动步骤

1. 在 `Driver/` 目录创建新驱动类，继承 `AbstractStorage`
2. 实现抽象方法：`getType()`, `doUpload()`, `doDelete()`, `getUrl()`
3. 在 `StorageFactory::$drivers` 中注册
4. 在 `channels.php` 中添加配置

---

## 十三、错误处理

| 场景 | 异常类型 | 处理方式 |
|------|----------|----------|
| 渠道不存在 | ApiException | 抛出异常 |
| 不支持的文件类型 | ApiException | 抛出异常 |
| 文件大小超限 | ApiException | 抛出异常 |
| 上传失败 | ApiException | 抛出异常 |
| 驱动未实现 | ApiException | 抛出异常 |
| 直传通道不可用 | ApiException | 抛出异常 |
| 文件不存在 | ApiException | 抛出异常 |
| 权限不足 | ApiResponse | 返回 403 |
| 速率限制 | ApiResponse | 返回 429 |
| 不支持的批量操作 | ApiException | 抛出异常 |

---

## 十四、安全设计

### 已实施

| 措施 | 说明 |
|------|------|
| 速率限制 | ThinkPHP Cache 持久化滑动窗口，upload + directUpload |
| driver_path 隐藏 | model `$hidden` + StorageResult `toArray()` 不含 driver_path |
| driver_path 服务端推导 | `confirmUpload` 不接受客户端 driver_path |
| 路径遍历防护 | `LocalStorage::doDelete()` 使用 `realpath()` 校验 |
| 堆栈泄露防护 | `ExceptionHandle` 生产环境不返回堆栈 |
| MIME 检测 | 使用 `finfo` 读文件魔数，不依赖扩展名 |
| ids 校验 | `batchOperate` 用 `array_map('intval')` + `array_filter` |
| toggle_public 原子操作 | 使用 `Db::raw('1 - is_public')` 避免竞态 |
| trash/restore 用模型方法 | 走 SoftDelete 生命周期，不用原生 SQL |
| cleanup admin 守卫 | 需要管理员权限 |
| 路径统一生成 | PathGenerator 统一收口，模板可配置，UUID v4 |
| status/upload_status 分离 | status 仅控制业务封禁，upload_status 仅控制上传流程 |

### 待处理

| # | 严重度 | 问题 |
|---|--------|------|
| S1 | CRITICAL | `confirmDirectUpload` 无归属校验（IDOR） |
| S4 | HIGH | Visitor `getFile` 枚举（uid=0 跳过 visible 过滤） |
| S5 | HIGH | COS 驱动禁用 SSL 验证（MITM 风险） |
| S6 | HIGH | `scene`/`ref_type`/`ref_id` 无输入校验 |
| S8 | MEDIUM | 文件存储在公开 webroot，无访问控制 |
| S9 | LOW | COS 错误信息泄露 bucket URL |

---

## 十五、前端管理页面

### 文件管理（/apps/storage/files）

- **视图切换**：`v-tabs` 切换"文件列表"和"回收站"
- **操作列**：编辑（打开 EditFileDialog）+ 删除（移入回收站/永久删除）
- **EditFileDialog**：集中管理审核状态（正常/封禁）和公开状态
- **批量操作**：审核通过/封禁/切换公开/移入回收站/永久删除
- **统一 API**：所有操作走 `batchOperate` 接口

### 存储配置（/apps/storage/config）

- admin 后台可编辑渠道配置（`path_template`、`url_prefix`、`allow_mime_types`、`max_file_size` 等）
- 配置通过 `Config::setGroup()` 存入 `configs` 表（通用 key-value）
- 前端表单字段定义在 `FrontEnd-Admin/utils/storage.ts` 的 `channelFieldDefs`

---

## 附录

### A. 环境变量

```env
# OSS
OSS_ACCESS_KEY=your-access-key
OSS_SECRET_KEY=your-secret-key
OSS_BUCKET=your-bucket
OSS_ENDPOINT=oss-cn-hangzhou.aliyuncs.com
OSS_URL_PREFIX=https://cdn.example.com

# COS
COS_SECRET_ID=your-secret-id
COS_SECRET_KEY=your-secret-key

# QINIU
QINIU_ACCESS_KEY=your-access-key
QINIU_SECRET_KEY=your-secret-key
QINIU_BUCKET=your-bucket
QINIU_DOMAIN=https://cdn.example.com

```

### B. 路由注册（当前版本）

```php
// app/api/route/public.php
Route::group('', function () {
    Route::post('upload/upload', 'public.Upload/upload');
    Route::get('upload/files', 'public.Upload/files');
    Route::get('upload/get-file', 'public.Upload/getFile');
    Route::post('upload/batch-operate', 'public.Upload/batchOperate');
    Route::post('upload/direct-upload-credential', 'public.Upload/getDirectUploadCredential');
    Route::post('upload/direct-upload-confirm', 'public.Upload/confirmDirectUpload');
    Route::post('upload/cleanup', 'public.Upload/cleanup');
})->middleware([JwtAuthCheck::class, PermissionCheck::class]);
```

### C. StorageManager 方法清单

| 方法 | 说明 | 调用方 |
|------|------|--------|
| `upload($file, $path, $options)` | 上传文件（代理上传，upload_status 始终 COMPLETED） | Upload::upload() |
| `list($params, $userId, $isAdmin)` | 文件列表（支持回收站） | Upload::files() |
| `getFile($fileId, $userId, $isAdmin)` | 获取单个文件 | Upload::getFile() |
| `batchOperate($method, $ids)` | 统一批量操作 | Upload::batchOperate() |
| `hardDelete($fileId)` | 永久删除（内部方法） | batchOperate hard_delete |
| `delete($fileId)` | 删除存储文件（内部方法） | - |
| `checkRateLimit($uid)` | 速率限制检查 | Upload::upload() / getDirectUploadCredential() |

### D. 权限配置

```sql
-- 存储权限（当前版本）
INSERT INTO permissions (id, name, slug, path, method, description) VALUES
(73, '查看文件', 'storage-view', '/api/upload/get-file', 'GET', '查看文件信息'),
(74, '文件列表', 'storage-list', '/api/upload/files', 'GET', '获取文件列表'),
(80, '清理过期', 'storage-cleanup', '/api/upload/cleanup', 'POST', '清理过期文件'),
(82, '批量操作文件', 'storage-batch-operate', '/api/upload/batch-operate', 'POST', '批量操作文件');
```

---

## 版本历史

| 版本 | 日期 | 说明 |
|------|------|------|
| 1.0 | 2026-05-12 | 初始版本，基本上传/删除功能 |
| 2.0 | 2026-05-12 | 新增权限控制、软删除/硬删除、审核机制、文件关联 |
| 3.0 | 2026-05-14 | 重大重构：统一 batchOperate 接口、status 简化为 0/1、upload_status 新增、回收站视图、安全加固（速率限制/路径遍历防护/driver_path 隐藏/服务端推导） |
| 4.0 | 2026-05-17 | 路径重构：PathGenerator 统一路径生成（UUID v4 + 模板可配置）、driver_path 语义统一、OSS url_prefix 不再作为 object key 前缀、upload_status 与 scene 解耦（代理上传始终 COMPLETED）、渠道配置新增 path_template、前端 admin 配置页更新 |
