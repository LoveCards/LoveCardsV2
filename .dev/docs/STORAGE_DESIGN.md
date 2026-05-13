# Storage 模块设计文档

> 本文档基于实际代码实现生成，描述 Storage 模块的完整架构与使用方法。
> 最后更新：2026-05-14

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

- **配置驱动**：渠道配置存储在 `config/core/storage/` 目录
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
│   ├── QiniuStorage.php         # 七牛云 (type=qiniu)
│   └── SmmsStorage.php          # SM.MS 图床 (type=api)
├── DirectUpload/
│   ├── DirectUploadManager.php   # 直传管理器
│   ├── DirectUploadProvider.php  # 直传提供者接口
│   ├── AbstractDirectUpload.php  # 直传抽象基类
│   ├── OssDirectUpload.php       # OSS 直传
│   ├── CosDirectUpload.php       # COS 直传
│   └── QiniuDirectUpload.php     # 七牛直传
├── ChannelManager.php            # 渠道配置管理
├── StorageFactory.php            # 驱动工厂
└── StorageManager.php            # 统一入口

app/api/model/
└── Files.php                     # 文件模型（SoftDelete）

config/core/storage/
├── channels.php                  # 渠道配置
└── settings.php                 # 存储设置
```

---

## 三、架构分层

```
┌─────────────────────────────────────────────────────────────┐
│                    StorageManager (门面)                      │
│              统一入口，编排上传/删除/查询/批量操作逻辑            │
└───────────────────────────┬─────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    ChannelManager (配置)                     │
│              加载渠道配置，提供可用性检查                      │
└───────────────────────────┬─────────────────────────────────┘
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
│ LocalStorage   │   │ CloudStorage  │   │  ApiStorage   │
│  (type=local) │   │(oss/cos/qiniu)│   │   (smms)     │
└───────────────┘   └───────────────┘   └───────────────┘
```

---

## 四、配置说明

### 4.1 channels.php - 渠道配置

```php
return [
    'local' => [
        'type' => 'local',
        'root' => 'public/storage',
        'url_prefix' => '/storage',
        'allow_mime_types' => 'image/jpeg,image/png,image/gif,image/webp,...',
        'max_file_size' => 10485760,
    ],

    'oss' => [
        'type' => 'oss',
        'access_key' => env('OSS_ACCESS_KEY', ''),
        'secret_key' => env('OSS_SECRET_KEY', ''),
        'bucket' => env('OSS_BUCKET', ''),
        'endpoint' => env('OSS_ENDPOINT', ''),
        'url_prefix' => env('OSS_URL_PREFIX', ''),
        'allow_mime_types' => 'image/jpeg,image/png,image/gif,image/webp',
        'max_file_size' => 52428800,
    ],

    'cos' => [
        'type' => 'cos',
        'secret_id' => env('COS_SECRET_ID', ''),
        'secret_key' => env('COS_SECRET_KEY', ''),
        'bucket' => 'xxx',
        'region' => 'ap-guangzhou',
        'cdn_url' => 'https://xxx.cos.ap-guangzhou.myqcloud.com',
        'allow_mime_types' => 'image/jpeg,image/png,image/gif,image/webp',
        'max_file_size' => 52428800,
    ],

    'qiniu' => [
        'type' => 'qiniu',
        'access_key' => env('QINIU_ACCESS_KEY', ''),
        'secret_key' => env('QINIU_SECRET_KEY', ''),
        'bucket' => env('QINIU_BUCKET', ''),
        'domain' => env('QINIU_DOMAIN', ''),
        'allow_mime_types' => 'image/jpeg,image/png,image/gif,image/webp',
        'max_file_size' => 52428800,
    ],

    'smms' => [
        'type' => 'api',
        'api_key' => env('SMMS_API_KEY', ''),
        'allow_mime_types' => 'image/jpeg,image/png,image/gif,image/webp',
        'max_file_size' => 10485760,
    ],
];
```

### 4.2 settings.php - 全局设置

```php
return [
    'default' => 'cos',  // 默认存储渠道

    'rate_limit' => [
        'max' => 10,      // 每窗口最大请求数
        'window' => 60,   // 时间窗口（秒）
    ],

    'direct_upload' => [
        'expire' => 3600, // 直传凭证有效期（秒）
    ],
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
  `scene` varchar(32) DEFAULT 'direct' COMMENT '场景：card/comment/avatar/direct',
  `ref_type` varchar(32) DEFAULT NULL COMMENT '关联类型：card/comment',
  `ref_id` int(11) DEFAULT NULL COMMENT '关联ID',
  `original_name` varchar(255) DEFAULT NULL COMMENT '原始文件名',
  `file_path` varchar(500) NOT NULL COMMENT '存储路径',
  `file_url` varchar(1000) NOT NULL COMMENT '访问URL',
  `file_size` int(11) NOT NULL COMMENT '文件大小(字节)',
  `file_ext` varchar(20) NOT NULL COMMENT '文件扩展名',
  `mime_type` varchar(100) DEFAULT NULL COMMENT 'MIME类型',
  `driver_path` varchar(500) DEFAULT NULL COMMENT '驱动特定标识（API 响应中隐藏）',
  `metadata` json DEFAULT NULL COMMENT '额外元数据（预留字段）',
  `status` tinyint(1) DEFAULT 0 COMMENT '审核状态: 0=正常 1=封禁',
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

### 5.2 审核状态（status 字段）

| 状态值 | 常量 | 说明 |
|--------|------|------|
| 0 | STATUS_NORMAL | 正常 |
| 1 | STATUS_BANNED | 封禁 |

> 注：v2.5 之前存在 status=3（待审核），已在 v2.5 中移除。所有上传统一为 STATUS_NORMAL(0)。

### 5.3 上传状态（upload_status 字段）

| 状态值 | 常量 | 说明 |
|--------|------|------|
| 0 | UPLOAD_PENDING | 上传中（直传场景，已获取凭证但未确认） |
| 1 | UPLOAD_COMPLETED | 上传完成 |
| 2 | UPLOAD_FAILED | 上传失败（直传过期/异常） |

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
| smms | api | SMMS API | ❌ | 图床 |

### 8.2 AbstractStorage 基类

所有驱动继承 `AbstractStorage`，自动获得：

- MIME 类型校验（基于 `allow_mime_types` 配置，使用 `finfo` 检测真实 MIME）
- 文件大小校验（基于 `max_file_size` 配置）
- 文件记录创建（`files` 表）

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

- `driver_path` 由服务端推导：`file_path . '.' . file_ext`，不信任客户端
- `confirmUpload` 内部校验 `upload_status == UPLOAD_PENDING`，防止重复确认
- 过期记录通过 `cleanupExpired()` 标记为 `UPLOAD_FAILED`

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

### 11.1 基本上传

```php
use app\api\service\Storage\StorageManager;

$file = request()->file('file');
$path = 'images/' . date('Ymd') . '/' . uniqid();
$result = StorageManager::upload($file, $path, [
    'user_id' => $userId,
    'scene' => 'direct',
    'status' => 0,           // STATUS_NORMAL
    'upload_status' => 1,    // UPLOAD_COMPLETED
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

### 待处理

| # | 严重度 | 问题 |
|---|--------|------|
| S1 | CRITICAL | `confirmDirectUpload` 无归属校验（IDOR） |
| S4 | HIGH | Visitor `getFile` 枚举（uid=0 跳过 visible 过滤） |
| S5 | HIGH | COS/SMMS 驱动禁用 SSL 验证（MITM 风险） |
| S6 | HIGH | `scene`/`ref_type`/`ref_id` 无输入校验 |
| S8 | MEDIUM | 文件存储在公开 webroot，无访问控制 |
| S9 | LOW | COS 错误信息泄露 bucket URL |
| F1 | MEDIUM | 存储配置页面使用硬编码静态数据 |

---

## 十五、前端管理页面

### 文件管理（/apps/storage/files）

- **视图切换**：`v-tabs` 切换"文件列表"和"回收站"
- **操作列**：编辑（打开 EditFileDialog）+ 删除（移入回收站/永久删除）
- **EditFileDialog**：集中管理审核状态（正常/封禁）和公开状态
- **批量操作**：审核通过/封禁/切换公开/移入回收站/永久删除
- **统一 API**：所有操作走 `batchOperate` 接口

### 存储配置（/apps/storage/config）

- 只读展示渠道配置和基础设置
- 数据来自前端硬编码（后端无配置读写 API）

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

# SMMS
SMMS_API_KEY=your-api-key
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
| `upload($file, $path, $options)` | 上传文件 | Upload::upload() |
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
