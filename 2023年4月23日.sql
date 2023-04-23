-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2023-04-23 23:56:49
-- 服务器版本： 5.7.40-log
-- PHP 版本： 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `quyihang_ltd`
--

-- --------------------------------------------------------

--
-- 表的结构 `cards`
--

CREATE TABLE `cards` (
  `id` int(11) NOT NULL COMMENT 'cid/pid/aid:1',
  `content` mediumtext NOT NULL COMMENT '内容',
  `img` varchar(256) DEFAULT '' COMMENT '封面',
  `woName` varchar(256) DEFAULT '' COMMENT '发布者名字',
  `woContact` varchar(256) DEFAULT '' COMMENT '我的联系方式',
  `taName` varchar(256) DEFAULT '' COMMENT '对方的名字',
  `taContact` varchar(256) DEFAULT '' COMMENT '对方的联系方式',
  `good` int(11) NOT NULL DEFAULT '0' COMMENT '点赞数',
  `comments` int(11) NOT NULL DEFAULT '0' COMMENT '评论数',
  `look` int(11) NOT NULL DEFAULT '0',
  `tag` varchar(256) DEFAULT '' COMMENT '标签Json',
  `model` int(11) DEFAULT '0' COMMENT '卡片模式',
  `time` timestamp NOT NULL COMMENT '上传时间',
  `ip` varchar(256) DEFAULT '' COMMENT '上传IP',
  `top` int(11) DEFAULT '0' COMMENT '置顶',
  `state` int(11) DEFAULT '0' COMMENT '状态'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `cards`
--

INSERT INTO `cards` (`id`, `content`, `img`, `woName`, `woContact`, `taName`, `taContact`, `good`, `comments`, `look`, `tag`, `model`, `time`, `ip`, `top`, `state`) VALUES
(1, 'LoveCards简约而不简单', 'http://5b0988e595225.cdn.sohucs.com/images/20170922/eaab95242c534e11b29f9fdd407f0e3c.jpeg', '吃纸怪', '', '', '', 0, 0, 1, '', 1, '2023-04-23 23:56:21', '223.104.19.72', 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `cards_comments`
--

CREATE TABLE `cards_comments` (
  `id` int(11) NOT NULL COMMENT 'pid/aid:2',
  `cid` varchar(256) NOT NULL COMMENT 'CardsID',
  `content` varchar(256) NOT NULL COMMENT '内容',
  `name` varchar(256) NOT NULL COMMENT '我的名字',
  `ip` varchar(256) NOT NULL COMMENT '发布者IP',
  `time` timestamp NOT NULL COMMENT '发布时间',
  `state` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `cards_tag`
--

CREATE TABLE `cards_tag` (
  `id` int(11) NOT NULL COMMENT 'pid',
  `name` varchar(8) DEFAULT '',
  `tip` varchar(64) DEFAULT '' COMMENT '提示',
  `state` int(11) NOT NULL DEFAULT '0',
  `time` timestamp NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `cards_tag_map`
--

CREATE TABLE `cards_tag_map` (
  `id` int(11) NOT NULL,
  `cid` int(11) DEFAULT NULL COMMENT 'CardsID',
  `tid` int(11) DEFAULT NULL COMMENT 'TagID',
  `time` timestamp NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `good`
--

CREATE TABLE `good` (
  `id` int(11) NOT NULL,
  `aid` int(11) NOT NULL COMMENT '应用ID',
  `pid` int(11) NOT NULL COMMENT '条目ID',
  `ip` varchar(32) NOT NULL,
  `time` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `img`
--

CREATE TABLE `img` (
  `id` int(11) NOT NULL,
  `aid` int(11) NOT NULL COMMENT '应用ID',
  `pid` int(11) NOT NULL COMMENT '条目ID',
  `url` varchar(256) NOT NULL,
  `time` timestamp NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `img`
--

INSERT INTO `img` (`id`, `aid`, `pid`, `url`, `time`) VALUES
(1, 1, 1, 'http://5b0988e595225.cdn.sohucs.com/images/20170922/eaab95242c534e11b29f9fdd407f0e3c.jpeg', '2023-04-23 23:56:21');

-- --------------------------------------------------------

--
-- 表的结构 `system`
--

CREATE TABLE `system` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT '',
  `value` varchar(2555) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `system`
--

INSERT INTO `system` (`id`, `name`, `value`) VALUES
(1, 'siteUrl', 'lovecards.cn'),
(2, 'siteName', 'LC2测试版本'),
(3, 'siteICPId', '备案信息'),
(4, 'siteKeywords', 'LoveCards,lovecards.cn,吃纸怪'),
(5, 'siteDes', 'LoveCards2测试版本'),
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

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `userName` varchar(32) NOT NULL,
  `password` varchar(64) NOT NULL,
  `power` int(11) NOT NULL DEFAULT '0',
  `uuid` varchar(64) DEFAULT '' COMMENT '登入凭证'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `user`
--

INSERT INTO `user` (`id`, `userName`, `password`, `power`, `uuid`) VALUES
(1, 'testtest', '51abb9636078defbf888d8457a7c76f85c8f114c', 0, '');

--
-- 转储表的索引
--

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
-- 表的索引 `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `cards`
--
ALTER TABLE `cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'cid/pid/aid:1', AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `cards_comments`
--
ALTER TABLE `cards_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'pid/aid:2';

--
-- 使用表AUTO_INCREMENT `cards_tag`
--
ALTER TABLE `cards_tag`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'pid';

--
-- 使用表AUTO_INCREMENT `cards_tag_map`
--
ALTER TABLE `cards_tag_map`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `good`
--
ALTER TABLE `good`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `img`
--
ALTER TABLE `img`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `system`
--
ALTER TABLE `system`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- 使用表AUTO_INCREMENT `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
