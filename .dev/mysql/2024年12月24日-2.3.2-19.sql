-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机：localhost
-- 生成日期：2024-12-24 17:49:08
-- 服务器版本：8.0.36
-- PHP 版本：8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库：`456_chizg_cn`
--

-- --------------------------------------------------------

--
-- 表的结构 `admin`
--

CREATE TABLE `admin` (
  `id` int NOT NULL,
  `userName` varchar(32) NOT NULL,
  `password` varchar(64) NOT NULL,
  `power` int NOT NULL DEFAULT '0',
  `uuid` varchar(64) DEFAULT '' COMMENT '登入凭证'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `admin`
--

INSERT INTO `admin` (`id`, `userName`, `password`, `power`, `uuid`) VALUES
(1, 'admin', 'd033e22ae348aeb5660fc2140aec35850c4da997', 0, '');

-- --------------------------------------------------------

--
-- 表的结构 `cards`
--

CREATE TABLE `cards` (
  `id` int NOT NULL COMMENT 'cid/pid/aid:1',
  `uid` int NOT NULL DEFAULT '0',
  `content` mediumtext NOT NULL COMMENT '内容',
  `img` varchar(256) DEFAULT '' COMMENT '封面',
  `woName` varchar(256) DEFAULT '' COMMENT '发布者名字',
  `woContact` varchar(256) DEFAULT '' COMMENT '我的联系方式',
  `taName` varchar(256) DEFAULT '' COMMENT '对方的名字',
  `taContact` varchar(256) DEFAULT '' COMMENT '对方的联系方式',
  `good` int NOT NULL DEFAULT '0' COMMENT '点赞数',
  `comments` int NOT NULL DEFAULT '0' COMMENT '评论数',
  `look` int NOT NULL DEFAULT '0' COMMENT '浏览量',
  `tag` varchar(256) DEFAULT '' COMMENT '标签 Json',
  `model` int DEFAULT '0' COMMENT '卡片模式',
  `time` timestamp NOT NULL COMMENT '发布时间',
  `ip` varchar(256) DEFAULT '' COMMENT '发布 IP',
  `top` enum('0','1') DEFAULT '0' COMMENT '置顶状态',
  `status` enum('0','1') DEFAULT '0' COMMENT '封禁状态'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `comments`
--

CREATE TABLE `comments` (
  `id` int NOT NULL COMMENT 'pid/aid:2',
  `aid` int NOT NULL COMMENT '应用 ID',
  `pid` varchar(256) NOT NULL COMMENT '条目 ID',
  `uid` int NOT NULL,
  `content` varchar(256) NOT NULL COMMENT '内容',
  `name` varchar(256) NOT NULL COMMENT '我的名字',
  `ip` varchar(256) NOT NULL COMMENT '发布 IP',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '发布时间',
  `status` int NOT NULL COMMENT '封禁状态'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `good`
--

CREATE TABLE `good` (
  `id` int NOT NULL,
  `aid` int NOT NULL COMMENT '应用 ID',
  `pid` int NOT NULL COMMENT '条目 ID',
  `uid` int NOT NULL,
  `ip` varchar(32) NOT NULL COMMENT '发布 IP',
  `time` datetime NOT NULL COMMENT '发布时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `images`
--

CREATE TABLE `images` (
  `id` int NOT NULL,
  `aid` int NOT NULL COMMENT '应用 ID',
  `pid` int NOT NULL COMMENT '条目 ID',
  `uid` int NOT NULL,
  `url` varchar(256) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `system`
--

CREATE TABLE `system` (
  `id` int NOT NULL,
  `name` varchar(255) DEFAULT '',
  `value` varchar(2555) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `system`
--

INSERT INTO `system` (`id`, `name`, `value`) VALUES
(1, 'siteUrl', 'lovecards.cn'),
(2, 'siteName', 'LoveCardsV2.3.2'),
(3, 'siteICPId', ''),
(4, 'siteKeywords', ''),
(5, 'siteDes', ''),
(10, 'siteFooter', ''),
(11, 'LCEAPI', ''),
(12, 'siteCopyright', ''),
(13, 'siteTitle', 'LoveCards'),
(14, 'smtpSecure', ''),
(15, 'smtpName', '');

-- --------------------------------------------------------

--
-- 表的结构 `tags`
--

CREATE TABLE `tags` (
  `id` int NOT NULL COMMENT 'tid/pid',
  `aid` int NOT NULL COMMENT '应用 ID',
  `name` varchar(8) DEFAULT '' COMMENT '标签名',
  `tip` varchar(64) DEFAULT '' COMMENT '提示',
  `status` int NOT NULL DEFAULT '0' COMMENT '封禁状态',
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `tags_map`
--

CREATE TABLE `tags_map` (
  `id` int NOT NULL COMMENT 'pid',
  `aid` int NOT NULL COMMENT '应用 ID',
  `pid` int DEFAULT NULL COMMENT 'AID[PID]',
  `tid` int DEFAULT NULL COMMENT 'TagID',
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `number` varchar(32) NOT NULL,
  `avatar` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(320) NOT NULL,
  `phone` varchar(32) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `status` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPACT;

--
-- 转储表的索引
--

--
-- 表的索引 `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `cards`
--
ALTER TABLE `cards`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `good`
--
ALTER TABLE `good`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `system`
--
ALTER TABLE `system`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `tags_map`
--
ALTER TABLE `tags_map`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用 AUTO_INCREMENT
--

--
-- 使用表 AUTO_INCREMENT `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表 AUTO_INCREMENT `cards`
--
ALTER TABLE `cards`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'cid/pid/aid:1';

--
-- 使用表 AUTO_INCREMENT `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'pid/aid:2';

--
-- 使用表 AUTO_INCREMENT `good`
--
ALTER TABLE `good`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表 AUTO_INCREMENT `images`
--
ALTER TABLE `images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- 使用表 AUTO_INCREMENT `system`
--
ALTER TABLE `system`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- 使用表 AUTO_INCREMENT `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'tid/pid';

--
-- 使用表 AUTO_INCREMENT `tags_map`
--
ALTER TABLE `tags_map`
  MODIFY `id` int NOT NULL AUTO_INCREMENT COMMENT 'pid';

--
-- 使用表 AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
