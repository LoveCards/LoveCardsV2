# 控制器层重构设计文档

> 版本：v1.0 | 日期：2026-05-20 | 状态：已实施

---

## 1. 设计目标

- **控制器扁平化**：去掉 `admin/user/public` 三层目录，`controller/` 下直接放模块文件
- **命名解耦 RBAC**：方法名表达数据范围（`all*`），不表达角色身份（`admin*`）
- **RESTful 路由**：URL 路径表达资源范围（`/cards`、`/all/cards`），路由文件按资源域组织
- **逻辑下沉**：控制器只做管道（参数→服务→响应），业务逻辑全部在 Service 层
- **消除重复**：BaseController 增强、BatchOperateTrait、FieldsToggleTrait

---

## 2. 设计原则

### 2.1 控制器 = 纯管道

```
控制器职责：获取参数 → 调用 Service → 构建响应
禁止：直接操作 Model/Db、业务逻辑判断、角色判断
```

### 2.2 命名表达范围，不表达角色

```
all*  = 全部数据范围（不限 user_id，含非公开状态）
无前缀 = 默认范围（公开数据 / 自己的数据，取决于具体操作语义）

❌ adminUpdate / userList / publicIndex
✅ allUpdate / update / list
```

### 2.3 参数不同 = 拆成两个方法

```
admin 和 user 编辑卡片的验证规则不同（admin 能改 status，user 不能）
→ 拆成 update() 和 allUpdate()，各自有独立的 Validate 场景
→ RBAC 分别配置哪些角色能访问哪个方法
```

### 2.4 参数相同 = 一个方法

```
create() 不管 admin 还是 user 都用同一个
→ 一条路由，RBAC 配置哪些角色能访问
→ Service 内部根据 uid 决定是否需要审核等细节
```

### 2.5 路由无角色，只有范围

```
路由文件按资源域组织：cards.php / comments.php / ...
URL 路径表达范围：/cards（默认）、/all/cards（全部）
RBAC 通过路由名配置谁能访问，不通过文件位置
```

---

## 3. 文件结构

### 3.1 控制器（扁平，16 个文件含 trait）

```
app/api/controller/
├── BaseController.php      ← 重写：param() + paramIndex() + paramCommon()
├── BatchOperateTrait.php   ← 新建：batch() + abstract getBatchService()
├── Cards.php
├── Comments.php
├── Tags.php
├── Users.php
├── Info.php
├── Auth.php
├── Likes.php
├── Images.php
├── Roles.php
├── Permissions.php
├── Config.php
├── System.php
├── Dashboard.php
├── Upload.php
└── Theme.php
```

**删除**：`Params.php`、`admin/` 目录（10 个文件）、`user/` 目录（6 个文件）、`public/` 目录（4 个文件）

### 3.2 Service 层

```
app/api/service/
├── Content/
│   ├── Cards.php
│   ├── Comments.php
│   ├── Tags.php
│   ├── Images.php
│   ├── Likes.php
│   └── FieldsToggleTrait.php  ← 新建
├── RBAC/
│   ├── RBAC.php
│   └── Roles.php
├── Storage/
│   └── ChannelTester.php      ← 新建（从 Config 控制器提取）
├── Users.php
├── Config.php
├── Dashboard.php              ← 新建
└── Theme.php
```

### 3.3 路由文件（按资源域，9 个文件）

```
app/api/route/
├── cards.php
├── comments.php
├── tags.php
├── users.php
├── auth.php
├── roles.php
├── permissions.php
├── system.php
└── files.php
```

**删除**：`admin.php`、`user.php`、`public.php`

---

## 4. 控制器方法一览

### Cards.php

| 方法 | 路由 | 语义 |
|------|------|------|
| `list()` | GET /cards | 公开卡片列表 |
| `hotList()` | GET /cards/hot | 热门卡片 |
| `get($id)` | GET /cards/{id} | 卡片详情 |
| `images($id)` | GET /cards/{id}/images | 卡片图集 |
| `create()` | POST /cards | 创建卡片 |
| `update($id)` | PATCH /cards/{id} | 编辑自己的卡片 |
| `delete($id)` | DELETE /cards/{id} | 删除/隐藏自己的卡片 |
| `like($id)` | POST /cards/{id}/like | 点赞 |
| `comment($id)` | POST /cards/{id}/comments | 评论 |
| `listOwn()` | GET /users/me/cards | 我的卡片列表 |
| `allList()` | GET /all/cards | 全部卡片列表 |
| `allGet($id)` | GET /all/cards/{id} | 获取任意卡片 |
| `allUpdate($id)` | PATCH /all/cards/{id} | 编辑任意卡片 |
| `allDelete($id)` | DELETE /all/cards/{id} | 删除任意卡片 |
| `batch()` | POST /all/cards/batch | 批量操作 |
| `setting()` | GET/POST /cards/setting | 卡片设置 |

### Comments.php

| 方法 | 路由 | 语义 |
|------|------|------|
| `cardList($cardId)` | GET /cards/{id}/comments | 卡片的评论列表 |
| `create($cardId)` | POST /cards/{id}/comments | 创建评论 |
| `get($id)` | GET /comments/{id} | 评论详情 |
| `update($id)` | PATCH /comments/{id} | 编辑评论 |
| `delete($id)` | DELETE /comments/{id} | 删除自己的评论 |
| `listOwn()` | GET /users/me/comments | 我的评论 |
| `allList()` | GET /all/comments | 全部评论列表 |
| `allGet($id)` | GET /all/comments/{id} | 获取任意评论 |
| `allUpdate($id)` | PATCH /all/comments/{id} | 编辑任意评论 |
| `allDelete($id)` | DELETE /all/comments/{id} | 删除任意评论 |
| `batch()` | POST /all/comments/batch | 批量操作 |

### Tags.php

| 方法 | 路由 | 语义 |
|------|------|------|
| `list()` | GET /tags | 标签列表 |
| `get($id)` | GET /tags/{id} | 标签详情 |
| `create()` | POST /tags | 创建标签 |
| `update($id)` | PATCH /tags/{id} | 编辑标签 |
| `delete($id)` | DELETE /tags/{id} | 删除标签 |
| `allList()` | GET /all/tags | 全部标签（含禁用） |
| `allCreate()` | POST /all/tags | 管理创建标签 |
| `allUpdate($id)` | PATCH /all/tags/{id} | 编辑任意标签 |
| `allDelete($id)` | DELETE /all/tags/{id} | 删除任意标签 |
| `batch()` | POST /all/tags/batch | 批量操作 |

### Users.php

| 方法 | 路由 | 语义 |
|------|------|------|
| `allList()` | GET /all/users | 全部用户列表 |
| `allGet($id)` | GET /all/users/{id} | 获取任意用户 |
| `allUpdate($id)` | PATCH /all/users/{id} | 编辑任意用户 |
| `allDelete($id)` | DELETE /all/users/{id} | 删除任意用户 |
| `batch()` | POST /all/users/batch | 批量操作 |

### Info.php

| 方法 | 路由 | 语义 |
|------|------|------|
| `get()` | GET /users/me | 我的信息 |
| `update()` | PATCH /users/me | 编辑我的信息 |
| `password()` | POST /users/me/password | 修改密码 |
| `email()` | POST /users/me/email | 绑定邮箱 |
| `emailCaptcha()` | POST /users/me/email-captcha | 邮箱验证码 |

### Auth.php

| 方法 | 路由 | 语义 |
|------|------|------|
| `login()` | POST /auth/login | 登录 |
| `register()` | POST /auth/register | 注册 |
| `guest()` | POST /auth/guest | 访客登录 |
| `captcha()` | POST /auth/captcha | 获取验证码 |
| `logout()` | POST /auth/logout | 登出 |
| `check()` | GET /auth/check | Token 校验 |

### Likes.php

| 方法 | 路由 | 语义 |
|------|------|------|
| `list()` | GET /likes | 我的点赞列表 |
| `unlike($id)` | DELETE /likes/{id} | 取消点赞 |

### Roles.php

| 方法 | 路由 | 语义 |
|------|------|------|
| `list()` | GET /all/roles | 角色列表 |
| `get($id)` | GET /all/roles/{id} | 角色详情 |
| `create()` | POST /all/roles | 创建角色 |
| `update($id)` | PATCH /all/roles/{id} | 编辑角色 |
| `delete($id)` | DELETE /all/roles/{id} | 删除角色 |
| `assignPermissions($id)` | POST /all/roles/{id}/permissions | 分配权限 |
| `getRolePermissions($id)` | GET /all/roles/{id}/permissions | 获取角色权限 |

### Permissions.php

| 方法 | 路由 | 语义 |
|------|------|------|
| `list()` | GET /all/permissions | 权限列表 |
| `all()` | GET /all/permissions/all | 全部权限 |

### Config.php

| 方法 | 路由 | 语义 |
|------|------|------|
| `index()` | GET /all/config | 获取配置 |
| `save()` | POST /all/config | 保存配置 |
| `storageChannels()` | GET /all/config/storage-channels | 存储渠道 |
| `testChannel()` | POST /all/config/test-channel | 测试渠道 |
| `channelStats()` | GET /all/config/channel-stats | 渠道统计 |

### System.php

| 方法 | 路由 | 语义 |
|------|------|------|
| `themes()` | GET /all/system/themes | 主题列表 |
| `themeSet()` | POST /all/system/set-theme | 设置主题 |
| `themeConfig()` | POST /all/system/theme-config | 主题配置 |
| `update()` | GET /all/system/update | 系统更新 |

### Dashboard.php

| 方法 | 路由 | 语义 |
|------|------|------|
| `index()` | GET /all/dashboard | 控制台数据 |

### Upload.php

| 方法 | 路由 | 语义 |
|------|------|------|
| `upload()` | POST /files | 上传文件 |
| `list()` | GET /files | 文件列表 |
| `get($id)` | GET /files/{id} | 文件详情 |
| `batch()` | POST /files/batch | 批量操作 |
| `direct()` | POST /files/direct | 直传凭证 |
| `confirm($id)` | PATCH /files/{id}/confirm | 确认直传 |
| `cleanup()` | DELETE /files/expired | 清理过期 |
| `allDelete($id)` | DELETE /all/files/{id} | 删除文件 |

### Theme.php

| 方法 | 路由 | 语义 |
|------|------|------|
| `config()` | GET /theme/config | 主题配置 |

---

## 5. Service 方法对应关系

| 控制器方法 | Service 方法 | 说明 |
|-----------|-------------|------|
| `list()` | `listPublic()` | 公开数据 |
| `listOwn()` | `listOwn()` | 自己的数据 |
| `allList()` | `listAll()` | 全部数据 |
| `get($id)` | `get()` | 获取一条 |
| `allGet($id)` | `getAny()` | 获取任意（含非公开） |
| `create()` | `create()` | 创建 |
| `update($id)` | `update()` | 编辑自己的（受限字段） |
| `allUpdate($id)` | `updateAny()` | 编辑任意（全字段） |
| `delete($id)` | `delete()` / `deleteOwn()` | 删除自己的 |
| `allDelete($id)` | `deleteAny()` | 删除任意 |
| `like($id)` | `like()` | 点赞 |
| `comment($id)` | `comment()` | 评论 |
| `batch()` | `batch()` | 批量 |
| `setting()` | `getSetting()` / `saveSetting()` | 设置 |

---

## 6. Validate 场景名对齐

| 改前 | 改后 |
|------|------|
| `$all_scene['admin']['patch']` | `$all_scene['allUpdate']` |
| `$all_scene['admin']['create']` | `$all_scene['allCreate']` |
| `$all_scene['user']['create']` | `$all_scene['create']` |
| `$all_scene['user']['patch']` | `$all_scene['update']` |
| `$all_scene['admin']['assignPermissions']` | `$all_scene['assignPermissions']` |

---

## 7. RBAC 配置

路由名不再包含角色前缀。RBAC 通过路由 meta 配置角色访问权限：

- **公开路由**：`meta.public=true`，不挂中间件，游客直接访问，不存 `role_permissions` 表
- **鉴权路由**：挂 `JwtAuthCheck + PermissionCheck`，通过 `role_permissions` 表配置角色权限

| 路由名 | 游客 | guest | user | admin | root |
|--------|:----:|:-----:|:----:|:-----:|:----:|
| cards.list | ✅ (公开) | ✅ | ✅ | ✅ | ✅ |
| cards.hot | ✅ (公开) | ✅ | ✅ | ✅ | ✅ |
| cards.get | ✅ (公开) | ✅ | ✅ | ✅ | ✅ |
| cards.images | ✅ (公开) | ✅ | ✅ | ✅ | ✅ |
| cards.create | ❌ | ❌ | ✅ | ✅ | ✅ |
| cards.update | ❌ | ❌ | ✅ | ✅ | ✅ |
| cards.delete | ❌ | ❌ | ✅ | ✅ | ✅ |
| cards.like | ❌ | ❌ | ✅ | ✅ | ✅ |
| cards.allList | ❌ | ❌ | ❌ | ✅ | ✅ |
| cards.allUpdate | ❌ | ❌ | ❌ | ✅ | ✅ |
| cards.allDelete | ❌ | ❌ | ❌ | ✅ | ✅ |
| cards.batch | ❌ | ❌ | ❌ | ✅ | ✅ |
| cards.setting | ❌ | ❌ | ❌ | ✅ | ✅ |

公开路由在后台权限管理页面显示为所有角色"已拥有（公开）"，checkbox 自动勾选且禁用。

---

## 8. 前端适配

### 8.1 API 路径变化

| 改前 | 改后 |
|------|------|
| GET /api/admin/cards | GET /api/all/cards |
| GET /api/public/cards | GET /api/cards |
| GET /api/cards | GET /api/users/me/cards |
| PATCH /api/admin/card | PATCH /api/all/cards/{id} |
| DELETE /api/admin/cards | DELETE /api/all/cards/{id} |
| POST /api/admin/cards/batch | POST /api/all/cards/batch |

### 8.2 hasAdminAccess 判断更新

```typescript
// 改前
hasAdminAccess(): boolean {
    return this.permissions.some(p => p.startsWith('admin.'));
}

// 改后
hasAdminAccess(): boolean {
    return this.permissions.some(p => p.includes('.allList') || p.includes('.allUpdate'));
}
```

---

## 9. 执行顺序

| 批次 | 内容 | 依赖 |
|------|------|------|
| 1 | BaseController 重写 | 无 |
| 2 | Params.php 删除 | 批次 1 |
| 3 | 新建 trait（BatchOperateTrait + FieldsToggleTrait） | 无 |
| 4 | Service 补齐（Cards/Comments/Tags/Users/Dashboard/ChannelTester） | 无 |
| 5 | Validate 场景重命名 | 无 |
| 6 | 控制器合并+扁平化（13 个文件） | 批次 1-5 |
| 7 | 路由重写（9 个文件） | 批次 6 |
| 8 | 删除旧目录+旧路由文件 | 批次 7 |
| 9 | 前端适配（API 路径 + hasAdminAccess） | 批次 7 |
| 10 | 验证（路由列表对比 + curl 全端点） | 全部 |

---

## 10. 影响面

### 不变的部分

- Model 层（10 个文件）
- ApiResponse / ApiException
- index 模块（SSR 渲染层）
- JWT 基础设施（common/jwt/）

### 已实施的变更

- 中间件：路由文件改为 `::class` 引用（非 `new` 实例化）
- RBAC 核心：`checkAccess()` 增加公开路由判断，`getRouteMeta()` 增加 `public` 字段，`getUserPermissions()` 合并公开路由
- RBAC 缓存：`Cache::clear()` → `CacheManager::clearDomain('rbac')`，不再全量清除
- RBAC 角色：硬编码 ID → `config('roles.system_roles.*')` 按 slug 引用
- assignPermissions：写入前自动过滤公开路由 hash（`array_filter` + `getRouteMeta`）
- PermissionCheck：空 routeName 返回 403（非放行）
- 路由 meta：所有 84 条路由补全 `name` + `group`，12 条公开路由标记 `public=true`
- 前端：`hasAdminAccess()` 改为 `.allList`/`.allUpdate` 判断，AssignPermissionsDialog 公开路由只读
- 前端：`userStore` 新增 `roles` + `isRoot()` 方法，`root.vue`/`02.initStore` 改用 slug 判断
- 前端：`users.vue` 角色映射表从硬编码改为 API 动态加载

### 量化

| 指标 | 改前 | 改后 |
|------|------|------|
| 控制器文件数 | 20 | 16（含 trait） |
| 目录层级 | 3 层 | 1 层 |
| 路由文件 | 3 个（按角色） | 9 个（按资源域） |
| 重复代码行 | ~260 | ~0 |
| URL 路径变化 | — | `/admin/*` → `/all/*`，`/public/*` → `/` |
