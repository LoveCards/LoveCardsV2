<h1 align="center">
  <br>
  <a href="https://lovecards.cn/" alt="logo" ><img src="https://s11.ax1x.com/2024/01/11/pFCilx1.png" width="150"/></a>
  <br>
  LoveCards
  <br>
</h1>
<h4 align="center">由你筑起的“领域” 众人皆可“倾心倾意”.</h4>

<p align="center">
  <a href="https://github.com/zhiguai/LoveCards/releases">
    <img src="https://img.shields.io/github/v/release/zhiguai/LoveCards?include_prereleases&style=flat-square" />
  </a>
  <img src="https://img.shields.io/github/stars/zhiguai/LoveCards?style=social">
</p>

<p align="center">
  <a href="https://lovecards.cn">主页</a> •
  <a href="https://forum.lovecards.cn">论坛</a> •
  <a href="https://jq.qq.com/?_wv=1027&k=QTRjFYyB">QQ群组</a> •
  <a href="https://github.com/zhiguai/LoveCards/releases">下载</a>
</p>

<img src="https://img1.imgtp.com/2023/09/18/UyB65ww3.png">

## 🌟 亮点

🗃️ 不止表白卡，更有交流卡  
👨‍👩‍👧‍👦 强大的用户系统，也支持游客的随时访问
✨ 标签系统，你的站点，你的热点，你创造  
💙 模板系统，给你无限可能  
📤 卡片不限字数，支持多图片上传  
💻 支持评论，点赞，让互动性拉满  
👩‍👧‍👦 管理后台可添加多个管理员  
🔗 卡片一键分享至多平台  
👁️‍🗨️ 卡片浏览次数统计  
🚀 发行版开箱即用  
🌈 ... ...

## 👀 部分图示

#### 后台总览
<img src="https://s11.ax1x.com/2024/01/11/pFCifRs.jpg">  

#### 外观设置
<img src="https://s11.ax1x.com/2024/01/11/pFCiDMt.jpg">

#### 默认主题配色展示
<div style="display: flex; justify-content: space-between;">
    <img src="https://s11.ax1x.com/2024/01/11/pFCitaD.jpg" style="width: 30%;" alt="Image 1">
    <img src="https://s11.ax1x.com/2024/01/11/pFCilx1.png" style="width: 30%;" alt="Image 2">
    <img src="https://s11.ax1x.com/2024/01/11/pFCiYVO.jpg" style="width: 30%;" alt="Image 3">
</div>

## 🛠️ 部署

#### 1. 环境（参考开发环境）

Nginx/Apache  
PHP8+  
Composer  
mysql  
redis(可选)  

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

更新通知可通过关注该仓库、关注[LoveCards论坛](https://forum.lovecards.cn)、加入[QQ交流群](https://jq.qq.com/?_wv=1027&k=QTRjFYyB)及时获取  
> 目前该项目仅有**一人**长期维护与更新(能力有限🥵)，如果你也是一位开发者，🥰非常欢迎你加入到LC的生态以及核心程序的开发中来

## 👨‍💻 开发者看过来

#### 模板说明

-   <a href="https://docs.lovecards.cn">LoveCards文档镜像🪞</a>(🔥更新中)
-   <a href="https://lovecards.github.io">LoveCards文档🐱</a>(🔥更新中)
-   <a href="https://console-docs.apipost.cn/preview/ad83ecdb4f10e38b/e187796270055b7b">API 文档</a>(🔥更新中)

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