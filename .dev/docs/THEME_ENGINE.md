# LoveCards3 主题引擎设计文档

> 版本：1.0.0 | 最后更新：2026-05-30

---

## 一、架构概述

主题引擎是 LoveCards3 的前端引导层，位于 `app/frontend/` 模块。它的核心职责是：

1. **接收请求**，判断当前活跃主题
2. **根据主题类型**（SPA / SSR）选择引导方式
3. **返回 HTML** 给浏览器
4. **提供主题管理 API**（上传/切换/配置）

```
浏览器请求
    │
    ▼
app/frontend/controller/Theme.php   ← 主题引擎入口
    │
    ├─ 读 active theme（DB: core.active_theme）
    ├─ 读 theme.json（/public/theme/{name}/theme.json）
    │
    ├─ mode = "spa"
    │   └─ 返回 dist/index.html + 注入 window.__LC__
    │
    └─ mode = "ssr"
        ├─ URL 匹配 theme.json routes
        ├─ 鉴权检查
        ├─ 内部调度 API 获取首屏数据
        ├─ 注入 window.__LC__ + window.__LC_DATA__
        └─ TP6 模板引擎渲染模板
```

### 设计原则

- **主题引擎不碰数据**：所有业务数据通过 API 获取，主题引擎只做引导和注入
- **SPA 和 SSR 统一管理**：一套配置、一套 API、一套切换逻辑
- **一站式部署**：PHP 直接服务，不需要额外的 Node.js 进程（SPA 模式）
- **开发者友好**：上传 ZIP 即可安装，改 UI 即可出新主题

---

## 二、模块结构

```
app/frontend/
├── controller/
│   └── Theme.php              # 主题引擎入口控制器
├── service/
│   └── ThemeEngine.php        # 主题引擎核心服务
└── route/
    └── app.php                # 路由定义
```

### 2.1 Theme.php（入口控制器）

```php
namespace app\frontend\controller;

class Theme
{
    // 公开页面入口（兜底路由）
    public function index()
    {
        $uri = request()->pathinfo();
        return ThemeEngine::boot($uri);
    }
}
```

### 2.2 ThemeEngine.php（核心服务）

核心服务，负责主题引导加载的全部逻辑。

### 2.3 app.php（路由定义）

```php
use think\facade\Route;

// 主题引擎兜底路由（最后注册）
Route::get('/', 'frontend.Theme/index');
Route::get('/:path', 'frontend.Theme/index')
    ->pattern(['path' => '[\w\-/]+']);
```

---

## 三、主题引导流程

### 3.1 SPA 模式

```
浏览器 GET /any-path
    │
    ▼
ThemeEngine::boot($uri)
    │
    ├─ 检测是否为静态资产请求（.js/.css/.png 等）
    │   └─ 是 → serveAsset($uri) → 返回文件内容
    │
    ├─ 读 active theme → mode = "spa"
    ├─ 读 {theme_path}/out/index.html（或 dist/index.html）
    ├─ 注入 window.__LC__ 到 </head> 前
    └─ 返回完整 HTML

浏览器拿到 HTML
    ├─ 加载 /_next/static/chunks/xxx.js
    │   └─ 请求到达 ThemeEngine::boot()
    │   └─ 检测为 .js 资产 → serveAsset() 返回 JS 文件
    ├─ JS 读 window.__LC__
    ├─ JS 初始化 SDK client
    ├─ Vue Router / React Router 接管路由
    └─ JSSDK 调 API 获取数据 → 渲染
```

**SPA 模式下 PHP 做四件事**：
1. 服务静态资产（JS/CSS/图片/字体等）
2. 读 `index.html` 文件
3. 注入 `window.__LC__` 全局变量
4. 返回 HTML

**注入内容**：
```html
<script>
  window.__LC__ = {
    "apiUrl": "/api",
    "theme": "default",
    "config": {
      "primaryColor": "#e91e63",
      "fontFamily": "sans",
      "siteName": "LoveCards"
    }
  }
</script>
```

### 3.2 SSR 模式

```
浏览器 GET /cards/5
    │
    ▼
ThemeEngine::boot($uri)
    │
    ├─ 读 active theme → mode = "ssr"
    ├─ URL 匹配 theme.json routes → /cards/:id
    │   → template: "card.html"
    │   → data: ["cards.get"]
    │   → auth: false
    │
    ├─ 鉴权检查 → auth: false → 跳过
    │
    ├─ 内部调度获取首屏数据
    │   ├─ 调 /api/cards/5 → 拿到 JSON
    │   └─ 组装 __LC_DATA__
    │
    ├─ 注入全局变量
    │   ├─ window.__LC__（配置）
    │   └─ window.__LC_DATA__（首屏数据）
    │
    ├─ TP6 模板引擎渲染 card.html
    │   └─ 模板里用 {$__LC_DATA__.card.data.title} 等
    │
    └─ 返回完整 HTML

浏览器拿到 HTML（首屏有内容）
    ├─ 加载前端 JS（CDN 版 Vue / jQuery + UMD 版 SDK）
    ├─ JS 读 window.__LC_DATA__（已有数据，不重复请求）
    └─ 交互操作（点赞/评论）→ JSSDK 调 API
```

---

## 四、theme.json 规范

每个主题必须包含 `theme.json` 文件，定义主题的元数据、模式、配置和路由。

### 4.1 完整示例

```json
{
  "name": "default",
  "version": "1.0.0",
  "description": "LoveCards 默认主题",
  "author": "LoveCards",
  "mode": "spa",
  "compatibility": {
    "min": "2.5.0",
    "max": "3.x"
  },
  "config": {
    "primaryColor": {
      "type": "color",
      "label": "主题色",
      "default": "#e91e63"
    },
    "fontFamily": {
      "type": "select",
      "label": "字体",
      "options": [
        { "value": "sans", "label": "无衬线" },
        { "value": "serif", "label": "衬线" }
      ],
      "default": "sans"
    },
    "logoUrl": {
      "type": "image",
      "label": "Logo",
      "default": ""
    },
    "siteName": {
      "type": "text",
      "label": "站点名称",
      "default": "LoveCards"
    }
  },
  "routes": {
    "/": {
      "template": "index.html",
      "data": ["cards.hot", "tags.list"],
      "auth": false
    },
    "/cards": {
      "template": "cards.html",
      "data": ["cards.list", "tags.list"],
      "auth": false
    },
    "/cards/:id": {
      "template": "card.html",
      "data": ["cards.get"],
      "auth": false
    },
    "/search": {
      "template": "search.html",
      "data": ["cards.search"],
      "auth": false
    },
    "/user": {
      "template": "user.html",
      "data": ["users.me"],
      "auth": true
    },
    "/user/:tab": {
      "template": "user.html",
      "data": ["users.me"],
      "auth": true
    }
  }
}
```

### 4.2 字段说明

| 字段 | 类型 | 必填 | 说明 |
|---|---|---|---|
| `name` | string | ✅ | 主题标识名（唯一，与目录名一致） |
| `version` | string | ✅ | 语义化版本号 |
| `description` | string | ❌ | 主题描述 |
| `author` | string | ❌ | 作者 |
| `mode` | string | ✅ | `"spa"` 或 `"ssr"` |
| `compatibility` | object | ❌ | 版本兼容范围 |
| `config` | object | ✅ | 配置 schema（见下文） |
| `routes` | object | SSR 必填 | 路由规则（SPA 模式可省略） |

### 4.3 config schema

每个配置项定义：

| 字段 | 说明 |
|---|---|
| `type` | `"text"` / `"select"` / `"color"` / `"image"` / `"toggle"` |
| `label` | 后台显示的标签 |
| `default` | 默认值 |
| `options` | select 类型的选项列表（`[{value, label}]`） |

### 4.4 routes（SSR 模式）

| 字段 | 说明 |
|---|---|
| `template` | 模板文件名（相对于 `templates/` 目录） |
| `data` | 需要预加载的数据集（引用 `PUBLIC_API` 常量表的 key） |
| `auth` | 是否需要登录（`true` / `false`） |

路由匹配支持 `:id` 参数语法，例如 `/cards/:id` 匹配 `/cards/5`。

---

## 五、主题目录结构

### 5.1 SPA 主题

```
{theme-name}/
├── theme.json           # 主题元数据 + 配置
├── out/                 # Next.js 静态导出产物（npm run build → out/）
│   ├── index.html       # SPA 入口壳
│   └── _next/static/    # JS/CSS 资产（由 ThemeEngine 自动服务）
├── preview.png          # 预览图（可选）
└── README.md            # 主题说明（可选）
```

**资产路径约定**：
- Next.js 静态导出的 `/_next/static/*` 绝对路径会被 ThemeEngine 自动解析
- 资产文件在 `out/_next/static/` 目录内
- 请求 `/_next/static/chunks/xxx.js` → 服务 `out/_next/static/chunks/xxx.js`
- 大多数资源名带哈希（如 `chunk.abc123.js`），可被永久缓存 1 年

**SPA 主题的 `out/` 目录由 `next export` 生成**（或等效的静态导出）。

### 5.2 SSR 主题

```
{theme-name}/
├── theme.json           # 主题元数据 + 配置 + 路由
├── templates/           # TP6 模板文件
│   ├── layout/
│   │   ├── header.html  # 公共头部
│   │   └── footer.html  # 公共尾部
│   ├── index.html       # 首页
│   ├── cards.html       # 卡片列表
│   ├── card.html        # 卡片详情
│   ├── search.html      # 搜索
│   ├── user.html        # 用户中心
│   └── 404.html         # 404 页面
├── assets/              # 静态资源
│   ├── style.css
│   ├── app.js           # 快捷调用层（路由 + 鉴权 + 状态）
│   └── images/
├── preview.png          # 预览图（可选）
└── README.md            # 主题说明（可选）
```

---

## 六、主题管理 API

### 6.1 端点列表

| 方法 | 路径 | 说明 | RBAC |
|---|---|---|---|
| GET | `/api/all/theme/list` | 列出已安装主题 | `theme.list` |
| POST | `/api/all/theme/upload` | 上传主题 ZIP | `theme.upload` |
| POST | `/api/all/theme/activate` | 切换活跃主题 | `theme.activate` |
| GET | `/api/all/theme/config` | 获取当前主题配置 | `theme.config` |
| PUT | `/api/all/theme/config` | 更新主题配置 | `theme.updateConfig` |
| POST | `/api/all/theme/freeze` | 固化配置到 theme.json | `theme.freeze` |
| DELETE | `/api/all/theme/:name` | 删除主题 | `theme.delete` |
| GET | `/theme/config` | 公开主题配置（前端用） | 公开 |

### 6.2 上传主题

```
POST /api/all/theme/upload
Content-Type: multipart/form-data

file: theme.zip
```

处理流程：
1. 接收 ZIP 文件
2. 解压到 `/public/theme/{name}/`（name 从 theme.json 读取）
3. 验证 theme.json 格式（name、mode、config 必填）
4. 返回主题信息

### 6.3 切换主题

```
POST /api/all/theme/activate
Content-Type: application/json

{ "name": "romantic" }
```

处理流程：
1. 验证主题存在
2. 读 theme.json 的 config schema
3. 将 config 的默认值组装为 JSON
4. 写入 DB：`core.active_theme = "romantic"`
5. 写入 DB：`core.theme_config = { "primaryColor": "#e91e63", ... }`

### 6.4 获取配置

```
GET /api/all/theme/config
```

返回：
```json
{
  "name": "romantic",
  "mode": "spa",
  "config_schema": {
    "primaryColor": { "type": "color", "label": "主题色", "default": "#e91e63" },
    ...
  },
  "config_values": {
    "primaryColor": "#ff5722",
    "fontFamily": "serif",
    ...
  }
}
```

### 6.5 更新配置

```
PUT /api/all/theme/config
Content-Type: application/json

{ "primaryColor": "#4caf50", "fontFamily": "sans" }
```

处理流程：
1. 读当前 DB `core.theme_config`
2. 合并新值（覆盖）
3. 写回 DB `core.theme_config`

### 6.6 固化配置

```
POST /api/all/theme/freeze
```

处理流程：
1. 读 DB `core.theme_config`（当前运行时配置值）
2. 读 `theme.json`
3. 将 config 每个字段的 `default` 更新为当前值
4. 写回 `theme.json`

效果：下次切换主题再切回来时，schema 的 default 就是固化后的值。

### 6.7 公开配置端点

```
GET /theme/config
```

无需鉴权，返回前端需要的配置：

```json
{
  "theme": "romantic",
  "config": {
    "primaryColor": "#ff5722",
    "fontFamily": "serif",
    "siteName": "LoveCards"
  }
}
```

---

## 七、DB 设计

### 7.1 configs 表新增行

| group | key | type | description |
|---|---|---|---|
| core | active_theme | string | 当前活跃主题名 |
| core | theme_config | json | 当前主题配置值（整个 JSON 对象） |

### 7.2 配置值存储格式

```json
// core.theme_config 的值
{
  "primaryColor": "#ff5722",
  "fontFamily": "serif",
  "logoUrl": "/uploads/logo.png",
  "siteName": "我的站点"
}
```

### 7.3 配置读取优先级

```
1. DB core.theme_config（运行时配置，后台修改）
2. theme.json config.*.default（固化后的默认值）
3. theme.json config.*.default（首次安装时的初始值）
```

---

## 八、内部调度机制（SSR 模式）

### 8.1 原理

SSR 模式下，PHP 主题引擎需要获取首屏数据。使用 TP6 的 `Request::create()` + `app()->dispatch()` 进行内部请求调度，不经过网络层。

```php
$request = \think\Request::create('/api/cards/5', 'GET');
$response = app()->dispatch($request);
$data = json_decode($response->getContent(), true);
```

### 8.2 数据集定义

首屏数据集通过 `PUBLIC_API` 常量表定义（由 @lovecards/sdk 导出）：

```php
// PHP 侧常量（与 SDK 的 PUBLIC_API 保持同步）
const PUBLIC_API = [
    'cards.hot'     => ['method' => 'GET', 'path' => '/api/cards/hot'],
    'cards.list'    => ['method' => 'GET', 'path' => '/api/cards'],
    'cards.get'     => ['method' => 'GET', 'path' => '/api/cards/:id'],
    'cards.search'  => ['method' => 'GET', 'path' => '/api/cards/search'],
    'tags.list'     => ['method' => 'GET', 'path' => '/api/tags'],
    'tags.get'      => ['method' => 'GET', 'path' => '/api/tags/:id'],
    'comments.list' => ['method' => 'GET', 'path' => '/api/comments/card/:id'],
    'users.me'      => ['method' => 'GET', 'path' => '/api/users/me'],
    'system.theme'  => ['method' => 'GET', 'path' => '/api/theme/config'],
];
```

### 8.3 路径参数替换

routes 里的 data key 引用 PUBLIC_API，路径中的 `:id` 等参数从 URL 匹配结果中替换：

```php
// URL: /cards/5
// route: /cards/:id → data: ["cards.get"]
// PUBLIC_API["cards.get"].path = "/api/cards/:id"
// 替换后: "/api/cards/5"
```

### 8.4 只加载公开数据

SSR 预加载只调用**公开端点**（不需要 JWT 鉴权的）：

| 数据集 | 是否公开 | 说明 |
|---|---|---|
| `cards.hot` | ✅ | 热门卡片 |
| `cards.list` | ✅ | 卡片列表 |
| `cards.get` | ✅ | 单个卡片 |
| `cards.search` | ✅ | 搜索 |
| `tags.list` | ✅ | 标签列表 |
| `tags.get` | ✅ | 单个标签 |
| `comments.list` | ✅ | 评论列表 |
| `users.me` | ❌ | 需要登录，不预加载 |
| `system.theme` | ✅ | 主题配置 |

`auth: true` 的路由，`data` 中的需要登录的端点会被跳过，由前端 JSSDK 后续加载。

### 8.5 注意事项

| 坑 | 说明 | 解决方案 |
|---|---|---|
| Request 单例冲突 | `dispatch()` 会修改全局 Request | 用 `Request::create()` 创建独立实例 |
| Session 干扰 | 内部请求共享 Session | 只调公开 API，不涉及 Session |
| 中间件重复执行 | JwtAuthCheck 等会再次执行 | 公开端点无 JWT 中间件，不影响 |
| 端口依赖 | 不走网络，无端口依赖 | `Request::create()` 是纯 PHP 内部调用 |

---

## 九、前端注入格式

### 9.1 SPA 模式注入

注入位置：`</head>` 标签前。

```html
<script>
  window.__LC__ = {
    "apiUrl": "/api",
    "theme": "default",
    "config": {
      "primaryColor": "#e91e63",
      "fontFamily": "sans",
      "logoUrl": "",
      "siteName": "LoveCards"
    }
  }
</script>
```

### 9.2 SSR 模式注入

注入位置：`</head>` 标签前（`__LC__`）+ `</body>` 标签前（`__LC_DATA__`）。

```html
<script>
  window.__LC__ = {
    "apiUrl": "/api",
    "theme": "default-ssr",
    "config": { ... }
  }
</script>
...
<script>
  window.__LC_DATA__ = {
    "cards.hot": { "code": 200, "data": { "data": [...] } },
    "tags.list": { "code": 200, "data": { "data": [...] } }
  }
</script>
```

### 9.3 错误处理

如果 API 调用失败，注入 error 信息：

```html
<script>
  window.__LC_DATA__ = {
    "cards.get": null,
    "_errors": {
      "cards.get": { "code": 404, "message": "卡片不存在" }
    }
  }
</script>
```

前端 JS 读到 `_errors` 就知道哪些数据加载失败，据此显示错误 UI。

---

## 十、ConfigService 迁移

### 10.1 迁移计划

| 项目 | 变更 |
|---|---|
| 文件位置 | `app/api/service/System/Config.php` → `app/common/service/Config.php` |
| 命名空间 | `app\api\service\System\Config` → `app\common\service\Config` |
| 引用更新 | 所有 `use app\api\service\System\Config as ConfigService` → `use app\common\service\Config as ConfigService` |

### 10.2 影响范围

- `app/api/` 模块：约 10+ 个文件引用 ConfigService
- `app/frontend/` 模块：ThemeEngine 引用 ConfigService
- `app/common/` 模块：无影响（是目标位置）

---

## 十一、旧模块清理

### 11.1 index → frontend 重命名

| 旧路径 | 新路径 |
|---|---|
| `app/index/` | `app/frontend/` |

### 11.2 删除的文件

| 文件 | 原因 |
|---|---|
| `app/frontend/common/Theme.php` | 功能合并到 ThemeEngine |
| `app/frontend/common/Common.php` | 数据走 API |
| `app/frontend/common/FrontEnd.php` | 认证走 API + SDK |
| `app/frontend/common/Admin.php` | 无用 |
| `app/frontend/common/App.php` | 无用 |
| `app/frontend/method/` 整个目录 | 数据查询走 API |
| `app/frontend/BaseController.php` | 删除或精简 |
| `app/frontend/controller/Index.php` | 重写为 Theme.php |

### 11.3 保留的文件

| 文件 | 原因 |
|---|---|
| `app/frontend/common/File.php` | 文件操作工具，可能有用 |

---

## 十二、路由优先级

```
请求到达 TP6
    │
    ├─ 1. /api/* 路由 → API 模块处理（最高优先级）
    ├─ 2. /all/* 路由 → API 模块处理（Admin API）
    ├─ 3. /theme/config → API 模块处理（公开端点）
    ├─ 4. /theme/* → 静态资源服务
    └─ 5. /* → 主题引擎兜底（最低优先级）
```

主题引擎路由必须最后注册，确保 API 路由优先匹配。

---

## 十三、配置项说明

### 13.1 config/apps/frontend.php

```php
<?php

return [
    'active_theme' => [
        'type'        => 'string',
        'default'     => 'default',
        'description' => '当前活跃主题',
    ],
    'theme_config' => [
        'type'        => 'json',
        'default'     => '{}',
        'description' => '当前主题配置值',
    ],
];
```
