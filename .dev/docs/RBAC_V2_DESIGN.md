# RBAC v2 + API 架构重构方案

> 状态：设计完成，待执行
> 日期：2026-06-01
> 关联任务：ToDo.md #2 API 目录结构 → RESTful API

---

## 一、设计原则

1. **API 层描述"能做什么"，不关心角色**
2. **RBAC 是独立模块，管理能力 × 角色映射，不感知路由/控制器**
3. **"精准/全部"取代"用户/管理员"** — 纯数据范围描述，无角色概念
4. **归属 = 资源 `user_id` == 请求者 `uid`**
5. **模块化高内聚，低熵增，少设计解决多问题**

---

## 二、能力命名规范

```
{resource}.{action}       → 精准（归属约束）
{resource}.{action}.all   → 全部（无归属约束）
```

### 全量能力表

| 资源 | 精准能力 | 全部能力 |
|------|---------|---------|
| cards | read, create, update, delete, approve, pin | read.all, update.all, delete.all, approve.all, pin.all |
| comments | read, create, update, delete | read.all, update.all, delete.all |
| tags | read, create, update, delete | read.all, update.all, delete.all |
| users | read, update, delete | read.all, update.all, delete.all |
| files | upload, read, delete | read.all, delete.all |
| likes | create, read, delete | — |
| roles | read, create, update, delete, assign | — |
| permissions | read | — |
| config | read, update, init, reload, register, deleteKey | — |
| storage | read, install, test | — |
| sender | read, install, test | — |
| captcha | read, install | — |
| theme | read, update, upload, delete, freeze, activate | — |
| dashboard | read | — |
| session | login, register, guest, logout, check, captcha | — |

### 设计决策

- **不设 batch 能力**：batch 复用单条操作能力（如 `cards.delete`），Service 层根据 method 检查
- **不设能力继承**：`cards.update` 和 `cards.update.all` 是独立能力，RBAC 分配时手动勾选
- **不设 `cards.create.all`**：创建卡片无归属概念，一个能力足够

---

## 三、RBAC 表结构

### 新建 `role_capabilities`

```sql
CREATE TABLE role_capabilities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    capability VARCHAR(100) NOT NULL,
    UNIQUE KEY uk_role_cap (role_id, capability),
    KEY idx_role_id (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 旧表 `role_permissions`

保留不删除，靠 git 兜底。验证完成后手动清理。

---

## 四、请求处理流程

```
请求 PATCH /api/cards/14

1. ThemeBoot 中间件
   → 非 /api 前缀，放行到 API 路由

2. JwtAuthCheck 中间件
   → 解析 token → uid=1, rolesId=[3]
   → 注入 request()->uid, request()->user, request()->rolesId

3. PermissionCheck 中间件
   → 路由名: cards.update
   → 路由 meta.caps: ['cards.update', 'cards.update.all']
   → 查询 role_capabilities 表，uid=1 的能力列表
   → caps = ['cards.read', 'cards.create', 'cards.update', 'cards.delete', ...]
   → cards.update 在 caps 中? → ✅ 放行
   → 注入 request()->caps = caps

4. Cards::update(14) 控制器
   → 参数校验
   → CardsService::updateCard($data, uid=1, caps=[...])

5. CardsService::updateCard()
   → self::guard(14, uid=1, caps, 'cards.update')
     → cards.update.all 在 caps 中? → ❌
     → cards.update 在 caps 中? → ✅
     → 查 cards 表 id=14 → user_id=1
     → 1 == 1 → ✅ 归属通过
   → CardsModel::update($data)

6. ApiResponse::createNoContent() → 204
```

---

## 五、中间件设计

### PermissionCheck 中间件（重写）

```php
class PermissionCheck
{
    public function handle($request, \Closure $next)
    {
        $routeName = request()->rule()->getName();
        $routeMeta = request()->rule()->getOption('meta');
        $requiredCaps = $routeMeta['caps'] ?? [$routeName];

        // 获取用户能力
        $caps = RBAC::getUserCapabilities($request->rolesId ?? []);

        // 检查是否满足任一能力
        $hasAccess = false;
        foreach ($requiredCaps as $cap) {
            if (in_array($cap, $caps)) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            return ApiResponse::createForbidden('权限不足');
        }

        // 注入能力列表到 request
        $request->caps = $caps;

        return $next($request);
    }
}
```

### 路由 meta 配置格式

```php
Route::patch('cards/:id', 'Content.Cards/update')
    ->name('cards.update')
    ->option('meta', [
        'name' => '编辑卡片',
        'group' => '卡片',
        'caps' => ['cards.update', 'cards.update.all'],
    ]);
```

### 公开路由

```php
Route::get('cards/hot', 'Content.Cards/hotList')
    ->name('cards.hot')
    ->option('meta', [
        'name' => '热门卡片',
        'group' => '卡片',
        'public' => true,
    ]);
```

### batch 路由

只挂 `JwtAuthCheck`，不挂 `PermissionCheck`：

```php
Route::post('cards/batch', 'Content.Cards/batch')
    ->name('cards.batch')
    ->middleware(JwtAuthCheck::class)
    ->option('meta', ['name' => '卡片批量操作', 'group' => '卡片']);
```

### users/me 路由

只挂 `JwtAuthCheck`，不做能力检查：

```php
Route::get('users/me', 'User.Users/me')
    ->name('users.me')
    ->middleware(JwtAuthCheck::class)
    ->option('meta', ['name' => '个人信息', 'group' => '用户', 'public' => true]);
```

---

## 六、OwnershipGuard Trait

### 位置

`common/support/OwnershipGuard.php`（替代 `common/service/Traits/Ownable.php`）

### 接口

```php
trait OwnershipGuard
{
    // 子类声明
    protected static string $guardModel;                    // 资源模型类
    protected static string $guardField = 'user_id';        // 归属字段

    /**
     * 验证能力 + 归属（单个资源）
     *
     * 逻辑：
     * 1. baseCap.all 在 caps 中 → 放行（跳过归属）
     * 2. baseCap 在 caps 中 → 检查归属（user_id == uid）
     * 3. 都没有 → 403
     */
    protected static function guard(
        int $id, int $uid, array $caps, string $baseCap
    ): void;

    /**
     * 验证能力 + 归属（批量资源）
     *
     * 逐条检查归属，部分无权则 403
     */
    protected static function guardBatch(
        array $ids, int $uid, array $caps, string $baseCap
    ): void;
}
```

### 使用示例

```php
class CardsService
{
    use OwnershipGuard;
    protected static string $guardModel = CardsModel::class;

    public static function updateCard(array $data, int $uid, array $caps): void
    {
        self::guard($data['id'], $uid, $caps, 'cards.update');
        CardsModel::update($data);
    }

    public static function deleteCards(array $ids, int $uid, array $caps): void
    {
        self::guardBatch($ids, $uid, $caps, 'cards.delete');
        CardsModel::destroy($ids);
    }
}
```

---

## 七、能力检查分层

| 场景 | 能力检查位置 | 归属检查位置 |
|------|------------|------------|
| 普通 CRUD | 中间件 PermissionCheck | Service `guard()` |
| batch 操作 | Service `guardBatch()` | Service `guardBatch()` |
| `users/me` 系列 | 不检查（只需 token） | 不检查 |

**batch 为什么在 Service 层检查能力？**

因为 batch 的 `method` 参数决定了需要什么能力（delete → `cards.delete`，approve → `cards.approve`），这是中间件无法预先知道的运行时参数。

---

## 八、batch 操作设计

不设独立 batch 能力。路由只检查 token，能力在 Service 层根据 method 检查。

```php
// 控制器
public function batch() {
    $params = $this->param(Common::class, Common::$all_scene['BatchOperate'], request()->param());
    CardsService::batchOperate($params['method'], $params['ids'], request()->uid, request()->caps);
    return ApiResponse::createNoContent();
}

// Service
public static function batchOperate(string $method, array $ids, int $uid, array $caps): void
{
    $opCaps = [
        'top'       => 'cards.pin',
        'unset_top' => 'cards.pin',
        'approve'   => 'cards.approve',
        'ban'       => 'cards.approve',
        'hide'      => 'cards.update',
        'unhide'    => 'cards.update',
        'delete'    => 'cards.delete',
    ];

    $cap = $opCaps[$method] ?? null;
    if (!$cap) throw ApiException::badRequest('不支持的操作');

    // 能力 + 归属一体化检查
    self::guardBatch($ids, $uid, $caps, $cap);

    // 执行
    match ($method) {
        'top', 'unset_top' => FieldsToggle::toggle(CardsModel::class, 'is_top', $ids, [0, 1]),
        'approve' => FieldsToggle::toggle(CardsModel::class, 'status', $ids, [3, 0]),
        'delete' => CardsModel::destroy($ids),
    };
}
```

---

## 九、RBAC 服务重构

### RBAC.php 变更

| 旧方法 | 新方法 | 说明 |
|--------|--------|------|
| `checkAccess($rolesId, $routeName, $method)` | 保持 | 内部改为查 `role_capabilities` |
| `getRoleHashes($roleId)` | `getRoleCapabilities($roleId)` | 改名 |
| `getRoleHashSet($roleId)` | `getRoleCaps($roleId)` | 改名 |
| `getRouteMeta()` | 保持 | 扫描路由 meta |
| `getUserPermissions($rolesId)` | `getUserCapabilities($rolesId)` | 改名，返回 capability 字符串数组 |

### getUserCapabilities 逻辑

```php
public static function getUserCapabilities(array $rolesId): array
{
    if (empty($rolesId)) return [];

    // root 角色拥有所有能力
    if (in_array(config('system.system_roles.root'), $rolesId)) {
        return array_keys(self::getAllCapabilities());
    }

    return CacheManager::get('rbac', 'caps:' . md5(implode(',', $rolesId)), function () use ($rolesId) {
        return Db::table('role_capabilities')
            ->whereIn('role_id', $rolesId)
            ->distinct()
            ->column('capability');
    }, CacheManager::TTL_LONG);
}
```

### Roles Service 变更

| 旧方法 | 新方法 | 说明 |
|--------|--------|------|
| `assignPermissions($roleId, $hashes)` | `assignCapabilities($roleId, $caps)` | 参数改为 capability 字符串数组 |
| `getRolePermissionHashes($roleId)` | `getRoleCapabilities($roleId)` | 改名 |

---

## 十、路由合并规则

### 合并前 → 合并后

```
GET  /cards         + GET  /all/cards       → GET  /cards
GET  /cards/:id     + GET  /all/cards/:id   → GET  /cards/:id
PATCH /cards/:id    + PATCH /all/cards/:id  → PATCH /cards/:id
DELETE /cards/:id   + DELETE /all/cards/:id → DELETE /cards/:id
POST /cards/batch   (无 all/ 版本)          → POST /cards/batch（保持）

GET  /comments      + GET  /all/comments    → GET  /comments（如有）
PATCH /comments/:id + PATCH /all/comments/:id → PATCH /comments/:id
DELETE /comments/:id + DELETE /all/comments/:id → DELETE /comments/:id

（tags、users、files、config、roles 同理）
```

### 路由总数

~136 条 → ~55 条

---

## 十一、控制器合并

### 合并规则

```
list() + allList()     → list()
get() + allGet()       → get()
update() + allUpdate() → update()
delete() + allDelete() → delete()
```

### 控制器示例（Cards）

```php
class Cards extends BaseController
{
    use BatchOperateTrait;

    protected function getBatchService(): string
    {
        return CardsService::class;
    }

    public function list()
    {
        $params = $this->paramIndex(Request::param());
        $result = CardsService::list($params, request()->caps ?? []);
        return ApiResponse::createOk($result);
    }

    public function get($id)
    {
        $result = CardsService::get((int) $id, request()->caps ?? []);
        return ApiResponse::createOk($result);
    }

    public function create()
    {
        $params = $this->param(CardsValidate::class, CardsValidate::$all_scene['create'], Request::param());
        $params['user_id'] = request()->uid;
        $params['post_ip'] = request()->ip();
        $params['status'] = ConfigService::get('cards.approve') ? 3 : 0;

        $cardId = CardsService::createCard($params);

        if (ConfigService::get('cards.approve')) {
            return ApiResponse::createCreated();
        }
        return ApiResponse::createOk(['id' => $cardId]);
    }

    public function update($id)
    {
        $params = $this->param(CardsValidate::class, CardsValidate::$all_scene['update'], Request::param());
        $params['id'] = (int) $id;
        CardsService::updateCard($params, request()->uid, request()->caps);
        return ApiResponse::createNoContent();
    }

    public function delete($id)
    {
        CardsService::deleteCards([(int) $id], request()->uid, request()->caps);
        return ApiResponse::createNoContent();
    }

    public function like($id)
    {
        $likes = LikesService::like('card', (int) $id, request()->uid, request()->ip());
        return ApiResponse::createOk(['likes' => $likes]);
    }

    public function listOwn()
    {
        $params = $this->paramIndex(Request::param());
        $result = CardsService::listOwn($params, request()->uid);
        return ApiResponse::createOk($result);
    }

    public function batch()
    {
        $params = $this->param(Common::class, Common::$all_scene['BatchOperate'], request()->param());
        CardsService::batchOperate($params['method'], $params['ids'], request()->uid, request()->caps);
        return ApiResponse::createNoContent();
    }
}
```

---

## 十二、Service 层示例

```php
class CardsService
{
    use OwnershipGuard;
    protected static string $guardModel = CardsModel::class;

    public static function list(array $params, array $caps): array
    {
        if (!in_array('cards.read.all', $caps)) {
            $params['where']['status'] = 0;
        }
        $params['search_default_key'] = 'content';
        return ModelList::make(CardsModel::class)->getPaginate($params)->toArray();
    }

    public static function get(int $id, array $caps): array
    {
        $card = CardsModel::find($id);
        if (!$card) throw ApiException::notFound('卡片不存在');

        if (!in_array('cards.read.all', $caps) && $card->status !== 0) {
            throw ApiException::notFound('卡片不存在');
        }

        return $card->toArray();
    }

    public static function updateCard(array $data, int $uid, array $caps): void
    {
        self::guard($data['id'], $uid, $caps, 'cards.update');
        CardsModel::update($data);
    }

    public static function deleteCards(array $ids, int $uid, array $caps): void
    {
        self::guardBatch($ids, $uid, $caps, 'cards.delete');
        CardsModel::destroy($ids);
    }

    public static function batchOperate(string $method, array $ids, int $uid, array $caps): void
    {
        $opCaps = [
            'top'       => 'cards.pin',
            'unset_top' => 'cards.pin',
            'approve'   => 'cards.approve',
            'ban'       => 'cards.approve',
            'delete'    => 'cards.delete',
        ];

        $cap = $opCaps[$method] ?? null;
        if (!$cap) throw ApiException::badRequest('不支持的操作');

        self::guardBatch($ids, $uid, $caps, $cap);

        match ($method) {
            'top', 'unset_top' => FieldsToggle::toggle(CardsModel::class, 'is_top', $ids, [0, 1]),
            'approve' => FieldsToggle::toggle(CardsModel::class, 'status', $ids, [3, 0]),
            'delete' => CardsModel::destroy($ids),
        };
    }
}
```

---

## 十三、角色能力分配示例

| 能力 | 游客 | 普通用户 | 发布者 | 审核员 | 管理员 |
|------|:----:|:-------:|:-----:|:-----:|:-----:|
| cards.read | ✅ | ✅ | ✅ | ✅ | ✅ |
| cards.read.all | ❌ | ❌ | ❌ | ✅ | ✅ |
| cards.create | ❌ | ✅ | ✅ | ❌ | ✅ |
| cards.update | ❌ | ❌ | ✅ | ❌ | ❌ |
| cards.update.all | ❌ | ❌ | ❌ | ❌ | ✅ |
| cards.delete | ❌ | ❌ | ✅ | ❌ | ❌ |
| cards.delete.all | ❌ | ❌ | ❌ | ❌ | ✅ |
| cards.approve | ❌ | ❌ | ❌ | ✅ | ✅ |
| cards.pin | ❌ | ❌ | ✅ | ❌ | ❌ |
| cards.pin.all | ❌ | ❌ | ❌ | ❌ | ✅ |

---

## 十四、代码变更清单

### 新建文件

| 文件 | 说明 |
|------|------|
| `common/support/OwnershipGuard.php` | 归属守卫 Trait |
| `api/model/RoleCapabilities.php` | 能力表模型 |

### 重写文件

| 文件 | 变更 |
|------|------|
| `api/route/*.php`（16 个） | 合并路由，新增 meta.caps |
| `api/middleware/PermissionCheck.php` | 读 meta.caps + 注入 request->caps |
| `api/service/Rbac/RBAC.php` | hash → capability |
| `api/service/Rbac/Roles.php` | assignPermissions → assignCapabilities |
| `api/controller/Rbac/Roles.php` | reseed() 移入 Service |
| `api/controller/Rbac/Permissions.php` | 分页逻辑移入 Service |
| `api/controller/Content/Cards.php` | 合并 list/allList 等 |
| `api/controller/Content/Comments.php` | 同上 |
| `api/controller/Content/Tags.php` | 同上 |
| `api/controller/User/Users.php` | 同上 |
| `api/service/Content/Cards.php` | 接收 $caps，使用 OwnershipGuard |
| `api/service/Content/Comments.php` | 同上 |
| `api/service/Content/Tags.php` | 同上 |

### 删除文件

| 文件 | 说明 |
|------|------|
| `common/service/Traits/Ownable.php` | 被 OwnershipGuard 替代 |

### 数据库变更

| 操作 | 说明 |
|------|------|
| CREATE TABLE `role_capabilities` | 新表 |
| SEED | 生成能力分配种子数据 |

### 前端 Admin 变更

- API 路径：部分 `/all/xxx` → `/xxx`
- 权限管理：hash 选择器 → capability 字符串选择器（**标记为后续任务**）

### SDK 变更

- 管理员 getter 合并到主 getter

---

## 十五、执行顺序

| 阶段 | 内容 | 工作量 |
|------|------|--------|
| 1 | Common 重构：OwnershipGuard 迁移，Ownable 删除 | S |
| 2 | RBAC 服务重构：新建表，RBAC.php + Roles.php 重写 | M |
| 3 | PermissionCheck 中间件重写 | S |
| 4 | 路由重写：16 个文件 | L |
| 5 | 控制器合并 + Service 层接收 $caps | L |
| 6 | Seed 数据 | S |
| 7 | SDK 适配 | S |
| 8 | 前端权限管理界面 | **后续任务** |

---

## 十六、遗留项

| 项 | 状态 |
|----|------|
| 前端 Admin 权限管理界面（hash → capability） | 后续任务 |
| 旧表 `role_permissions` 清理 | 验证完成后手动删除 |
| Model 层重构（ToDo #1） | 按原计划 |
| Common 重新设计（ToDo #3） | 与本方案阶段 1 合并执行 |
