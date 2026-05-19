# RBAC 模块设计文档

> 版本：v2.0 | 日期：2026-05-18 | 状态：已实施

---

## 1. 设计原则

- **路由即权限**：权限定义从路由文件 `->name()` + `->setOption('meta')` 自动扫描，不需要数据库 permissions 表
- **hash 标识**：`permission_hash = md5(route_name:method)`，角色权限关联用 hash 而非自增 ID
- **单表查询**：只有 roles + role_permissions 两张表，权限检查不 JOIN
- **缓存透明**：TP Cache 接口，底层可切换（文件/Redis）
- **模块化**：service 层按业务域聚合（RBAC/、Content/、Storage/）

## 2. 架构

### 2.1 请求生命周期

```
GET /api/admin/cards
Authorization: Bearer eyJxxx
      |
      v
JwtAuthCheck
  - 解析 JWT token
  - 加载用户 (UsersService::Get)
  - 解析 roles_id
  - 注入 request: uid, user, rolesId
  - visitor 模式: uid=0, rolesId=[4]
      |
      v
PermissionCheck
  - routeName = request()->rule()->getName()  // "admin.cards.index"
  - method = request()->method()               // "GET"
  - RBAC::checkAccess(rolesId, routeName, method)
      |
      v
RBAC::checkAccess([1,2,3], "admin.cards.index", "GET")
  - root(1) in [1,2,3]? -> true -> 直接放行
  - hash = md5("admin.cards.index:GET")
  - 查缓存 rbac:set:{roleId} -> in_array(hash, set)
  - 缓存未命中 -> DB 单表查询 role_permissions
  - 结果写缓存 (TTL 1h)
      |
      v
Controller -> 正常执行
```

### 2.2 模块边界

```
app/api/
├── service/RBAC/
│   ├── RBAC.php           核心：checkAccess + getUserPermissions + getRouteMeta + clearCache
│   └── Roles.php          角色 CRUD + 权限分配 (assignPermissions by hash)
├── service/Content/
│   ├── Cards.php
│   ├── Comments.php
│   ├── Tags.php
│   ├── Likes.php
│   └── Images.php
├── service/Storage/       已有，不动
├── service/Users.php      跨域：用户 CRUD + 登录注册
├── service/Config.php     跨域：系统配置
├── middleware/
│   ├── JwtAuthCheck.php   认证：解析 token + 加载用户 + 注入 rolesId
│   └── PermissionCheck.php 授权：读 routeName + 调 RBAC::checkAccess
├── model/
│   ├── Roles.php          角色模型 (含 is_system)
│   └── RolePermissions.php 角色权限关联模型 (permission_hash，无业务逻辑)
└── route/
    ├── admin.php          管理端路由 (36 条，全部有 name + meta)
    ├── user.php           用户端路由 (21 条，全部有 name + meta)
    └── public.php         公开路由 (13 条，全部有 name + meta)
```

## 3. 数据库

### 3.1 表结构（2 张表）

```sql
-- 角色表
roles (
  id, name, slug, description, is_system,
  created_at, updated_at, deleted_at
)

-- 角色权限关联表
role_permissions (
  id, role_id, permission_hash, created_at
  UNIQUE(role_id, permission_hash)
  FK -> roles(id) ON DELETE CASCADE
)
```

**没有 permissions 表。** 权限定义从路由文件实时扫描。

### 3.2 permission_hash 计算

```
permission_hash = md5(route_name + ":" + method)

示例:
  md5("admin.cards.index:GET")    = "85760574831dcab7ea1a4eb1b132fd85"
  md5("admin.cards.update:PATCH") = "321ba78e6f233055dbe7714e1bdd5715"
  md5("user.auth.login:POST")     = "aad6d7831ae99afc9e623bcf99e0f72f"
```

### 3.3 角色

| ID | slug | 名称 | is_system | 说明 |
|----|------|------|-----------|------|
| 1 | root | 超级管理员 | 1 | 快速放行，跳过所有权限检查 |
| 2 | admin | 管理员 | 1 | 需要逐条分配权限 |
| 3 | user | 普通用户 | 1 | 注册默认角色 |
| 4 | guest | 访客 | 1 | visitor 模式自动分配 |

## 4. 路由 meta 声明

### 4.1 语法

```php
Route::get('admin/cards', 'admin.Cards/Index')
    ->name('admin.cards.index')
    ->setOption('meta', [
        'name'  => '获取卡片列表',   // 中文名，管理后台展示
        'group' => 'admin.cards',     // 分组，管理后台分组展示
    ]);
```

### 4.2 meta 字段

| 字段 | 类型 | 说明 |
|------|------|------|
| name | string | 中文名称，给人看 |
| group | string | 分组标识，格式: 模块.资源 |

### 4.3 路由扫描

```php
// RBAC::getRouteMeta() 内部
$routeList = Route::getRuleName()->getRuleList();

foreach ($routeList as $rule) {
    $name = $rule['name'];           // ->name() 的值
    $method = $rule['method'];       // HTTP 方法
    $meta = $rule['option']['meta']; // ->setOption('meta', [...]) 的值
    $path = $rule['rule'];           // URL path pattern

    $hash = md5($name . ':' . $method);
    $result[$hash] = [
        'hash'       => $hash,
        'route_name' => $name,
        'method'     => $method,
        'name'       => $meta['name'] ?? $name,
        'group'      => $meta['group'] ?? '',
        'path'       => '/' . ltrim($path, '/'),
    ];
}
```

## 5. RBAC 核心接口

### 5.1 checkAccess — 权限检查

```php
RBAC::checkAccess(array $rolesId, string $routeName, string $method): bool

流程:
  1. rolesId 为空? -> false
  2. root(1) in rolesId? -> true (快速放行)
  3. hash = md5(routeName:method)
  4. 遍历 rolesId:
     - getRoleHashSet(roleId) -> 缓存的 hash 数组
     - in_array(hash, set)? -> true
  5. 全部不匹配 -> false
```

### 5.2 getUserPermissions — 用户权限列表

```php
RBAC::getUserPermissions(array $rolesId): string[]

返回: 用户所有角色拥有的 route_name 列表 (去重)
用途: GET /api/user/info 返回给前端
```

### 5.3 getRouteMeta — 路由权限扫描

```php
RBAC::getRouteMeta(): array

返回: 所有命名路由的权限元数据
用途: 管理后台权限列表页、角色分配权限时的选项列表
```

### 5.4 clearCache — 缓存清除

```php
RBAC::clearCache(int $roleId): void

触发时机: 角色权限变更 (assignPermissions / deleteRoles)
当前实现: Cache::clear() (全量清除)
```

## 6. 认证中间件 (JwtAuthCheck)

```php
职责:
  1. 解析 JWT token (Jwt::verify)
  2. 加载用户 (UsersService::Get)
  3. 解析 roles_id
  4. 注入 request: uid, user, rolesId
  5. visitor 模式: uid=0, user=null, rolesId=[4]
  6. token 续期: 写入响应头 X-New-Token

注入的 request 属性:
  - uid: int          用户 ID (0 = visitor)
  - user: object|null 用户模型对象
  - rolesId: array    角色 ID 数组 [1, 2, 3]
  - 注意: 不再注入 JwtData，控制器用 request()->uid 代替
```

## 7. 授权中间件 (PermissionCheck)

```php
职责:
  1. 读 request()->rule()->getName() 获取路由名
  2. 路由名为空? -> 放行 (未命名路由不做权限检查)
  3. 读 request->rolesId
  4. 调 RBAC::checkAccess(rolesId, routeName, method)
  5. 不通过 -> 403 Forbidden

注意: 不查用户表，不处理 token，纯授权判断
```

## 8. 管理后台

### 8.1 权限列表页 (/admin/apps/permissions)

- 数据来源: 路由扫描 (RBAC::getRouteMeta)
- 只读，无新建/编辑/删除操作
- 表格列: 分组 | 名称 | 路由标识 | Method | URL
- 搜索支持: name, route_name, method, group, path

### 8.2 角色分配权限

- 权限选项: 从路由扫描获取
- 分组: 按 meta.group 分组展示
- 存储: 选中的权限 hash 存入 role_permissions 表
- API: POST /api/admin/role/assign-permissions {id, permission_hashes}

### 8.3 API 端点

| 端点 | 方法 | 说明 |
|------|------|------|
| /api/admin/permissions/all | GET | 所有权限 (路由扫描) |
| /api/admin/permissions | GET | 权限分页列表 (路由扫描) |
| /api/admin/roles | GET | 角色列表 |
| /api/admin/role | POST | 创建角色 |
| /api/admin/role | PATCH | 更新角色 |
| /api/admin/role | DELETE | 删除角色 |
| /api/admin/role/assign-permissions | POST | 分配权限 |
| /api/admin/role/permissions | GET | 获取角色的权限 hash 列表 |
| /api/user/info | GET | 用户信息 + roles + permissions |

## 9. 前端权限判断

```typescript
// stores/userStore.ts
state: {
    permissions: [] as string[],  // route_name 列表
},
actions: {
    hasPermission(routeName: string): boolean {
        return this.permissions.includes(routeName);
    },
    hasAdminAccess(): boolean {
        return this.permissions.some(p => p.startsWith('admin.'));
    }
}

// middleware/01.auth.global.ts
if (userStore.hasAdminAccess()) {
    // 允许进入后台
} else {
    // 跳转错误页
}
```

## 10. 缓存策略

| 缓存 key | 内容 | TTL | 失效时机 |
|---------|------|-----|---------|
| rbac:set:{roleId} | 角色的 hash 集合 | 1h | 角色权限变更时 Cache::clear() |
| rbac:route_meta | 路由扫描结果 | 1h | 路由变更后重启自动刷新 |
| rbac:perms:{hash} | 用户权限列表 | 1h | 角色权限变更时清除 |

## 11. 使用指南

### 11.1 新增路由权限

```php
// 1. 路由加 name + meta
Route::post('admin/xxx', 'admin.Xxx/Create')
    ->name('admin.xxx.create')
    ->setOption('meta', ['name' => '创建XXX', 'group' => 'admin.xxx']);

// 2. 重启后端 (路由缓存刷新)

// 3. 管理后台 -> 角色管理 -> 分配权限 -> 勾选新权限
```

### 11.2 新建角色

管理后台 -> 角色管理 -> 创建角色

### 11.3 给用户分配角色

```sql
UPDATE users SET roles_id = '[2, 3]' WHERE id = 5;
```

### 11.4 匹配规则

| 维度 | 匹配方式 |
|------|---------|
| rolesId | 用户的任一角色命中即放行 (OR) |
| routeName | md5(routeName:method) 精确匹配 |
| method | 每个 HTTP 方法独立一条权限记录 |

### 11.5 匹配失败的情况

| 情况 | 结果 |
|------|------|
| 路由没加 ->name() | 直接放行 (不做权限检查) |
| role_permissions 里没有对应 hash | 403 |
| 角色被软删除 | 用户登录时加载不到该角色 |
| 路由名改了但没重新分配权限 | 403 (hash 变了) |

## 12. 文件清单

### 后端

| 文件 | 职责 |
|------|------|
| service/RBAC/RBAC.php | 核心: checkAccess + getUserPermissions + getRouteMeta + clearCache |
| service/RBAC/Roles.php | 角色 CRUD + assignPermissions(hash) + getRolePermissionHashes |
| middleware/JwtAuthCheck.php | 认证: 解析 token + 加载用户 + 注入 rolesId |
| middleware/PermissionCheck.php | 授权: 读 routeName + 调 RBAC::checkAccess |
| model/Roles.php | 角色模型 (含 is_system) |
| model/RolePermissions.php | 角色权限关联模型 (permission_hash) |
| controller/admin/Roles.php | 角色 CRUD + AssignPermissions + GetRolePermissionHashes |
| controller/admin/Permissions.php | 权限列表 (读路由扫描，只读) |
| controller/user/Info.php | Get() 返回 roles + permissions |
| controller/user/Auth.php | Guest() role [4] |
| route/admin.php | 管理端路由 (36 条) |
| route/user.php | 用户端路由 (21 条) |
| route/public.php | 公开路由 (13 条) |
| validate/Roles.php | 角色验证 (permission_hashes) |

### 前端

| 文件 | 职责 |
|------|------|
| stores/userStore.ts | 用户状态 + permissions + hasAdminAccess |
| middleware/01.auth.global.ts | 路由守卫: hasAdminAccess 判断 |
| api/app/admin/permissions.ts | getAllPermissions + getPermissionIndex |
| api/app/admin/roles.ts | 角色 CRUD + assignPermissions + getRolePermissionHashes |
| api/types/permissions.ts | PermissionItem 类型 (hash, route_name, method, name, group, path) |
| api/types/roles.ts | AssignPermissions 类型 (permission_hashes) |
| pages/apps/permissions.vue | 权限列表 (只读，分组展示) |
| components/apps/roles/AssignPermissionsDialog.vue | 分配权限 (按 group 分组，存 hash) |
