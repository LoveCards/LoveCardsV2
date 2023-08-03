-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2023-08-03 20:58:14
-- 服务器版本： 8.0.12
-- PHP 版本： 7.3.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `127_0_0_1`
--

-- --------------------------------------------------------

--
-- 表的结构 `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `userName` varchar(32) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(64) COLLATE utf8mb4_general_ci NOT NULL,
  `power` int(11) NOT NULL DEFAULT '0',
  `uuid` varchar(64) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '登入凭证'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `admin`
--

INSERT INTO `admin` (`id`, `userName`, `password`, `power`, `uuid`) VALUES
(1, 'admin', '', 0, '');

-- --------------------------------------------------------

--
-- 表的结构 `cards`
--

CREATE TABLE `cards` (
  `id` int(11) NOT NULL COMMENT 'cid/pid/aid:1',
  `content` mediumtext COLLATE utf8mb4_general_ci NOT NULL COMMENT '内容',
  `img` varchar(256) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '封面',
  `woName` varchar(256) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '发布者名字',
  `woContact` varchar(256) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '我的联系方式',
  `taName` varchar(256) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '对方的名字',
  `taContact` varchar(256) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '对方的联系方式',
  `good` int(11) NOT NULL DEFAULT '0' COMMENT '点赞数',
  `comments` int(11) NOT NULL DEFAULT '0' COMMENT '评论数',
  `look` int(11) NOT NULL DEFAULT '0' COMMENT '浏览量',
  `tag` varchar(256) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '标签Json',
  `model` int(11) DEFAULT '0' COMMENT '卡片模式',
  `time` timestamp NOT NULL COMMENT '发布时间',
  `ip` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '发布IP',
  `top` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '0' COMMENT '置顶状态',
  `status` enum('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '0' COMMENT '封禁状态'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `cards_comments`
--

CREATE TABLE `cards_comments` (
  `id` int(11) NOT NULL COMMENT 'pid/aid:2',
  `cid` varchar(256) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'CardsID',
  `content` varchar(256) COLLATE utf8mb4_general_ci NOT NULL COMMENT '内容',
  `name` varchar(256) COLLATE utf8mb4_general_ci NOT NULL COMMENT '我的名字',
  `ip` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '发布IP',
  `time` timestamp NOT NULL COMMENT '发布时间',
  `status` int(11) NOT NULL COMMENT '封禁状态'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `cards_tag`
--

CREATE TABLE `cards_tag` (
  `id` int(11) NOT NULL COMMENT 'tid/pid',
  `name` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '标签名',
  `tip` varchar(64) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '提示',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '封禁状态',
  `time` timestamp NOT NULL COMMENT '发布时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `cards_tag_map`
--

CREATE TABLE `cards_tag_map` (
  `id` int(11) NOT NULL COMMENT 'pid',
  `cid` int(11) DEFAULT NULL COMMENT 'CardsID',
  `tid` int(11) DEFAULT NULL COMMENT 'TagID',
  `time` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `good`
--

CREATE TABLE `good` (
  `id` int(11) NOT NULL,
  `aid` int(11) NOT NULL COMMENT '应用ID',
  `pid` int(11) NOT NULL COMMENT '条目ID',
  `ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '发布IP',
  `time` datetime NOT NULL COMMENT '发布时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `img`
--

CREATE TABLE `img` (
  `id` int(11) NOT NULL,
  `aid` int(11) NOT NULL COMMENT '应用ID',
  `pid` int(11) NOT NULL COMMENT '条目ID',
  `url` varchar(256) COLLATE utf8mb4_general_ci NOT NULL,
  `time` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `system`
--

CREATE TABLE `system` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `value` varchar(2555) COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `system`
--

INSERT INTO `system` (`id`, `name`, `value`) VALUES
(1, 'siteUrl', 'lovecards.cn'),
(2, 'siteName', 'LoveCardsv2'),
(3, 'siteICPId', '备案信息'),
(4, 'siteKeywords', 'LoveCards,lovecards.cn,吃纸怪'),
(5, 'siteDes', 'LoveCardsv2'),
(6, 'smtpUser', ''),
(7, 'smtpHost', ''),
(8, 'smtpPort', ''),
(9, 'smtpPass', ''),
(10, 'siteFooter', ''),
(11, 'LCEAPI', ''),
(12, 'siteCopyright', '©2018-2023 LoveCards. All Rights Reserved.All Rights Reserved－保留所有权利'),
(13, 'siteTitle', ''),
(14, 'smtpSecure', ''),
(15, 'smtpName', '');

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
-- 表的索引 `cards_comments`
--
ALTER TABLE `cards_comments`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `cards_tag`
--
ALTER TABLE `cards_tag`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `cards_tag_map`
--
ALTER TABLE `cards_tag_map`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `good`
--
ALTER TABLE `good`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `img`
--
ALTER TABLE `img`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `system`
--
ALTER TABLE `system`
  ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `cards`
--
ALTER TABLE `cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'cid/pid/aid:1';

--
-- 使用表AUTO_INCREMENT `cards_comments`
--
ALTER TABLE `cards_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'pid/aid:2';

--
-- 使用表AUTO_INCREMENT `cards_tag`
--
ALTER TABLE `cards_tag`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'tid/pid';

--
-- 使用表AUTO_INCREMENT `cards_tag_map`
--
ALTER TABLE `cards_tag_map`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'pid';

--
-- 使用表AUTO_INCREMENT `good`
--
ALTER TABLE `good`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `img`
--
ALTER TABLE `img`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `system`
--
ALTER TABLE `system`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
