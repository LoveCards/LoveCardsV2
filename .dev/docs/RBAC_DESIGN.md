# RBAC 完全体升级方案

> 版本：v1.0 | 日期：2026-05-18 | 状态：待实施

---

## 1. 背景与动机

LoveCards 从早期的 user/admin 二元身份模型，渐进式演化出 RBAC 权限系统。当前处于过渡态，存在以下结构性问题：

| # | 问题 | 严重度 | 位置 |
|---|------|--------|------|
| 1 | aid bypass 是死代码 | 🔴 | PermissionCheck:16 |
| 2 | 前端硬编码 role ID 0 或 1 | 🔴 | 01.auth.global.ts:31 |
| 3 | user 路由权限 route_name 未迁移 | 🔴 | SQL |
| 4 | guest 创建给了 role 3(user) 而非 4(guest) | 🟡 | Auth::Guest() |
| 5 | 软删除角色不检查 | 🟡 | PermissionCheck |
| 6 | 零缓存 — 每请求 1+2N 次 DB 查询 | 🟡 | PermissionCheck |
| 7 | 401/403 混用 | 🟡 | PermissionCheck |
| 8 | user/info 不返回权限列表 | 🟡 | Info::Get() |
| 9 | guest 权限记录 slug 是旧 URL path | 🟡 | SQL |
| 10 | service 层平铺 — RBAC 散落三处 | ⚪ | service/ |
| 11 | RolePermissions 与 Roles 功能重复 | ⚪ | 多处 |

## 2. 设计目标

| 目标 | 说明 |
|------|------|
| 模块化 | 按业务域聚合：RBAC/、Content/、Storage/ |
| 高内聚 | 每个文件只做一件事 |
| 不熵增 | 文件数净减少 |
| 缓存透明 | TP Cache 接口，底层可切换 |
| 页面级权限 | 前端按 permission 列表控制 |
| 一步到位 | 不分阶段 |

## 3. 数据模型

### 3.1 现有表（不变）

- users: roles_id (JSON)
- roles: id, name, slug, description
- permissions: id, name, slug, route_name, method
- role_permissions: role_id, permission_id

### 3.2 微调

roles 新增 is_system TINYINT(1) DEFAULT 0

### 3.3 废弃

- aid 概念
- guest 专属权限记录 (slug LIKE guest-%)
- 旧 URL path 权限记录

## 4. 架构设计

### 4.1 请求生命周期

```
Request → JwtAuthCheck → PermissionCheck → Controller
```

JwtAuthCheck: 解析JWT + 加载用户 + 注入 uid/user/rolesId
PermissionCheck: 读 request.rolesId + 调 RBAC::checkAccess + 403

### 4.2 模块边界

```
service/
├── RBAC/
│   ├── RBAC.php          运行时检查+缓存
│   ├── Roles.php         角色CRUD+权限分配
│   └── Permissions.php   权限CRUD
├── Content/
│   ├── Cards.php
│   ├── Comments.php
│   ├── Tags.php
│   ├── Likes.php
│   └── Images.php
├── Storage/              已有不动
├── Users.php             跨域
└── Config.php            跨域
```

## 5. 文件变更清单

### 后端

| 操作 | 文件 | 变更 |
|------|------|------|
| 新增 | service/RBAC/RBAC.php | checkAccess+getUserPermissions+clearCache |
| 移动 | service/Roles.php → RBAC/Roles.php | 吸收RolePermissions CRUD |
| 移动 | service/Permissions.php → RBAC/Permissions.php | 改namespace |
| 移动 | Cards/Comments/Tags/Likes/Images → Content/ | 改namespace |
| 重构 | JwtAuthCheck.php | 吸收用户加载 |
| 重构 | PermissionCheck.php | 改用RBAC::checkAccess |
| 重构 | Info.php | 返回roles+permissions |
| 重构 | Auth.php | Guest() role [3]→[4] |
| 重构 | Roles model | 加is_system |
| 删除 | RolePermissions service+controller+validate | 合并到RBAC/Roles |
| 删除 | BaseService.php | 空壳 |

### 前端

| 操作 | 文件 | 变更 |
|------|------|------|
| 重构 | userStore.ts | 加permissions状态 |
| 重构 | 01.auth.global.ts | 废弃硬编码role ID |

## 6. API变更

GET /api/user/info 增强：返回 roles + permissions 数组
错误码：401未认证 / 403无权限

## 7. 缓存策略

权限检查缓存 TTL 1h，角色权限变更时 Cache::clear()

## 8. 实施计划（16步）

1. RBAC.php 新增
2. Roles.php 移动+重构
3. Permissions.php 移动
4. Content/ 目录+5文件移动
5. 删除 BaseService+RolePermissions service
6. JwtAuthCheck 重构
7. PermissionCheck 重构
8. RolePermissions controller+validate 删除，路由清理
9. controller use语句 15处替换
10. Info::Get() 增强
11. Auth::Guest() 修复
12. Auth::Register() 去默认值
13. Roles is_system
14. SQL迁移脚本
15. 前端改造
16. 端到端验证

## 9. 验证清单

- 后端启动无报错
- 登录返回token
- user/info返回roles+permissions
- root访问admin → 200
- user访问admin → 403
- visitor访问cards → 200
- guest访问admin → 403
- 前端登录正常
- 前端非admin跳转错误页
- 权限变更后缓存清除
- 系统角色不可删除

## 附录 A：目标目录结构

```
app/api/service/
├── RBAC/
│   ├── RBAC.php              运行时权限检查 + 缓存（新增）
│   ├── Roles.php             角色 CRUD + 权限分配 + 缓存清除
│   └── Permissions.php       权限 CRUD
├── Content/
│   ├── Cards.php             卡片 CRUD + 置顶/状态/批量
│   ├── Comments.php          评论 CRUD + 批量
│   ├── Tags.php              标签 CRUD + 关联
│   ├── Likes.php             点赞 CRUD
│   └── Images.php            图片查询
├── Storage/                  存储域（已有，不动）
├── Users.php                 跨域：用户 CRUD + 登录注册
└── Config.php                跨域：系统配置
```

## 附录 B：RBAC service 接口

```php
namespace app\api\service\RBAC;

class RBAC
{
    public static function checkAccess(array $rolesId, string $routeName, string $method): bool;
    public static function getUserPermissions(array $rolesId): array;
    public static function clearCache(int $roleId): void;
}
```

## 附录 C：Roles service 接口

```php
namespace app\api\service\RBAC;

class Roles
{
    // 角色 CRUD
    public static function Index(array $params): array;
    public static function createRole(array $data): string;
    public static function updateRole(int $id, array $data): void;
    public static function Get(int $id): RolesModel;
    public static function deleteRoles($id = false, array $ids = []): void;

    // 权限分配
    public static function assignPermissions(int $roleId, array $permissionIds): void;
    public static function getRolePermissions(int $roleId): array;
    public static function getRolePermissionIds(int $roleId): array;
}
```