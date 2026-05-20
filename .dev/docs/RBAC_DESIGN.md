# RBAC 模块设计文档

> 版本：v3.0 | 日期：2026-05-20 | 状态：已实施

---

## 1. 设计原则

- **路由即权限**：权限定义从路由文件 `->name()` + `->setOption('meta')` 自动扫描，不需要数据库 permissions 表
- **hash 标识**：`permission_hash = md5(route_name:method)`，角色权限关联用 hash 而非自增 ID
- **单表查询**：只有 roles + role_permissions 两张表，权限检查不 JOIN
- **缓存透明**：CacheManager 统一封装，Tag 按业务域精确清除，底层可切换（文件/Redis）
- **模块化**：service 层按业务域聚合（RBAC/、Content/、Storage/）
- **三层身份**：游客（免登录）/ 访客（临时注册）/ 用户（正式登录），公开路由通过 `meta.public` 标记

## 2. 架构

### 2.1 三层身份模型

| | 游客 (Tourist) | 访客 (Guest) | 用户 (User) |
|--|:-:|:-:|:-:|
| 有 token | ❌ | ✅ | ✅ |
| 有 uid | ❌ (uid=0) | ✅ (系统自动创建) | ✅ (自己注册) |
| 登记 | 免登记 | 一键登记 (`Auth::guest()`) | 正式注册 (`Auth::login()`) |
| 角色 | 无 | `rolesId=[config('roles.system_roles.guest')]` | `rolesId=[root/admin/user]` |
| 权限来源 | 路由本身（`meta.public=true`） | `role_permissions` 表 | `role_permissions` 表 |

### 2.2 请求生命周期

**公开路由（游客可访问）**：
```
GET /api/cards
      |
      v
无中间件 → 直接访问 Controller
```

**鉴权路由**：
```
GET /api/all/dashboard
Authorization: Bearer eyJxxx
      |
      v
JwtAuthCheck
  - 解析 JWT token
  - 加载用户 (UsersService::Get)
  - 解析 roles_id（is_array 兼容 TP6 json schema）
  - 注入 request: uid, user, rolesId
  - 无 token + visitor_mode: uid=0, rolesId=[4]
  - 无 token + !visitor_mode: 401
      |
      v
PermissionCheck
  - routeName = request()->rule()->getName()
  - method = request()->method()
  - RBAC::checkAccess(rolesId, routeName, method)
      |
      v
RBAC::checkAccess([1,2,3], "dashboard.index", "GET")
  - hash = md5("dashboard.index:GET")
  - meta[hash].public? -> false（鉴权路由）
  - root(1) in [1,2,3]? -> true -> 直接放行
  - hash in getRoleHashSet(roleId)? -> 查缓存/DB
      |
      v
Controller -> 正常执行
```

### 2.3 模块边界

```
app/api/
├── controller/              扁平结构，16 个文件含 trait
│   ├── BaseController.php   param() + paramIndex() + paramCommon()
│   ├── BatchOperateTrait.php batch() + abstract getBatchService()
│   ├── Cards.php, Comments.php, Tags.php, Users.php
│   ├── Info.php, Auth.php, Likes.php, Images.php
│   ├── Roles.php, Permissions.php, Config.php
│   ├── System.php, Dashboard.php, Upload.php, Theme.php
│   └── (无 admin/ user/ public/ 子目录)
├── service/
│   ├── RBAC/
│   │   ├── RBAC.php         核心：checkAccess + getUserPermissions + getRouteMeta
│   │   └── Roles.php        角色 CRUD + 权限分配
│   ├── Content/             Cards, Comments, Tags, Likes, Images
│   ├── Storage/             存储抽象层
│   ├── Users.php            用户 CRUD + 登录注册
│   ├── Config.php           系统配置
│   ├── Dashboard.php        控制台数据
│   └── Theme.php            主题服务
├── middleware/
│   ├── JwtAuthCheck.php     认证：解析 token + 加载用户 + 注入 rolesId
│   ├── PermissionCheck.php  授权：读 routeName + 调 RBAC::checkAccess
│   ├── SessionDebounce.php  防抖
│   └── GeetestCheck.php     极验
├── validate/                场景名：create / update / allCreate / allUpdate
├── model/
│   ├── Roles.php            角色模型 (含 is_system)
│   └── ...
└── route/                   9 个资源域路由文件
    ├── cards.php            卡片路由（含公开 + 鉴权）
    ├── comments.php         评论路由
    ├── tags.php             标签路由
    ├── users.php            用户路由
    ├── auth.php             认证路由
    ├── roles.php            角色管理路由
    ├── permissions.php      权限管理路由
    ├── system.php           系统管理路由
    └── files.php            文件路由
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
  md5("cards.list:GET")      = "afb06a912e810db97bb46f887e3f49c5"
  md5("cards.allList:GET")   = "ca0ec1f8043058e143bb4fba909618e1"
  md5("cards.allUpdate:PATCH") = "ddecc68b8395811eba778869b558727e"
```

### 3.3 角色

| ID | slug | 名称 | is_system | 说明 |
|----|------|------|-----------|------|
| 1 | root | 超级管理员 | 1 | 快速放行，跳过所有权限检查 |
| 2 | admin | 管理员 | 1 | 需要逐条分配权限 |
| 3 | user | 普通用户 | 1 | 注册默认角色 |
| 4 | guest | 访客 | 1 | visitor 模式自动分配，一键注册 |
| 5 | test | 测试 | 0 | 自定义角色 |

**游客没有角色**，通过路由 `meta.public=true` 标记实现免登录访问。

## 4. 路由 meta 声明

### 4.1 语法

```php
// 公开路由（游客可访问）
Route::get('cards', 'Cards/list')
    ->name('cards.list')
    ->setOption('meta', ['name' => '卡片列表', 'group' => '卡片', 'public' => true]);

// 鉴权路由
Route::patch('cards/:id', 'Cards/update')
    ->name('cards.update')
    ->setOption('meta', ['name' => '编辑卡片', 'group' => '卡片'])
    ->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);
```

### 4.2 meta 字段

| 字段 | 类型 | 说明 |
|------|------|------|
| name | string | 中文名称，给人看 |
| group | string | 资源域分组（卡片/评论/标签/用户/认证/角色/权限/系统/配置/文件/点赞） |
| public | bool | 是否公开路由（游客免登录可访问），默认 false |

### 4.3 公开路由列表

| route_name | method | name | group |
|-----------|--------|------|-------|
| cards.list | GET | 卡片列表 | 卡片 |
| cards.hot | GET | 热门卡片 | 卡片 |
| cards.get | GET | 卡片详情 | 卡片 |
| cards.images | GET | 卡片图集 | 卡片 |
| comments.cardList | GET | 卡片评论列表 | 评论 |
| tags.list | GET | 标签列表 | 标签 |
| tags.get | GET | 标签详情 | 标签 |
| theme.config | GET | 主题配置 | 系统 |
| auth.login | POST | 登录 | 认证 |
| auth.register | POST | 注册 | 认证 |
| auth.guest | POST | 访客登录 | 认证 |
| auth.captcha | POST | 获取验证码 | 认证 |

### 4.4 路由扫描

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
        'public'     => $meta['public'] ?? false,   // 新增
    ];
}
```

## 5. RBAC 核心接口

### 5.1 checkAccess — 权限检查

```php
RBAC::checkAccess(array $rolesId, string $routeName, string $method): bool

流程:
  1. hash = md5(routeName:method)
  2. meta[hash].public? -> true（公开路由直接放行）
  3. rolesId 为空? -> false
  4. root(1) in rolesId? -> true (快速放行)
  5. 遍历 rolesId:
     - getRoleHashSet(roleId) -> 缓存的 hash 数组
     - in_array(hash, set)? -> true
  6. 全部不匹配 -> false
```

### 5.2 getUserPermissions — 用户权限列表

```php
RBAC::getUserPermissions(array $rolesId): string[]

返回: 公开路由 + 角色专属权限的 route_name 列表 (去重)
逻辑: 公开路由自动合并（所有角色天然拥有），再叠加 role_permissions 表数据
用途: GET /api/users/me 返回给前端
```

### 5.3 getRouteMeta — 路由权限扫描

```php
RBAC::getRouteMeta(): array

返回: 所有命名路由的权限元数据（含 public 标记）
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
  3. 解析 roles_id（is_array 兼容 TP6 json schema 自动 decode）
  4. 注入 request: uid, user, rolesId
  5. 无 token + visitor_mode: uid=0, user=null, rolesId=[4]
  6. 无 token + !visitor_mode: 401
  7. token 续期: 写入响应头 X-New-Token

注意: 公开路由不经过此中间件

注入的 request 属性:
  - uid: int          用户 ID (0 = visitor)
  - user: object|null 用户模型对象
  - rolesId: array    角色 ID 数组 [1, 2, 3]
```

## 7. 授权中间件 (PermissionCheck)

```php
职责:
  1. 读 request()->rule()->getName() 获取路由名
  2. 路由名为空? -> 放行 (未命名路由不做权限检查)
  3. 读 request->rolesId（由 JwtAuthCheck 设置）
  4. 调 RBAC::checkAccess(rolesId, routeName, method)
  5. 不通过 -> 403 Forbidden

注意: 只处理鉴权路由。公开路由不经过此中间件。
```

## 8. 管理后台

### 8.1 权限列表页

- 数据来源: 路由扫描 (RBAC::getRouteMeta)
- 只读，无新建/编辑/删除操作
- 表格列: 分组 | 名称 | 路由标识 | Method | URL | 公开
- 搜索支持: name, route_name, method, group, path

### 8.2 角色分配权限

- 权限选项: 从路由扫描获取
- 分组: 按 meta.group 分组展示
- 公开路由: checkbox 自动勾选且禁用（灰色锁定状态）
- 存储: 选中的权限 hash 存入 role_permissions 表（不含公开路由）
- API: POST /api/all/roles/{id}/permissions {permission_hashes}

### 8.3 API 端点

| 端点 | 方法 | 说明 |
|------|------|------|
| /api/all/permissions/all | GET | 所有权限 (路由扫描) |
| /api/all/permissions | GET | 权限分页列表 (路由扫描) |
| /api/all/roles | GET | 角色列表 |
| /api/all/roles | POST | 创建角色 |
| /api/all/roles/{id} | PATCH | 更新角色 |
| /api/all/roles/{id} | DELETE | 删除角色 |
| /api/all/roles/{id}/permissions | POST | 分配权限 |
| /api/all/roles/{id}/permissions | GET | 获取角色的权限 hash 列表 |
| /api/users/me | GET | 用户信息 + roles + permissions |

## 9. 前端权限判断

```typescript
// stores/userStore.ts
state: {
    permissions: [] as string[],  // route_name 列表（含公开路由）
},
actions: {
    hasPermission(routeName: string): boolean {
        return this.permissions.includes(routeName);
    },
    hasAdminAccess(): boolean {
        return this.permissions.some(p => p.includes('.allList') || p.includes('.allUpdate'));
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

### 11.1 新增公开路由

```php
// 路由加 name + meta（含 public: true），不挂中间件
Route::get('xxx', 'Xxx/list')
    ->name('xxx.list')
    ->setOption('meta', ['name' => 'XXX列表', 'group' => 'XXX', 'public' => true]);

// 重启后端（路由缓存刷新）
// 公开路由自动对所有角色可见，无需在 role_permissions 表里分配
```

### 11.2 新增鉴权路由

```php
// 路由加 name + meta，挂 JwtAuthCheck + PermissionCheck
Route::post('xxx', 'Xxx/create')
    ->name('xxx.create')
    ->setOption('meta', ['name' => '创建XXX', 'group' => 'XXX'])
    ->middleware(JwtAuthCheck::class)->middleware(PermissionCheck::class);

// 重启后端
// 管理后台 -> 角色管理 -> 分配权限 -> 勾选新权限
```

### 11.3 新建角色

管理后台 -> 角色管理 -> 创建角色

### 11.4 给用户分配角色

```sql
UPDATE users SET roles_id = '[2, 3]' WHERE id = 5;
```

### 11.5 缓存管理

所有缓存操作通过 `CacheManager`（`app/common/cache/CacheManager.php`）统一封装：

```php
use app\common\cache\CacheManager;

// 读取（支持 loader 回源，缓存 miss 时自动调用 loader 并写入）
$data = CacheManager::get('rbac', $key, function () {
    return DB::table('...')->select();
}, CacheManager::TTL_LONG);

// 写入（自动按 domain 打 Tag）
CacheManager::set('rbac', $key, $data, CacheManager::TTL_LONG);

// 按业务域精确清除（不影响其他业务）
CacheManager::clearDomain('rbac');  // 只清除 rbac 相关缓存
```

业务域 Tag 映射（`DOMAIN_TAGS` 常量）：rbac / jwt / captcha / email / system / storage

TTL 常量：`TTL_SHORT(60)` / `TTL_MEDIUM(300)` / `TTL_LONG(3600)` / `TTL_DAY(86400)` / `TTL_3_DAYS(259200)`

### 11.6 角色配置

系统角色定义在 `config/apps/roles.php`，代码中按 slug 引用，不硬编码 ID：

```php
config('roles.system_roles.root')   // → 1
config('roles.system_roles.admin')  // → 2
config('roles.system_roles.user')   // → 3
config('roles.system_roles.guest')  // → 4
config('roles.default_role')        // → 'user'
config('roles.guest_role')          // → 'guest'
config('roles.admin_roles')         // → ['root', 'admin']
```

系统角色（`is_system=1`）不可删除、不可修改 slug。自定义角色 `is_system=0`。

### 11.7 匹配规则

| 维度 | 匹配方式 |
|------|---------|
| 公开路由 | meta.public=true 直接放行，不依赖 rolesId |
| rolesId | 用户的任一角色命中即放行 (OR) |
| routeName | md5(routeName:method) 精确匹配 |
| method | 每个 HTTP 方法独立一条权限记录 |

### 11.8 匹配失败的情况

| 情况 | 结果 |
|------|------|
| 路由没加 ->name() | 直接放行 (不做权限检查) |
| role_permissions 里没有对应 hash | 403（除非 meta.public=true） |
| 角色被软删除 | 用户登录时加载不到该角色 |
| 路由名改了但没重新分配权限 | 403 (hash 变了) |

## 12. 文件清单

### 后端

| 文件 | 职责 |
|------|------|
| service/RBAC/RBAC.php | 核心: checkAccess + getUserPermissions + getRouteMeta + clearCache (CacheManager) |
| service/RBAC/Roles.php | 角色 CRUD + assignPermissions(hash, 过滤 public) + getRolePermissionHashes |
| middleware/JwtAuthCheck.php | 认证: 解析 token + 加载用户 + 注入 rolesId |
| middleware/PermissionCheck.php | 授权: 读 routeName + 调 RBAC::checkAccess |
| controller/Roles.php | 角色 CRUD + AssignPermissions + GetRolePermissionHashes |
| controller/Permissions.php | 权限列表 (读路由扫描，只读) |
| controller/Info.php | Get() 返回 roles + permissions |
| controller/Auth.php | guest() role 从 config 读取 |
| common/cache/CacheManager.php | 统一缓存管理器（Tag 按域精确清除） |
| config/apps/roles.php | 系统角色 slug→ID 映射配置 |
| route/cards.php | 卡片路由（4 公开 + 13 鉴权） |
| route/comments.php | 评论路由（1 公开 + 10 鉴权） |
| route/tags.php | 标签路由（2 公开 + 8 鉴权） |
| route/users.php | 用户路由（10 鉴权） |
| route/auth.php | 认证路由（4 公开 + 4 鉴权） |
| route/roles.php | 角色管理路由（7 鉴权） |
| route/permissions.php | 权限管理路由（2 鉴权） |
| route/system.php | 系统管理路由（1 公开 + 10 鉴权） |
| route/files.php | 文件路由（8 鉴权） |
| validate/Roles.php | 角色验证 (permission_hashes) |

### 前端

| 文件 | 职责 |
|------|------|
| stores/userStore.ts | 用户状态 + permissions + roles + hasAdminAccess + isRoot |
| middleware/01.auth.global.ts | 路由守卫: hasAdminAccess 判断 |
| api/app/admin/permissions.ts | getAllPermissions + getPermissionIndex |
| api/app/admin/roles.ts | 角色 CRUD + assignPermissions + getRolePermissionHashes |
| api/types/permissions.ts | PermissionItem 类型 (hash, route_name, method, name, group, path, public) |
| api/types/roles.ts | AssignPermissions 类型 (permission_hashes) |
| pages/apps/permissions.vue | 权限列表 (只读，分组展示) |
| components/apps/roles/AssignPermissionsDialog.vue | 分配权限 (按 group 分组，公开路由自动勾选且禁用) |
