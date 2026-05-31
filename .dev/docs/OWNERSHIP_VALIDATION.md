# 资源所有权验证设计文档

> 版本：1.0.0  
> 更新时间：2026-05-30  
> 作者：LoveCards Team

---

## 一、概述

### 1.1 问题背景

在多用户系统中，资源所有权验证是核心安全机制。用户只能操作自己拥有的资源，管理员可以操作任意资源。

**原有问题**：
- Cards、Tags 模块缺少所有权验证
- 任何登录用户可修改/删除任意资源
- 存在严重安全漏洞

### 1.2 设计目标

- 用户只能编辑/删除自己的资源
- 管理员可以编辑/删除任意资源
- 代码优雅、可复用、易维护
- 低熵增、高可读性

### 1.3 解决方案

采用 **Service + Trait** 模式：
- 所有权验证是业务逻辑，在 Service 层完成
- Trait 提供复用，避免代码重复
- 控制器显式传递 `uid`，语义清晰
- 管理员接口不传 `uid`，跳过所有权验证

---

## 二、架构设计

### 2.1 系统分层

```
中间件层：JwtAuthCheck → PermissionCheck（认证 + 授权，不碰业务逻辑）
     ↓
控制器层：获取参数 → 调用 Service → 返回响应（显式传递 uid）
     ↓
Service 层：业务逻辑 + 所有权验证（Trait 声明规则，一行代码验证）
     ↓
Model 层：数据持久化（无需关心所有权）
```

### 2.2 核心组件

| 组件 | 文件路径 | 职责 |
|------|---------|------|
| Ownable Trait | `app/common/service/Traits/Ownable.php` | 提供所有权验证方法 |
| Cards Service | `app/api/service/Content/Cards.php` | 卡片业务逻辑 + 所有权验证 |
| Tags Service | `app/api/service/Content/Tags.php` | 标签业务逻辑 + 所有权验证 |

---

## 三、Ownable Trait 使用指南

### 3.1 Trait 定义

**文件**：`app/common/service/Traits/Ownable.php`

```php
<?php

namespace app\common\service\Traits;

use app\api\ApiException;

/**
 * 资源所有权验证 Trait
 *
 * 在 Service 类中使用：
 *
 * class Cards
 * {
 *     use Ownable;
 *
 *     // 必须声明以下常量
 *     const MODEL_CLASS = CardsModel::class;
 *     const OWNER_FIELD = 'user_id';
 *     const RESOURCE_NAME = '卡片';
 * }
 */
trait Ownable
{
    /**
     * 验证单个资源所有权
     *
     * @param int $id 资源 ID
     * @param int $uid 当前用户 ID
     * @throws ApiException 资源不存在或无权访问
     */
    protected static function assertOwner(int $id, int $uid): void
    {
        $modelClass = static::MODEL_CLASS;
        $ownerField = static::OWNER_FIELD ?? 'user_id';
        $resourceName = static::RESOURCE_NAME ?? '资源';

        $resource = $modelClass::where('id', $id)->findOrEmpty();

        if ($resource->isEmpty()) {
            throw ApiException::notFound($resourceName . '不存在');
        }

        if ($resource->$ownerField != $uid) {
            throw ApiException::forbidden('无权操作此' . $resourceName);
        }
    }

    /**
     * 验证批量资源所有权
     *
     * @param array $ids 资源 ID 列表
     * @param int $uid 当前用户 ID
     * @throws ApiException 部分资源无权访问
     */
    protected static function assertOwnerBatch(array $ids, int $uid): void
    {
        if (empty($ids)) {
            return;
        }

        $modelClass = static::MODEL_CLASS;
        $ownerField = static::OWNER_FIELD ?? 'user_id';
        $resourceName = static::RESOURCE_NAME ?? '资源';

        $notOwnedIds = $modelClass::whereIn('id', $ids)
            ->where($ownerField, '<>', $uid)
            ->column('id');

        if ($notOwnedIds) {
            $count = count($notOwnedIds);
            if ($count === 1) {
                throw ApiException::forbidden('无权操作此' . $resourceName);
            } else {
                throw ApiException::forbidden('无权操作部分' . $resourceName);
            }
        }
    }

    /**
     * 验证资源所有权（管理员跳过）
     *
     * @param int $id 资源 ID
     * @param int|null $uid 用户 ID（null 表示管理员操作）
     * @throws ApiException 资源不存在或无权访问
     */
    protected static function assertOwnerIf(int $id, ?int $uid): void
    {
        if ($uid !== null) {
            self::assertOwner($id, $uid);
        }
    }

    /**
     * 验证批量资源所有权（管理员跳过）
     *
     * @param array $ids 资源 ID 列表
     * @param int|null $uid 用户 ID（null 表示管理员操作）
     * @throws ApiException 部分资源无权访问
     */
    protected static function assertOwnerBatchIf(array $ids, ?int $uid): void
    {
        if ($uid !== null) {
            self::assertOwnerBatch($ids, $uid);
        }
    }
}
```

### 3.2 三步骤集成

**步骤 1**：引入 Trait

```php
use app\common\service\Traits\Ownable;

class Cards
{
    use Ownable;
    // ...
}
```

**步骤 2**：声明常量

| 常量 | 说明 | 示例 |
|------|------|------|
| `MODEL_CLASS` | Model 类名（完整命名空间） | `CardsModel::class` |
| `OWNER_FIELD` | 所有者字段名 | `'user_id'` |
| `RESOURCE_NAME` | 资源名称（用于错误提示） | `'卡片'` |

**步骤 3**：在方法中调用

| 场景 | 方法 | 代码 |
|------|------|------|
| 单个更新（用户操作） | `assertOwnerIf($id, $uid)` | `self::assertOwnerIf($data['id'], $uid)` |
| 单个更新（管理员操作） | 跳过验证 | 不传 `$uid` 即可 |
| 批量删除（用户操作） | `assertOwnerBatchIf($ids, $uid)` | `self::assertOwnerBatchIf($ids, $uid)` |
| 批量删除（管理员操作） | 跳过验证 | 不传 `$uid` 即可 |

---

## 四、API 端点对照

### Cards 模块

| API | 方法 | 角色 | 所有权验证 |
|-----|------|------|-----------|
| `/cards/:id` | `PATCH` | 用户 | ✅ `assertOwnerIf($id, $uid)` |
| `/cards/:id` | `DELETE` | 用户 | ✅ `assertOwnerBatchIf([$id], $uid)` |
| `/all/cards/:id` | `PATCH` | 管理员 | ❌ 跳过 |
| `/all/cards/:id` | `DELETE` | 管理员 | ❌ 跳过 |

### Tags 模块

| API | 方法 | 角色 | 所有权验证 |
|-----|------|------|-----------|
| `/tags/:id` | `PATCH` | 用户 | ✅ `assertOwnerIf($id, $uid)` |
| `/tags/:id` | `DELETE` | 用户 | ✅ `assertOwnerBatchIf([$id], $uid)` |
| `/all/tags/:id` | `PATCH` | 管理员 | ❌ 跳过 |
| `/all/tags/:id` | `DELETE` | 管理员 | ❌ 跳过 |

### 其他模块

| 模块 | 状态 | 实现方式 |
|------|------|---------|
| Comments | ✅ 安全 | `where(['user_id' => $uid])` |
| Files | ✅ 安全 | 验证 `user_id` + `is_public` |
| Likes | ✅ 安全 | 验证 `uid` |
| Users | ✅ 安全 | 验证 `uid` |

---

## 五、错误处理

| 场景 | 异常 | HTTP | 错误码 |
|------|------|------|--------|
| 资源不存在 | `ApiException::notFound()` | 404 | 9003 |
| 无权访问 | `ApiException::forbidden()` | 403 | 1101 |

响应格式：
```json
{
    "code": 9003,
    "message": "卡片不存在"
}
```

---

## 六、新模块扩展指南

### 6.1 步骤

1. Model 必须有所有者字段（如 `user_id`）
2. Service 引入 `Ownable` Trait
3. 声明 `MODEL_CLASS`、`OWNER_FIELD`、`RESOURCE_NAME`
4. 在 update/delete 方法中调用 `assertOwnerIf()` / `assertOwnerBatchIf()`
5. 控制器传递 `request()->uid`（用户接口）/ 不传（管理员接口）

### 6.2 示例

```php
class NewResource
{
    use Ownable;

    const MODEL_CLASS = NewResourceModel::class;
    const OWNER_FIELD = 'user_id';
    const RESOURCE_NAME = '新资源';

    static public function update(array $data, ?int $uid = null): void
    {
        self::assertOwnerIf($data['id'], $uid);
        // 业务逻辑...
    }
}
```

---

## 七、常见问题

**Q：为什么不在中间件层验证所有权？**

所有权验证是业务逻辑，不同场景规则不同，应该在 Service 层完成。

**Q：为什么用 Trait 而不是基类？**

Trait 更灵活，不强制继承关系，Service 可自由选择。

**Q：管理员接口如何确保安全？**

由 `PermissionCheck` 中间件验证角色权限，只有管理员才能访问。

**Q：批量操作如何处理无权资源？**

当前设计为"全部拒绝"——任一资源无权则全部拒绝。

---

## 八、迁移记录

### v1.0.0 (2026-05-30)

| 文件 | 操作 | 说明 |
|------|------|------|
| `common/service/Traits/Ownable.php` | 新增 | 所有权验证 Trait |
| `api/service/Content/Cards.php` | 修改 | 引入 Trait + `updateCard()` `deleteCards()` 增加 `$uid` 参数 |
| `api/controller/Content/Cards.php` | 修改 | 适配新接口签名 |
| `api/service/Content/Tags.php` | 修改 | 引入 Trait + `updateTag()` `deleteTags()` 增加 `$uid` 参数 |
| `api/controller/Content/Tags.php` | 修改 | 适配新接口签名 |
| `api/service/Content/Cards.php` | 删除 | `updateAny()` `deleteAny()` 冗余方法 |
| `api/service/Content/Tags.php` | 删除 | `updateAny()` `deleteAny()` 冗余方法 |
