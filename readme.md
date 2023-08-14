<h1 align="center">
  <br>
  <a href="https://lovecards.cn/" alt="logo" ><img src="https://img1.imgtp.com/2023/08/04/TiKztuPI.png" width="150"/></a>
  <br>
  LoveCards
  <br>
</h1>
<h4 align="center">或许，它可以不只是表白墙.</h4>

<p align="center">
  <a href="https://github.com/zhiguai/LoveCards/releases">
    <img src="https://img.shields.io/github/v/release/zhiguai/LoveCards?include_prereleases&style=flat-square" />
  </a>
  <img src="https://img.shields.io/github/stars/zhiguai/LoveCards?style=social">
</p>

<p align="center">
  <a href="https://lovecards.cn">主页</a> •
  <a href="http://test123.chizg.cn">演示站</a> •
  <a href="https://jq.qq.com/?_wv=1027&k=QTRjFYyB">QQ群组</a> •
  <a href="https://github.com/zhiguai/LoveCards/releases">下载</a>
</p>

<img src="https://img1.imgtp.com/2023/05/21/G50Prq3T.png">
<img src="https://img1.imgtp.com/2023/05/21/YSPANS28.png">

## 🌟 亮点

🗃️ 不止表白卡，更有交流卡  
✨ 标签系统，你的站点，你的热点，你创造  
💙 模板系统，给你无限可能  
📤 卡片不限字数，支持多图片上传  
💻 支持评论，点赞，让互动性拉满  
👩‍👧‍👦 管理后台可添加多个管理员  
🔗 卡片一键分享至多平台  
👁️‍🗨️ 卡片浏览次数统计  
🚀 发行版开箱即用  
🌈 ... ...

## 🛠️ 部署

#### 1. 环境（参考开发环境）

Nginx/Apache  
PHP8+  
Composer  
mysql

#### 2. TP6 安装依赖（发行版跳过）

`composer install`

#### 3. 配置

1. 设置运行目录为“Public”
2. 设置伪静态，伪静态规则参考“Public”目录下的“.htaccess”与“nginx.htaccess”（无法自动识别时请手动设置）
3. 删除“lock.txt”安装记录文件
4. 赋予程序根目录操作权限为“777”（出现异常时可选）
5. 关闭防跨站(宝塔可选)

#### 4. 进入网站自动跳转至安装引导

## ⚠️ 注意

请及时关注该仓库或加入 QQ 交流群获取更新通知  
如有安装问题请搜索“TP6 程序宝塔部署教程”或加入 QQ 交流群咨询（请先学会如何正确提问）  
项目官网<a href="https://lovecards.cn">lovecards.cn</a>

## 👨‍💻 开发者看过来

#### 模板说明

-   LC2 模板存放目录“public\view\index\”,例如：默认模板路径“public\view\index\index”
-   目前 LC2 支持模板开发，具体开发文档暂未完成，可参考默认模板以及 ThinkTemplate 开发指南(注意：config.ini 不可少)
-   <a href="https://www.kancloud.cn/manual/think-template/1286403">ThinkTemplate 开发指南(LC2 模板开发语法指南)</a>
-   <a href="https://console-docs.apipost.cn/preview/ad83ecdb4f10e38b/e187796270055b7b">API 文档</a>(待完善)

#### 版本号说明  
-   Ver为实际版本号，程序中的“版本检测”将优先或仅以Ver为准
-   VerS为展示版本号，用作发行版的展示
-   VerS中[A.B.C]  
    当 A 改变意味着 有较低层的代码或架构等存在重大改动  
    当 B 改变意味着 数据库相关有改动  
    当 C 改变意味着 一般逻辑代码有改动
-   Ver中[A.B.C]  
    A 指的也就是LC2中的2 一般不变  
    B 可简单对照 VerS中的 A
    C 可简单对照 VerS中的 B与C的合

## ⚗️ 技术栈

-   [PHP](https://www.php.net "PHP")+[ThinkPHP6](https://www.thinkphp.cn/ "ThinkPHP6")
-   [MDUI](https://www.mdui.org/ "MDUI")+[Jquery](https://jquery.com/ "Jquery")

## 👯‍♀️👯‍♂ 大家庭

<a href="https://jq.qq.com/?_wv=1027&k=QTRjFYyB">QQ 交流群 801235342</a>  
**如有问题，请先加群咨询**

## ⭐ Star History

<a href="https://github.com/zhiguai/LoveCards/stargazers">
    <img width="500" alt="Star History Chart" src="https://api.star-history.com/svg?repos=zhiguai/LoveCards&type=Date">
</a> 

## 📜 License
GPL V3