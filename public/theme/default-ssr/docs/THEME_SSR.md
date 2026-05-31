# LoveCards3 SSR 默认主题设计文档

> 版本：2.0.0 | 最后更新：2026-05-31
>
> 主题目录：`BackEnd/public/theme/default-ssr/`

---

## 一、技术栈

| 层 | 技术 | 说明 |
|---|---|---|
| 模板引擎 | TP6 原生模板引擎 | `{volist}`、`{if}`、`{$var}` 等 |
| 前端框架 | CDN 版 Vue 3（createApp） | 用于交互 hydrate |
| HTTP | @lovecards/sdk（UMD 版） | 本地 `/assets/lovecards.umd.js` |
| 设计系统 | Vibrant Acrylic / Glassmorphism | 与 SPA 主题统一 |
| CSS | 纯 CSS + CSS Custom Properties | 基于 OKLCH 色彩空间 |
| 字体 | Geist（Google Fonts CDN） |  |

### 1.1 与 SPA 主题的设计统一

| 维度 | SPA 主题 | SSR 主题 |
|------|---------|---------|
| 色彩 | OKLCH 粉色系统 `oklch(0.55 0.24 355)` | 同一色彩系统，hex 兼容 |
| 卡片 | Acrylic `backdrop-filter: blur(20px)` + 半透明 | 同一 Acrylic 效果 |
| 圆角 | `1rem`（16px）基准大圆角 | 同一圆角体系 |
| 暗色模式 | `.dark` class + next-themes | `.dark` class + localStorage |
| 字体 | Geist | Geist（CDN） |
| 布局 | 桌面侧边栏 + 移动端底栏 | 同一响应式布局 |

---

## 二、目录结构

```
BackEnd/public/theme/default-ssr/
├── theme.json               # 主题元数据 + 配置 + 路由
├── templates/               # TP6 模板文件
│   ├── layout/
│   │   ├── header.html      # 公共头部（侧边栏 + 移动端导航）
│   │   └── footer.html      # 公共尾部（JS 加载）
│   ├── index.html           # 首页
│   ├── cards.html           # 卡片列表
│   ├── card.html            # 卡片详情
│   ├── search.html          # 搜索
│   ├── publish.html         # 发布卡片
│   ├── login.html           # 登录
│   ├── user.html            # 用户中心
│   ├── settings.html        # 编辑资料
│   ├── password.html        # 修改密码
│   ├── user-cards.html      # 我的卡片
│   ├── user-comments.html   # 我的评论
│   ├── user-likes.html      # 我的点赞
│   └── 404.html             # 404 页面
├── assets/
│   ├── style.css            # 主题样式（Vibrant Acrylic）
│   ├── app.js               # 快捷调用层（路由 + 鉴权 + 主题切换）
│   ├── lovecards.umd.js     # @lovecards/sdk UMD 版
│   └── modules/             # Vue 3 交互模块
│       ├── auth.js          # 登录/注册/访客
│       ├── card.js          # 卡片详情（评论/点赞）
│       ├── publish.js       # 发布/编辑卡片
│       ├── search.js        # 搜索 + 分页
│       ├── user.js          # 用户中心
│       ├── settings.js      # 编辑资料
│       ├── password.js      # 修改密码
│       ├── user-cards.js    # 我的卡片
│       ├── user-comments.js # 我的评论
│       └── user-likes.js    # 我的点赞
├── docs/
│   └── THEME_SSR.md         # 本文件
└── preview.png              # 预览图（可选）
```

---

## 三、设计系统

### 3.1 CSS 变量

```css
:root {
  /* 主题色 */
  --primary: #e91e63;
  --primary-hover: #c2185b;
  --secondary: #ce93d8;

  /* 表面色 */
  --background: #f8f9ff;
  --foreground: #1a1d29;
  --card-bg: rgba(255, 255, 255, 0.7);
  --card-border: rgba(255, 255, 255, 0.5);

  /* 圆角 */
  --radius: 16px;
  --radius-sm: 10px;
  --radius-pill: 9999px;

  /* 字体 */
  --font: 'Geist', sans-serif;
}

.dark {
  --background: #0d1117;
  --foreground: #e8eaed;
  --card-bg: rgba(20, 28, 48, 0.7);
  --card-border: rgba(255, 255, 255, 0.08);
}
```

### 3.2 Acrylic 卡片

```css
.card {
  background: var(--card-bg);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid var(--card-border);
  border-radius: var(--radius);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  transition: transform 0.3s, box-shadow 0.3s;
}
.card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
}
```

### 3.3 布局

- **桌面端**（≥768px）：左侧 260px 固定侧边栏 + 内容区
- **移动端**（<768px）：顶部导航 + 底部 Tab 导航 + FAB 按钮
- **最大内容宽度**：800px
- **卡片网格**：CSS 瀑布流（1/2/3 列响应式）或 Grid 布局

### 3.4 暗色模式

通过 `<html class="dark">` 切换，localStorage 持久化：

```js
localStorage.getItem('theme') === 'dark'
```

CSS 变量自动适配，不需要 `@media (prefers-color-scheme)` 覆盖。

---

## 四、数据流

### 4.1 首屏数据（PHP 注入）

```
浏览器 GET /cards/5
    │
    ▼
PHP 主题引擎
    ├─ 匹配路由 → /cards/:id → data: ["cards.get", "comments.list"]
    ├─ 内部调用 Service 层（不走 HTTP API）
    │   ├─ Cards::get(5)
    │   └─ Comments::listAll()
    ├─ 注入 → window.__LC_DATA__ = { "cards.get": { code: 200, data: Card } }
    └─ 渲染模板 → 用 {$__LC_DATA__.cards.get.data.content} 输出
```

**注意**：SSR 首屏数据通过 PHP Service 直接调用获取，不经过 HTTP API，所以不受后端响应格式变化影响。

### 4.2 交互数据（JSSDK 调用）

```
用户点击"点赞"
    │
    ▼
app.js
    ├─ sdk.cards.like(5)
    ├─ JSSDK 调 POST /api/cards/5/like
    └─ SDK 返回 { success: true, data: ..., message, timestamp }
       → 使用 res.data 获取业务数据
```

### 4.3 SDK 响应格式

所有 SDK 方法返回统一格式：

```js
// 成功
{ success: true, data: T, message: "...", timestamp: "..." }

// 分页列表
{ success: true, data: [...], pagination: { currentPage, totalPages, totalItems, itemsPerPage }, message: "...", timestamp: "..." }

// 错误
{ success: false, error: { code, message, details }, timestamp: "..." }
```

JS 模块中统一使用 `res.data` 获取业务数据，`res.pagination` 获取分页信息。

---

## 五、SDK API 映射

### 认证

```js
const res = await sdk.session.login({ account, password })
// res.data.token → 设置 token
```

### 卡片

```js
const { data } = await sdk.cards.list({ page: 1 })   // data: Card[]
const { data, pagination } = await sdk.cards.hot()    // data: Card[]
const { data } = await sdk.cards.get(1)               // data: Card
const { data } = await sdk.cards.search({ keyword })  // data: Card[]
const { data } = await sdk.cards.create({ content })  // data: { id }
await sdk.cards.update(id, { content })
await sdk.cards.delete(id)
await sdk.cards.like(id)
```

### 评论

```js
const { data } = await sdk.comments.cardList(cardId) // data: Comment[]
const { data } = await sdk.comments.create(cardId, { content }) // data: Comment
await sdk.comments.delete(id)
```

### 标签

```js
const { data } = await sdk.tags.list()               // data: Tag[]
```

### 用户

```js
const { data } = await sdk.users.me()                // data: User
await sdk.users.updateMe({ username })
await sdk.users.updatePassword({ password })
await sdk.session.logout()
```

### 点赞

```js
const { data } = await sdk.likes.list()              // data: LikeItem[]
await sdk.likes.unlike(id)
```

---

## 六、theme.json

```json
{
  "name": "default-ssr",
  "version": "2.1.0",
  "description": "LoveCards 默认 SSR 主题",
  "author": "LoveCards",
  "mode": "ssr",
  "config": {
    "siteName": { "type": "text", "label": "站点名称", "default": "LoveCards" },
    "siteKeywords": { "type": "text", "label": "SEO 关键词", "default": "LoveCards,卡片" },
    "siteDesc": { "type": "text", "label": "SEO 描述", "default": "LoveCards - 简洁的卡片系统" },
    "primaryColor": { "type": "color", "label": "主色调", "default": "#e91e63" }
  },
  "routes": {
    "/": { "template": "index.html", "data": ["cards.hot", "tags.list"] },
    "/cards": { "template": "cards.html", "data": ["cards.list", "tags.list"] },
    "/cards/:id": { "template": "card.html", "data": ["cards.get", "comments.list"] },
    "/publish": { "template": "publish.html", "data": ["tags.list"] },
    "/search": { "template": "search.html", "data": [] },
    "/login": { "template": "login.html", "data": [] },
    "/user": { "template": "user.html", "data": [] },
    "/user/settings": { "template": "settings.html", "data": [] },
    "/user/password": { "template": "password.html", "data": [] },
    "/user/cards": { "template": "user-cards.html", "data": [] },
    "/user/comments": { "template": "user-comments.html", "data": [] },
    "/user/likes": { "template": "user-likes.html", "data": [] }
  }
}
```

---

## 七、开发者指南

### 7.1 改 UI 出新主题

1. 复制 `default-ssr/` 目录为新主题名
2. 修改 `theme.json` 的 `name`、`description`、`author`
3. 修改 `assets/style.css` 的 CSS 变量（改配色、改圆角）
4. 修改 `templates/` 下的 HTML 模板（改布局、改内容）
5. 上传 ZIP 到后台

### 7.2 添加新页面

1. 在 `templates/` 下创建新模板文件
2. 在 `theme.json` 的 `routes` 中添加路由规则
3. 在 `assets/modules/` 下添加 Vue 3 交互模块（可选）

### 7.3 SDK 格式适配

SDK 方法返回 `{ success, data, pagination?, message, timestamp }` 格式：

| 旧写法 | 新写法 |
|--------|--------|
| `res.data`（从 Paginated 取） | `res.data`（直接从 ApiResponse 取） |
| `res.last_page` | `res.pagination?.totalPages` |
| `res.token`（直接） | `res.data.token` |
| `res.id`（直接） | `res.data.id` |
