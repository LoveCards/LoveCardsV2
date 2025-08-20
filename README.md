<div align="center"><a name="readme-top"></a>

<img src="https://youke1.picui.cn/s1/2025/08/20/68a58e9d4bf4f.png" alt="LoveCards Banner">

<h1>LoveCards</h1>

倾心轻言·无限表达<br/>
一款轻量、快速、友好于一身的微型社区系统

[主页][lovecard-link] · [论坛][lovecard-forum-link] · [QQ 群组][lovecard-qqgroup-link] · [下载][github-releasedate-link] · [反馈问题](github-issues-link)

<!-- SHIELD GROUP -->

[![][github-release-shield]][github-release-link]
[![][github-action-release-shield]][github-action-release-link]
[![][github-releasedate-shield]][github-releasedate-link]
[![][github-contributors-shield]][github-contributors-link]<br>
[![][github-forks-shield]][github-forks-link]
[![][github-stars-shield]][github-stars-link]
[![][github-issues-shield]][github-issues-link]
[![][github-license-shield]][github-license-link]

<sup>小众不再小众的时代，一人也能点亮一个小宇宙</sup>
![][image-overview]

</div>

<details>
<summary><kbd>目录</kbd></summary>

-   [👋🏻 开始使用](#-开始使用)
-   [✨ 特性一览](#-特性一览)
-   [🛠️ 快速部署](#️-快速部署)
-   [🗺️ 开发指南](#️-开发指南)
-   [🤝 参与贡献](#-参与贡献)
-   [❤️ 社区赞助](#️-社区赞助)
-   [📜 License](#-license)

</details>

## 👋🏻 开始使用

LoveCards 又名 倾心轻言 是一个充满热情的开源项目，我们致力于为用户提供一个轻量、快速、友好微社区驱动能力。

项目目前正在积极开发中，有任何需求或者问题，欢迎提交 [Issues][github-issues-link]。

> [!IMPORTANT]
>
> **收藏项目**，你将从 GitHub 上无延迟地接收所有发布通知～ ⭐️

[![][image-star]][github-stars-link]

<details><summary><kbd>Star History</kbd></summary>
<a href="https://github.com/zhiguai/LoveCards/stargazers">
    <picture>
      <source media="(prefers-color-scheme: dark)" srcset="https://api.star-history.com/svg?repos=zhiguai/LoveCards&type=Date&theme=dark">
      <img alt="Star History Chart" src="https://api.star-history.com/svg?repos=zhiguai/LoveCards&type=Date">
    </picture>
</a>
</details>

## ✨ 特性一览

通过 LoveCards 的强大功能，体验为创意、交流和个性化而设计的全新社区之旅。

**轻量即开**  
零配置上线，压缩包一丢就跑。

**小圈私属**  
单站即宇宙，好友即世界。

**多彩焕肤**  
一键换色，风格随心。

**标签热浪**  
#今日份灵感 瞬间汇流。

**互动三连**  
点赞 评论 分享 一气呵成。

**长文无界**  
无限字数，多张配图，深度尽情。

> [!NOTE]
> 更多激动人心的功能正在开发中 🤫！

<div align="right">

[![][back-to-top]](#readme-top)

</div>

## 🛠️ 快速部署

正常用户可通过下面的方法快速搭建，搭建所需文件为[发行版本][github-releasedate-link]  
多种部署方法请参考文档：[部署指南][lovecard-docs-link]

<div align="right">

[![][back-to-top]](#readme-top)

</div>

## 🗺️ 开发指南

LoveCards 的成长离不开社区的力量 👏。目前该项目主要由一人长期维护 😢，我们非常欢迎并期待更多开发者加入 🤝！

### 本地开发

<details>
<summary><kbd>美美隐身</kbd></summary>

#### 环境要求

-   Web 服务器: Nginx / Apache
-   PHP: 8.0+
-   数据库: MySQL5.7+
-   依赖管理: Composer
-   缓存 (可选): Redis

#### 安装步骤

1.  **获取程序**：
    ```bash
    https://github.com/LoveCards/LoveCardsV2.git
    ```
2.  **安装依赖**：
    ```bash
    composer install
    ```
3.  **配置 Web 服务器**：
    -   设置网站的运行目录为 `public`
    -   设置伪静态规则，规则可参考 `.dev/env` 目录下的 `.htaccess`文件
4.  **安装引导**： - (可选) 如存在 `lock.txt` 文件，请先删除 - (可选) 确保程序根目录拥有写入权限 - 访问你的网站域名，程序将自动跳转至安装向导页面，根据提示完成安装
</details>

### 文档说明

我们为开发者提供了详细的文档，帮助您进行二次开发或主题设计。

-   [LoveCards 文档 (镜像)][lovecard-docs-link]
-   [LoveCards 文档 (GitHub)](https://lovecards.github.io)
-   [API 文档（新）](https://docs.apipost.net/docs/detail/43a9aeee4403000?target_id=)
-   [API 文档（旧）](https://console-docs.apipost.cn/preview/ad83ecdb4f10e38b/e187796270055b7b)

<div align="right">

[![][back-to-top]](#readme-top)

</div>

## 🤝 参与贡献

我们非常欢迎各种形式的贡献。如果你对贡献代码感兴趣，可以查看我们的 GitHub [Issues][github-issues-link] 和 [Projects][github-project-link]，大展身手，向我们展示你的奇思妙想。

> [!TIP]
>
> 我们希望创建一个技术分享型社区，一个可以促进知识共享、想法交流，激发彼此鼓励和协作的环境。
> 同时欢迎联系我们提供产品功能和使用体验反馈，帮助我们将 LoveCards 建设得更好。
>
> **组织维护者:** [@zhiguai](https://github.com/zhiguai)

<a href="https://github.com/LoveCards/LoveCardsV2/graphs/contributors" target="_blank">
  <table>
    <tr>
      <th colspan="2">
        <br><img src="https://contrib.rocks/image?repo=LoveCards/LoveCardsV2"><br><br>
      </th>
    </tr>
    <tr>
      <td>
        <picture>
          <source media="(prefers-color-scheme: dark)" srcset="https://next.ossinsight.io/widgets/official/compose-org-active-contributors/thumbnail.png?activity=active&period=past_28_days&image_size=2x3&owner_id=143197888&repo_ids=582292948&color_scheme=dark">
          <img src="https://next.ossinsight.io/widgets/official/compose-org-active-contributors/thumbnail.png?activity=active&period=past_28_days&image_size=2x3&owner_id=143197888&repo_ids=582292948&color_scheme=light">
        </picture>
      </td>
      <td rowspan="2">
        <picture>
          <source media="(prefers-color-scheme: dark)" srcset="https://next.ossinsight.io/widgets/official/compose-org-participants-growth/thumbnail.png?activity=active&period=past_28_days&owner_id=131470832&repo_ids=643445235&image_size=4x7&color_scheme=dark">
          <img src="https://next.ossinsight.io/widgets/official/compose-org-participants-growth/thumbnail.png?activity=active&period=past_28_days&owner_id=131470832&repo_ids=643445235&image_size=4x7&color_scheme=light">
        </picture>
      </td>
    </tr>
    <tr>
      <td>
        <picture>
          <source media="(prefers-color-scheme: dark)" srcset="https://next.ossinsight.io/widgets/official/compose-org-active-contributors/thumbnail.png?activity=new&period=past_28_days&image_size=2x3&owner_id=143197888&repo_ids=582292948&color_scheme=dark">
          <img src="https://next.ossinsight.io/widgets/official/compose-org-active-contributors/thumbnail.png?activity=new&period=past_28_days&image_size=2x3&owner_id=143197888&repo_ids=582292948&color_scheme=light">
        </picture>
      </td>
    </tr>
  </table>
</a>

<div align="right">

[![][back-to-top]](#readme-top)

</div>

## ❤️ 社区赞助

LoveCards 是一个用爱发电的免费开源项目。您的支持是对我们工作的最大肯定，也将帮助我们投入更多时间来维护和开发新功能。

#### 感谢以下赞助者：

<a href="https://hbyidc.com/recommend/4ai5youo0mTC">
  <img src="https://hbyidc.com/upload/common/default/a471175ea0195f59eb6d14dec27bafea1749120583%5E%E6%B1%89%E5%A0%A1%E4%BA%911020-02.png" width="100" alt="汉堡云">
</a>

## 📜 License

本开源项目遵循 [GPL V3][github-license-link] 开源协议。

<div align="right">

[![][back-to-top]](#readme-top)

</div>

[github-release-link]: https://github.com/LoveCards/LoveCardsV2/releases
[github-release-shield]: https://img.shields.io/github/v/release/LoveCards/LoveCardsV2?color=369eff&labelColor=black&logo=github&style=flat-square
[github-action-release-link]: https://github.com/LoveCards/LoveCardsV2/actions/workflows/release.yml
[github-action-release-shield]: https://img.shields.io/github/actions/workflow/status/LoveCards/LoveCardsV2/release.yml?label=release&labelColor=black&logo=githubactions&logoColor=white&style=flat-square
[github-releasedate-link]: https://github.com/LoveCards/LoveCardsV2/releases
[github-releasedate-shield]: https://img.shields.io/github/release-date/LoveCards/LoveCardsV2?labelColor=black&style=flat-square
[github-contributors-link]: https://github.com/LoveCards/LoveCardsV2/graphs/contributors
[github-contributors-shield]: https://img.shields.io/github/contributors/LoveCards/LoveCardsV2?color=c4f042&labelColor=black&style=flat-square
[github-forks-link]: https://github.com/LoveCards/LoveCardsV2/network/members
[github-forks-shield]: https://img.shields.io/github/forks/LoveCards/LoveCardsV2?color=8ae8ff&labelColor=black&style=flat-square
[github-stars-shield]: https://img.shields.io/github/stars/LoveCards/LoveCardsV2?color=ffcb47&labelColor=black&style=flat-square
[github-issues-link]: https://github.com/LoveCards/LoveCardsV2/issues
[github-issues-shield]: https://img.shields.io/github/issues/LoveCards/LoveCardsV2?color=ff80eb&labelColor=black&style=flat-square
[github-license-link]: https://github.com/LoveCards/LoveCardsV2/blob/main/LICENSE
[github-license-shield]: https://img.shields.io/badge/GPL-v3-white?labelColor=black&style=flat-square
[github-project-link]: https://github.com/LoveCards/LoveCardsV2/projects
[back-to-top]: https://img.shields.io/badge/-返回顶部-151515?style=flat-square
[image-overview]: https://youke1.picui.cn/s1/2025/08/20/68a58e9d82ca0.png
[image-star]: https://youke1.picui.cn/s1/2025/08/20/68a58e961f48c.png
[github-stars-link]: https://github.com/LoveCards/LoveCardsV2/stargazers
[lovecard-link]: https://docs.lovecards.cn/
[lovecard-forum-link]: https://forum.lovecards.cn
[lovecard-docs-link]: https://docs.lovecards.cn/
[lovecard-qqgroup-link]: https://jq.qq.com/?_wv=1027&k=QTRjFYyB
