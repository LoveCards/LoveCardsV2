# Captcha 验证模块设计文档

> 最后更新：2026-05-29

---

## 1. 模块概述

Captcha 是 LoveCards3 的统一验证模块，为业务方提供标准化的验证能力。

### 1.1 两种验证类型

| type | 机制 | generate 做什么 | verify 做什么 | 前端形态 |
|---|---|---|---|---|
| **code** | 秘钥传递（通过外部渠道把秘密发给用户） | 生成秘钥 + 存缓存 + 委托 Sender 投递 | 比对缓存 | 输入框 |
| **captcha** | 人机挑战（前端完成挑战，后端校验） | 返回前端 SDK 配置 / 生成图片 | 调外部 API 校验 / 比对缓存 | SDK 交互组件 或 图片+输入框 |

### 1.2 设计原则

- **与 Sender/Storage 模式对齐**：Factory 自动发现 Driver、ChannelManager 管配置、install 幂等注册
- **无"场景"概念**：中间件直接指定 type，不需要场景→类型的映射层
- **验证码渠道分离**：CodeDriver 只管生成+校验，发送委托 Sender 模块
- **code 类型支持双渠道**：邮箱验证码和短信验证码通过 `captcha.code_channel` 配置切换
- **captcha 类型内 interactive/image 透明切换**：调用方只传 `captcha`，具体 Driver 由配置决定

### 1.3 两层开关

| 层 | 配置项 | 作用 |
|---|---|---|
| 全局开关 | `captcha.code_enabled` | code 类型总开关 |
| 全局开关 | `captcha.captcha_enabled` | captcha 类型总开关 |
| 调用方本地开关 | `user.captcha` | 注册是否需要验证码（Controller/Service 层读取） |

全局开关关闭 → 该类型所有场景不验证。全局开关开启 → 看调用方本地开关。

captcha 类型不需要本地开关——中间件本身就决定是否拦截（挂了就验证，没挂就不验证）。

---

## 2. 目录结构

```
app/api/service/Captcha/
├── Captcha.php                       # Facade：generate() + verify() + driver()
├── CaptchaManager.php                # Admin API：types/drivers/meta/install
├── CaptchaFactory.php                # Driver 自动发现（扫描 Driver/*Driver.php）
├── ChannelManager.php                # 按 type 读默认 Driver slug
├── Contract/
│   ├── CaptchaInterface.php          # generate(params) + verify(params) + meta() + type()
│   └── AbstractDriver.php            # slug + config + type
├── Driver/
│   ├── CodeDriver.php                # type=code（生成+校验，发送委托 Sender）
│   ├── GeetestDriver.php             # type=captcha（极验交互式）
│   └── ImageDriver.php               # type=captcha（GD 图片验证码）
└── Middleware/
    └── CaptchaCheck.php              # 统一验证中间件（替代 GeetestCheck）

config/apps/
├── captcha.php                       # 模块级配置 schema
└── geetest.php                       # 保留，install 会注册到 configs 表

app/api/controller/Captcha/
└── Captcha.php                       # Captcha Admin API 控制器

app/api/route/
└── captcha.php                       # Captcha 路由定义（5 条）
```

---

## 3. 核心接口

### 3.1 CaptchaInterface

```
generate(params): array     ← 生成验证材料
verify(params): bool        ← 校验用户输入
meta(): array               ← Driver 元信息
type(): string              ← 返回所属 type（code / captcha）
```

### 3.2 AbstractDriver

```
属性：slug, config, type
构造：__construct(slug, config, type)
默认实现：meta() 从子类覆盖、type() 从构造参数读取
```

### 3.3 参数约定

**code 类型**：
```
generate: { to, scene?, ttl? }          → { status, msg }
verify:   { key, code, scene? }         → bool
```

**captcha 类型（interactive Driver）**：
```
generate: {}                            → { captcha_id, status, driver }
verify:   { lot_number, captcha_output, pass_token, gen_time }  → bool
```

**captcha 类型（image Driver）**：
```
generate: {}                            → { image_id, image_base64, driver }
verify:   { image_id, code }            → bool
```

`generate()` 返回值统一附带 `driver` 字段，前端据此判断渲染方式。

---

## 4. 架构分层

### 4.1 调用流程

**验证码（code）— Service 层调用**：
```
Session::sendCaptcha($email)
  → Captcha::generate('code', ['to' => $email, 'ttl' => 300])
    → ChannelManager::defaultDriver('code') → 'smtp_code'
    → CaptchaFactory::make('smtp_code') → CodeDriver
    → CodeDriver::generate()
      → 生成验证码 + 存 CacheManager
      → Sender::code('smtp', $email, $code)
```

**人机验证（captcha）— 中间件层调用**：
```
Route::post('cards/:id/comments', ...)
  ->middleware(CaptchaCheck::class, ['type' => 'captcha'])
    → CaptchaCheck::handle()
      → Captcha::isEnabled('captcha') ? 继续 : 跳过
      → Captcha::driver('captcha')
        → ChannelManager::defaultDriver('captcha') → 'geetest_v4'（或 'gd_image'）
        → CaptchaFactory::make('geetest_v4') → GeetestDriver
      → GeetestDriver::verify(...)
        → 调 Geetest API 校验
```

### 4.2 架构图

```
┌──────────────────────────────────────────────────────────────┐
│  调用方                                                       │
│  ├─ Controller/Service：Captcha::generate('code', ...)       │
│  ├─ Middleware：CaptchaCheck(type='captcha')                  │
│  └─ 前端：GET /api/captcha/config                            │
└───────────────────────┬──────────────────────────────────────┘
                        │
                        ▼
┌──────────────────────────────────────────────────────────────┐
│  Captcha Facade                                              │
│  generate(type, params, driver?) → ChannelManager → Factory  │
│  verify(type, params, driver?) → ChannelManager → Factory    │
│  driver(type, driver?) → 返回 Driver 实例                     │
│  isEnabled(type) → 读全局开关                                 │
└──────┬───────────────┬───────────────────┬───────────────────┘
       │               │                   │
       ▼               ▼                   ▼
┌────────────┐  ┌──────────────┐  ┌──────────────────────────┐
│ChannelMgr  │  │CaptchaFactory│  │  CaptchaManager          │
│按type读    │  │扫描 Driver/  │  │  Admin API only          │
│默认Driver  │  │实例化 Driver │  │  types/drivers/meta/     │
└────────────┘  └──────┬───────┘  │  install/config          │
                       │          └──────────────────────────┘
                       ▼
┌──────────────────────────────────────────────────────────────┐
│  Contract 层（CaptchaInterface + AbstractDriver）             │
└───────────────────────┬──────────────────────────────────────┘
                        │
           ┌────────────┼────────────┐
           ▼            ▼            ▼
┌──────────────┐ ┌─────────────┐ ┌──────────────┐
│  CodeDriver  │ │GeetestDriver│ │  ImageDriver │
│  type=code   │ │type=captcha │ │  type=captcha│
│  生成+校验   │ │  交互式校验  │ │  生成图片+比对│
│  发送→Sender │ │             │ │              │
└──────────────┘ └─────────────┘ └──────────────┘
```

---

## 5. 配置体系

### 5.1 模块级配置（config/apps/captcha.php → configs 表 captcha group）

| 配置项 | 默认值 | 说明 |
|---|---|---|
| `captcha.default_code_driver` | `smtp_code` | code 类型默认 Driver |
| `captcha.default_captcha_driver` | `geetest_v4` | captcha 类型默认 Driver |
| `captcha.code_enabled` | `true` | code 类型总开关 |
| `captcha.captcha_enabled` | `true` | captcha 类型总开关 |
| `captcha.code_channel` | `smtp` | code 发送渠道（smtp / sms） |

### 5.2 Driver 级配置（install 从 Driver::meta() 注册到 configs 表）

| group | key | 说明 |
|---|---|---|
| `geetest` | `status` | 极验开关 |
| `geetest` | `id` | Captcha ID |
| `geetest` | `key` | Captcha Key |

ImageDriver / CodeDriver 不需要额外配置。

### 5.3 调用方配置（各自模块的 config）

| 配置项 | 默认值 | 说明 |
|---|---|---|
| `user.captcha` | `false` | 注册验证码开关 |

---

## 6. Driver 实现

### 6.1 能力矩阵

| Driver | slug | type | generate | verify |
|---|---|---|---|---|
| CodeDriver | smtp_code | code | 生成验证码+存缓存+Sender发送 | 比对缓存 |
| GeetestDriver | geetest_v4 | captcha | 返回前端 SDK 配置（captcha_id） | 调极验 API |
| ImageDriver | gd_image | captcha | 生成 GD 图片+存缓存 | 比对缓存 |

### 6.2 CodeDriver

**generate(params)**：
- 生成 6 位随机验证码（大写字母+数字，排除易混淆字符）
- 存入 CacheManager（domain: captcha, key: `Captcha_{scene}_{to}`, ttl: params.ttl 或 300）
- 读 `captcha.code_channel` 配置确定 channelType（默认 smtp）
- 调 `Sender::code(channelType, to, code)` 发送
- 返回 `{ status, msg }`

**verify(params)**：
- 从 CacheManager 读取 `Captcha_{scene}_{key}`
- 比对用户输入的 code（toUpperCase）
- 验证成功后删除缓存
- 返回 bool

**渠道切换**：
- `captcha.code_channel = smtp` → `Sender::code('smtp', email, code)`
- `captcha.code_channel = sms` → `Sender::code('sms', phone, code)`

### 6.3 GeetestDriver

**generate(params)**：
- 从 configs 表读 `geetest.id` 和 `geetest.status`
- 返回 `{ captcha_id, status, driver: 'geetest_v4' }`

**verify(params)**：
- 从 configs 表读 `geetest.id` 和 `geetest.key`
- HMAC-SHA256 签名 lot_number
- POST 到 `http://gcaptcha4.geetest.com/validate?captcha_id={id}`
- 检查返回 `result === 'success'`
- 返回 bool

### 6.4 ImageDriver

**generate(params)**：
- 生成 4-6 位随机字符串（排除易混淆字符 I/l/O/0/1）
- 用 GD 库生成干扰图片：随机噪点、干扰线、旋转字符、背景色
- 存入 CacheManager（domain: captcha, key: `Captcha_image_{image_id}`, ttl: 120）
- 返回 `{ image_id, image_base64, driver: 'gd_image' }`

**verify(params)**：
- 从 CacheManager 读取 `Captcha_image_{image_id}`
- 比对用户输入（toUpperCase）
- 验证成功后删除缓存
- 返回 bool

---

## 7. 中间件

### 7.1 CaptchaCheck

**职责**：请求级验证拦截，用于 captcha 类型。

**参数**：通过路由中间件参数传入 `type`。

**逻辑**：
```
handle(request, next, type):
    if type 为空 → 跳过
    if !Captcha::isEnabled(type) → 跳过
    driver = Captcha::driver(type)
    if driver 为 null → 跳过
    params = collectParams(request, type)
    if !driver.verify(params) → 返回 401 "验证失败"
    next(request)
```

### 7.2 路由注册

```php
// 注册 — 人机验证
Route::post('session/register', ...)
    ->middleware(CaptchaCheck::class, ['type' => 'captcha']);

// 评论 — 人机验证
Route::post('cards/:id/comments', ...)
    ->middleware(CaptchaCheck::class, ['type' => 'captcha']);

// 发卡片 — 人机验证（新增，之前缺失）
Route::post('cards', ...)
    ->middleware(CaptchaCheck::class, ['type' => 'captcha']);
```

### 7.3 Code 类型不走中间件

验证码的"发送"是用户主动行为（点"发送验证码"按钮），有自己的路由 `POST /api/session/captcha`，由 Controller 调用 `Captcha::generate()`。校验在 Controller/Service 层完成，不走中间件。

---

## 8. Code 类型双渠道

### 8.1 邮箱验证码

```
Captcha::generate('code', ['to' => $email, 'ttl' => 300])
  → CodeDriver::generate()
    → 生成验证码 + 存缓存
    → Sender::code('smtp', $email, $code)
```

### 8.2 短信验证码

```
Captcha::generate('code', ['to' => $phone, 'ttl' => 300])
  → CodeDriver::generate()
    → 生成验证码 + 存缓存
    → Sender::code('sms', $phone, $code)
```

---

## 9. Admin API

| 方法 | 路径 | 说明 | RBAC |
|---|---|---|---|
| GET | /api/all/captcha/types | 列出所有 type | captcha.types |
| GET | /api/all/captcha/drivers | 列出所有已注册 Driver | captcha.drivers |
| GET | /api/all/captcha/:slug/meta | 获取 Driver 配置 schema | captcha.meta |
| POST | /api/all/captcha/install | 扫描 Driver 并注册配置 | captcha.install |
| GET | /api/captcha/config | 返回前端验证配置 | 公开 |

---

## 10. 前端适配

### 10.1 Geetest 配置读取

**旧方式**（Theme API core group，路径错误永远 undefined）：
```js
req.data.system.config.file.Geetest.Id
```

**新方式**（Captcha API）：
```js
this.RequestApiUrl('get', 'CaptchaConfig', undefined).then(req => {
    this.config.geetest4.CaptchaId = req.data.geetest?.id || '';
    this.config.geetest4.CaptchaStatus = Number(req.data.geetest?.status || 0);
});
```

### 10.2 Admin 配置页

**新建 `/apps/captcha/config`**：
- 按 type（code/captcha）分组展示 Driver 卡片
- 每种 type 可设默认 Driver
- 扫描渠道按钮（调用 install API）
- 基础设置页：开关 + 渠道选择

**系统配置页 `/apps/system`**：
- 移除 tab2（极验配置），迁到 Captcha 配置页

---

## 11. 与现有模块对比

| 维度 | Storage | Sender | Captcha |
|---|---|---|---|
| Facade | Storage::upload() | Sender::code() / notify() | Captcha::generate() / verify() |
| Factory | StorageFactory | SenderFactory | CaptchaFactory |
| ChannelManager | 按 channel 读 Driver | 按 channelType 读 Driver | 按 type 读 Driver |
| Driver 数量 | 3 | 2 | 3 |
| Middleware | 无 | 无 | CaptchaCheck |
| 配置管理 | storage_* group | sender_* group | captcha + geetest |

---

## 12. 已修复的附带问题

| 问题 | 修复 |
|---|---|
| GeetestCheck Config 路径错误 | GeetestDriver 从 configs 表读 `geetest.*` |
| 前端读不到 Geetest 配置 | 新增 `GET /api/captcha/config` 端点 |
| 卡片创建无验证 | cards.php 路由加 CaptchaCheck 中间件 |
| `user.captcha` 描述错误 | "登录验证码" → "注册验证码开关" |
| Session captcha 500 | 加参数校验 + 改用 `BaseController::param()` |
| Profile emailCaptcha 500 | `createError` → `createBadRequest` |
| login/register 无参数校验 | 加前置 null 检查 |

---

## 13. 实施清单

| 组件 | 文件 | 状态 |
|---|---|---|
| Contract | `CaptchaInterface.php` + `AbstractDriver.php` | ✅ |
| CodeDriver | `Driver/CodeDriver.php` | ✅ |
| GeetestDriver | `Driver/GeetestDriver.php` | ✅ |
| ImageDriver | `Driver/ImageDriver.php` | ✅ |
| ChannelManager | `ChannelManager.php` | ✅ |
| CaptchaFactory | `CaptchaFactory.php` | ✅ |
| CaptchaManager | `CaptchaManager.php` | ✅ |
| Captcha Facade | `Captcha.php` | ✅ |
| CaptchaCheck 中间件 | `Middleware/CaptchaCheck.php` | ✅ |
| 配置 schema | `config/apps/captcha.php` | ✅ |
| 路由 | `route/captcha.php`（5 条） | ✅ |
| 控制器 | `controller/Captcha/Captcha.php` | ✅ |
| Session 迁移 | `Session.php` 改调 Captcha Facade | ✅ |
| Profile 迁移 | `Profile.php` 改调 Captcha Facade | ✅ |
| 路由更新 | session.php / comments.php / cards.php | ✅ |
| 前端 Base.js | 新增 CaptchaInit() + CaptchaConfig API | ✅ |
| 前端页面 | captcha/config/index.vue + [slug].vue | ✅ |
| 前端 API | captcha.ts | ✅ |
| 系统配置页 | 移除 tab2（极验配置） | ✅ |
| 侧边栏 | 新增"验证"菜单组 | ✅ |
| 删除 | common/captcha/Code.php + GeetestCheck.php | ✅ |
| 修复 | user.php description / Profile 500 bug | ✅ |
