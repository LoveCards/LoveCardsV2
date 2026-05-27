# Sender 模块设计文档

> 最后更新：2026-05-28

---

## 1. 模块概述

Sender 是 LoveCards3 的统一消息发送模块，为业务方提供标准化的消息投递能力。业务方调用一个方法即可发送消息，不需要关心底层用的是 SMTP 邮件、短信还是 Webhook。

### 1.1 核心职责

- 封装多渠道消息发送（SMTP、短信、Webhook）
- 提供两种消息类型：验证码（code）和通知（notify）
- 按渠道类型管理默认 Driver
- 模板化通知消息内容
- 支持插件化驱动扩展

### 1.2 设计原则

- **Storage 模式对齐**：架构与 Storage 模块一致——Factory 自动发现 Driver、ChannelManager 管配置、install 幂等注册
- **渠道类型 ≠ Driver slug**：`Sender::code('smtp', ...)` 的第一个参数是渠道类型，不是具体的 Driver slug
- **两种消息类型**：code（验证码，结构固定）+ notify（通知，模板驱动），不扩展更多类型
- **模板 = .txt 文件 + 魔法变量**：统一存储，Driver 发送时按需渲染
- **工厂模式**：当前用工厂模式，预留事件驱动迁移能力

---

## 2. 概念模型

```
渠道类型（Channel Type）     smtp / sms / webhook / ...
    ↓
Driver slug（具体实现）      email / aliyun_sms / tencent_sms / ...
    ↓
消息类型（Message Type）     code / notify
    ↓
模板（仅 notify）            template/{channelType}_{scene}.txt
```

### 2.1 渠道类型

渠道类型是对消息投递方式的抽象分类。一个渠道类型下可以有多个 Driver。

| 渠道类型 | 说明 | 当前 Driver |
|---|---|---|
| smtp | SMTP 邮件 | SmtpDriver（slug: email） |
| sms | 短信 | AliyunSmsDriver（slug: aliyun_sms） |
| webhook | HTTP 回调 | 待实现 |

### 2.2 消息类型

只有两种，不扩展。

| 消息类型 | 说明 | 数据结构 |
|---|---|---|
| code | 验证码 | channelType + to + code + expire |
| notify | 通知 | channelType + to + template + vars |

- **code**：结构固定，内容简单（"您的验证码是 X，N 分钟内有效"）。SMTP 渠道通过模板文件渲染，SMS 渠道透传变量给服务商模板。
- **notify**：内容随场景变化，通过 .txt 模板文件定义内容，Driver 发送时读取模板并替换变量。

### 2.3 Driver

Driver = 渠道类型 + 渠道配置 + 支持的消息类型。

每个 Driver 声明：
- `meta()`：渠道类型、配置字段 schema、名称、图标
- `supportedTypes()`：支持哪些消息类型
- `send(Message)`：发送逻辑

### 2.4 模板

模板仅用于 notify 类型消息（code 类型不需要独立模板文件，SMTP 的验证码内容由 `smtp_code.txt` 定义，SMS 的验证码内容由服务商模板决定）。

模板文件命名规则：`{channelType}_{scene}.txt`

变量语法：`{变量名}`

---

## 3. 目录结构

```
app/api/service/Sender/
├── Sender.php                      # Facade：code() + notify() + dispatch()
├── SenderManager.php               # Admin API：types/meta/channels/install/testChannel/templates
├── SenderFactory.php               # Driver 自动发现（扫描 Driver/*Driver.php）
├── ChannelManager.php              # 渠道配置读取 + 按渠道类型读默认 Driver
├── Contract/
│   ├── SenderInterface.php         # Driver 契约：send(Message) + meta() + supportedTypes()
│   ├── AbstractDriver.php          # Driver 基类：config/slug/channelType + renderTemplate()
│   ├── Message.php                 # 消息值对象：code() + notify() 两种构造
│   └── SendResult.php              # 发送结果值对象：success/channelType/messageId/error
├── Driver/
│   ├── SmtpDriver.php              # SMTP 邮件 Driver（支持 code + notify）
│   └── AliyunSmsDriver.php         # 阿里云短信 Driver（仅支持 code）
└── template/
    └── smtp_code.txt               # SMTP 验证码模板

app/api/controller/Sender/
└── Sender.php                      # Sender Admin API 控制器

app/api/route/
└── sender.php                      # Sender 路由定义（6 条，全部注册 RBAC）

config/apps/
├── sender.php                      # Sender 全局配置 schema（default_smtp/default_sms/default_webhook）
├── sender_email.php                # SMTP 渠道配置 schema
└── sender_sms.php                  # SMS 渠道配置 schema
```

---

## 4. 接口设计

### 4.1 SenderInterface

```php
interface SenderInterface
{
    public function send(Message $message): SendResult;
    public static function meta(): array;
    public static function supportedTypes(): array;
}
```

### 4.2 AbstractDriver

```php
abstract class AbstractDriver implements SenderInterface
{
    protected array $config;
    protected string $channelSlug;
    protected string $channelType;

    public function __construct(string $slug, array $config, string $channelType);

    // 默认实现：从类名推导 channelType（SmtpDriver → smtp）
    public static function meta(): array;

    // 默认实现：['code', 'notify']
    public static function supportedTypes(): array;

    // 读模板文件 + str_replace 变量
    protected function renderTemplate(string $template, array $vars): string;
}
```

### 4.3 Message

```php
class Message
{
    public string $channelType;     // smtp / sms / webhook
    public string|array $to;        // 收件人
    public string $type;            // code / notify
    public string $code;            // code 类型：验证码
    public int $expire;             // code 类型：过期时间（分钟）
    public ?string $template;       // notify 类型：模板名
    public array $vars;             // notify 类型：模板变量
    public string $body;            // notify 类型：直接内容（不使用模板时）

    public static function code(string $channelType, string $to, string $code, int $expire = 5): self;
    public static function notify(string $channelType, string $to, string $template, array $vars = []): self;
}
```

### 4.4 SendResult

```php
class SendResult
{
    public bool $success;
    public string $channelType;
    public ?string $messageId;
    public ?string $error;

    public static function ok(string $channelType, ?string $messageId = null): self;
    public static function fail(string $channelType, string $error): self;
}
```

---

## 5. 架构分层

### 5.1 调用流程

```
业务方（Session / Profile / ...）
  │
  ▼
Sender::code('smtp', $email, 'ABC123')
  │
  ▼
Sender::dispatch(Message, driverSlug?)
  ├─ driverSlug 有值 → SenderFactory::make(driverSlug)
  ├─ driverSlug 无值 → ChannelManager::getDefault('smtp') → 得到 slug → SenderFactory::make(slug)
  ├─ 校验 supportedTypes
  └─ $driver->send(Message)
  │
  ▼
SmtpDriver::send(Message)
  ├─ type === 'code' → sendCode() → renderTemplate('code', vars) → doSend()
  └─ type === 'notify' → sendNotify() → renderTemplate(scene, vars) 或 body → doSend()
  │
  ▼
SendResult::ok('smtp') 或 SendResult::fail('smtp', error)
```

### 5.2 架构图

```
┌─────────────────────────────────────────────────────────┐
│                    业务方（调用者）                       │
│              Sender::code() / Sender::notify()          │
└────────────────────────┬────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│                    Sender Facade                         │
│         dispatch() → ChannelManager → Factory → Driver   │
└──────┬──────────────────┬───────────────────┬───────────┘
       │                  │                   │
       ▼                  ▼                   ▼
┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐
│ChannelManager│  │SenderFactory │  │  SenderManager       │
│按渠道类型读   │  │扫描 Driver/  │  │  Admin API only      │
│默认 Driver   │  │实例化 Driver │  │  types/meta/channels  │
└──────────────┘  └──────┬───────┘  │  install/templates    │
                         │          │  testChannel          │
                         ▼          └──────────────────────┘
┌─────────────────────────────────────────────────────────┐
│              Contract 层（接口 + 基类）                   │
│   SenderInterface / AbstractDriver / Message / SendResult│
└────────────────────────┬────────────────────────────────┘
                         │
            ┌────────────┼────────────┐
            ▼                         ▼
┌───────────────────┐      ┌───────────────────┐
│   SmtpDriver      │      │  AliyunSmsDriver   │
│   channelType=smtp│      │  channelType=sms   │
│   code + notify   │      │  code only         │
└───────────────────┘      └───────────────────┘
```

---

## 6. 默认机制

### 6.1 设计

按渠道类型设默认 Driver，与 Storage 模块一致。

```
configs 表：
sender.default_smtp  = 'email'       ← SMTP 渠道的默认 Driver slug
sender.default_sms   = 'sms'         ← SMS 渠道的默认 Driver slug
sender.default_webhook = ''           ← Webhook 渠道（未配置）
```

### 6.2 解析链

```
Sender::code('smtp', ...)
  → ChannelManager::getDefault('smtp')
    → 读 configs 表 sender.default_smtp → 值为 'email'
    → 验证 slug 'email' 存在 → 返回 'email'
  → SenderFactory::make('email')
    → ChannelManager::getBySlug('email') → 读 sender_email 配置
    → $channel['driver'] = 'smtp' → 匹配 SmtpDriver
    → meta()['channelType'] = 'smtp'
    → new SmtpDriver('email', $config, 'smtp')
```

### 6.3 回退机制

如果 `sender.default_smtp` 未配置或值无效：
1. 遍历所有已加载的渠道，找 `channelType` 匹配的第一个
2. 如果仍未找到，抛出异常"未配置 {channelType} 类型的发送渠道"

无参调用 `getDefault()` 时回退到 `'email'`。

---

## 7. 配置说明

### 7.1 配置体系

Driver 配置通过 API 注册到 `configs` 表，不依赖外部文件。

注册流程：
```
POST /api/all/sender/install
  → SenderFactory 扫描 Driver/*.php
  → 每个 Driver::meta() → 转换为 schema 格式
  → ConfigService::register('sender_' . type, $schema) → seed SQL
```

install 是幂等的——已存在的 key 跳过，只插入新 Driver 的配置。

### 7.2 全局配置 schema（config/apps/sender.php）

| key | type | default | description |
|-----|------|---------|-------------|
| default_smtp | string | email | SMTP 渠道默认 Driver |
| default_sms | string | sms | SMS 渠道默认 Driver |
| default_webhook | string | | Webhook 渠道默认 Driver |

### 7.3 SMTP 渠道配置 schema（config/apps/sender_email.php）

| key | type | default | description |
|-----|------|---------|-------------|
| driver | string | smtp | 驱动类型 |
| host | string | smtp.qq.com | SMTP 服务器 |
| port | int | 465 | 端口 |
| addr | string | | 发件邮箱 |
| pass | string | | 邮箱密码/授权码 |
| name | string | | 发件人名称 |
| security | string | ssl | 加密方式 |

### 7.4 SMS 渠道配置 schema（config/apps/sender_sms.php）

| key | type | default | description |
|-----|------|---------|-------------|
| driver | string | aliyun_sms | 驱动类型 |
| access_key | string | | AccessKey |
| secret_key | string | | SecretKey |
| sign_name | string | | 短信签名 |
| template_code | string | | 模板编码 |

### 7.5 Driver meta() 定位

Driver 的 `meta()` 方法声明该 Driver 需要哪些配置字段，供：
1. SenderManager 的 `meta` API 输出 schema
2. 前端渲染配置表单
3. SenderFactory 识别 Driver 的 channelType

---

## 8. Driver 实现

### 8.1 能力矩阵

| Driver | type（slug） | channelType | code | notify | 说明 |
|---|---|---|---|---|---|
| SmtpDriver | smtp | smtp | ✅ | ✅ | SMTP 邮件，支持验证码和通知 |
| AliyunSmsDriver | aliyun_sms | sms | ✅ | ❌ | 阿里云短信，仅支持验证码 |

### 8.2 SmtpDriver

**send() 分支逻辑**：
- `type === 'code'`：调用 `renderTemplate('code', ['code'=>..., 'expire'=>...])` 渲染 `smtp_code.txt`，用 `.html()` 发送
- `type === 'notify'`：如果 `$message->template` 有值，调用 `renderTemplate($template, $vars)`；否则用 `$message->body`，用 `.html()` 发送

**SMTP 配置注入**：使用反射 hack 强制覆盖 `mailer\lib\Config` 的静态属性，因为 `yzh52521/think-mail` 库读取配置后会缓存，不会重新读取。

### 8.3 AliyunSmsDriver

**supportedTypes()**：返回 `['code']`，不支持 notify。

**send() 逻辑**：
- 从配置读取 `template_code` 和 `sign_name`
- 构造参数 `['code' => $message->code, 'expire' => $message->expire]`
- 调用阿里云 SMS SDK（当前为占位实现）

**SMS 接口约束**：国内短信（阿里云/腾讯云/华为云）必须走预审核模板，不支持直发文本。验证码内容由服务商模板决定，Driver 只传变量。

---

## 9. 模板系统

### 9.1 模板文件

目录：`app/api/service/Sender/template/`

命名规则：`{channelType}_{scene}.txt`

当前模板：

| 文件 | 用途 | 变量 |
|---|---|---|
| smtp_code.txt | SMTP 验证码 | {code}, {expire} |

### 9.2 变量语法

使用 `{变量名}` 格式，`renderTemplate()` 通过 `str_replace` 替换。

```
您的验证码是 {code}，{expire}分钟内有效，请勿泄露给他人。
```

### 9.3 渲染流程

```php
// AbstractDriver::renderTemplate()
$filename = $this->channelType . '_' . $template . '.txt';
$path = __DIR__ . '/../template/' . $filename;
$content = file_get_contents($path);
foreach ($vars as $key => $value) {
    $content = str_replace('{' . $key . '}', (string) $value, $content);
}
return $content;
```

### 9.4 新增模板

在 `template/` 目录下创建 `{channelType}_{scene}.txt` 文件即可。业务方调用 `Sender::notify($channelType, $to, $scene, $vars)` 时自动加载。

---

## 10. Admin API

### 10.1 API 端点

| 方法 | 路径 | 说明 | RBAC name |
|---|---|---|---|
| GET | /api/all/sender/types | 列出所有 Driver 类型 | sender.types |
| GET | /api/all/sender/{type}/meta | 获取 Driver 配置 schema | sender.meta |
| POST | /api/all/sender/install | 扫描 Driver 并注册配置 | sender.install |
| GET | /api/all/sender/channels | 列出所有渠道 | sender.channels |
| GET | /api/all/sender/templates | 列出所有模板文件 | sender.templates |
| POST | /api/all/sender/test-channel | 测试渠道发送 | sender.testChannel |

所有路由在 `sender.php` 中定义，均注册 RBAC（group: 消息），需 admin 或 root 角色访问。

### 10.2 install API

```json
POST /api/all/sender/install

Response:
[
  {"group": "sender_smtp", "seeded": [...], "skipped": [...]},
  {"group": "sender_aliyun_sms", "seeded": [...], "skipped": [...]}
]
```

- `seeded`：新插入的配置 key
- `skipped`：已存在、跳过的配置 key
- 幂等操作，可重复调用

---

## 11. 前端页面

### 11.1 渠道列表页

路径：`/apps/sender/config`

功能：
- 按渠道类型分组展示渠道卡片
- 每组可设默认 Driver（写入 `sender.default_{channelType}`）
- 卡片显示支持的消息类型标签（code / notify）
- "扫描渠道"按钮（调用 install API 后刷新列表）

### 11.2 渠道配置页

路径：`/apps/sender/config/{slug}`

功能：
- 读取 `sender_{slug}` 配置组
- 动态渲染表单（字段定义从 API 获取）
- 保存配置
- 测试发信（支持自定义收件地址）

---

## 12. 新 Driver 扩展流程

### 12.1 步骤

```
1. 创建 Driver/XxxDriver.php
   - extends AbstractDriver
   - 实现 meta() → 声明 channelType、type、配置字段
   - 实现 supportedTypes() → 声明支持的消息类型
   - 实现 send(Message) → 发送逻辑

2. 创建 config/apps/sender_xxx.php（可选，如需独立配置 schema）

3. 调用 POST /api/all/sender/install
   - 或前端点"扫描渠道"按钮

4. 完成
   - SenderFactory 自动识别
   - 配置自动注册到 configs 表
   - 前端自动展示
```

### 12.2 示例：新增钉钉机器人 Driver

```php
// Driver/DingtalkDriver.php
class DingtalkDriver extends AbstractDriver
{
    public static function supportedTypes(): array
    {
        return ['notify'];  // 钉钉机器人只支持通知，不支持验证码
    }

    public static function meta(): array
    {
        return [
            'type'        => 'dingtalk',
            'channelType' => 'webhook',
            'name'        => '钉钉机器人',
            'icon'        => 'mdi-robot',
            'fields'      => [
                ['key' => 'webhook_url', 'label' => 'Webhook URL', 'type' => 'text'],
                ['key' => 'secret',      'label' => '签名密钥',     'type' => 'password'],
            ],
        ];
    }

    public function send(Message $message): SendResult
    {
        // 调用钉钉 Webhook API
    }
}
```

调用 `install` 后，`config/apps/sender.php` 需要加 `default_webhook` 的默认值（如 `'dingtalk'`）。

---

## 13. 消息类型详解

### 13.1 Code（验证码）

**数据结构**：
- `channelType`：渠道类型
- `to`：收件人（邮箱或手机号）
- `code`：验证码字符串
- `expire`：过期时间（分钟）

**SMTP 渠道处理**：
- 读取 `template/smtp_code.txt` 模板
- 替换 `{code}` 和 `{expire}` 变量
- 通过 Mailer 发送 HTML 邮件

**SMS 渠道处理**：
- 从配置读取 `template_code`（服务商模板 ID）和 `sign_name`（签名）
- 将 `code` 和 `expire` 作为变量传给服务商 API
- 验证码内容由服务商模板决定

### 13.2 Notify（通知）

**数据结构**：
- `channelType`：渠道类型
- `to`：收件人
- `template`：模板名（不含渠道前缀和 .txt 后缀）
- `vars`：模板变量数组

**处理流程**：
- `renderTemplate()` 读取 `template/{channelType}_{template}.txt`
- 替换所有 `{key}` 变量
- 通过 Driver 发送

**特殊处理**：
- `vars` 中的 `subject` 键会被 SMTP Driver 用作邮件主题
- 如果 `$message->body` 有值且 `$message->template` 为空，直接发送 body 内容

---

## 14. RBAC 权限

### 14.1 路由注册

所有 Sender 路由在 `sender.php` 中定义，均带 `->setOption('meta', [...])` 注解：

| 路由名 | meta.name | meta.group |
|---|---|---|
| sender.types | 消息驱动列表 | 消息 |
| sender.meta | 驱动配置信息 | 消息 |
| sender.install | 安装消息驱动 | 消息 |
| sender.channels | 消息渠道列表 | 消息 |
| sender.templates | 消息模板列表 | 消息 |
| sender.testChannel | 测试消息渠道 | 消息 |

### 14.2 权限分配

- **root（role_id=1）**：拥有所有 Sender 路由
- **admin（role_id=2）**：拥有所有 Sender 路由（路径以 `/all/` 开头）
- **user（role_id=3）**：无 Sender 路由权限

权限通过 `POST /api/all/roles/reseed` 重新生成。

---

## 15. 当前状态

### 15.1 已实现

| 组件 | 状态 | 说明 |
|---|---|---|
| SmtpDriver | ✅ 可用 | 已测试通过，实际发送到 QQ 邮箱成功 |
| AliyunSmsDriver | ⚠️ 占位 | 配置表已建，SDK 未接入 |
| Sender Facade | ✅ 可用 | code() + notify() + dispatch() |
| Admin API | ✅ 可用 | 6 个端点全部通过测试 |
| RBAC | ✅ 已注册 | 6 条路由全部注册，reseed 完成 |
| 前端页面 | ✅ 可用 | 渠道列表 + 渠道配置 + 扫描渠道 |

### 15.2 已测试端点

| 端点 | 测试结果 |
|---|---|
| GET /all/sender/types | ✅ 返回 2 个 Driver |
| GET /all/sender/channels | ✅ 返回完整字段定义 |
| GET /all/sender/templates | ✅ 返回 smtp_code 模板 |
| GET /all/sender/smtp/meta | ✅ 返回 schema + supportedTypes |
| GET /all/sender/aliyun_sms/meta | ✅ 返回 schema + supportedTypes |
| POST /all/sender/test-channel | ✅ SMTP 实际发送成功 |

### 15.3 业务调用方

| 文件 | 调用 |
|---|---|
| service/User/Session.php | `Sender::code('smtp', $email, $code)` |
| service/User/Profile.php | `Sender::code('smtp', $email, $code)` |
