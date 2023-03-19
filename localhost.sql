-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2023-03-13 17:52:46
-- 服务器版本： 5.6.50-log
-- PHP 版本： 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `sxy_fatda_cn`
--
CREATE DATABASE IF NOT EXISTS `sxy_fatda_cn` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `sxy_fatda_cn`;

-- --------------------------------------------------------

--
-- 表的结构 `cards`
--

CREATE TABLE `cards` (
  `id` int(11) NOT NULL COMMENT 'cid/pid/aid:1',
  `content` text NOT NULL COMMENT '内容',
  `img` varchar(256) DEFAULT '' COMMENT '封面',
  `woName` varchar(256) DEFAULT '' COMMENT '发布者名字',
  `woContact` varchar(256) DEFAULT '' COMMENT '我的联系方式',
  `taName` varchar(256) DEFAULT '' COMMENT '对方的名字',
  `taContact` varchar(256) DEFAULT '' COMMENT '对方的联系方式',
  `good` int(11) NOT NULL DEFAULT '0' COMMENT '点赞数',
  `comments` int(11) NOT NULL DEFAULT '0' COMMENT '评论数',
  `tag` varchar(256) DEFAULT '' COMMENT '标签Json',
  `model` int(11) DEFAULT '0' COMMENT '卡片模式',
  `time` timestamp NOT NULL COMMENT '上传时间',
  `ip` varchar(256) DEFAULT '' COMMENT '上传IP',
  `top` int(11) DEFAULT '0' COMMENT '置顶',
  `state` int(11) DEFAULT '0' COMMENT '状态'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `cards_tag_map`
--

CREATE TABLE `cards_tag_map` (
  `id` int(11) NOT NULL,
  `cid` int(11) DEFAULT NULL COMMENT 'CardsID',
  `tid` int(11) DEFAULT NULL COMMENT 'TagID',
  `time` timestamp NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `system`
--

CREATE TABLE `system` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT '',
  `value` varchar(2555) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `system`
--

INSERT INTO `system` (`id`, `name`, `value`) VALUES
(1, 'siteUrl', 'lovecards.cn'),
(2, 'siteName', 'LoveCards'),
(3, 'siteICPId', '备案信息12321'),
(4, 'siteKeywords', '关键词2133123'),
(5, 'siteDes', 'description123123'),
(6, 'smtpUser', '32112321231312'),
(7, 'smtpHost', '21321'),
(8, 'smtpPort', '312312321321'),
(9, 'smtpPass', '2132132'),
(10, 'siteFooter', '                    <div class=\"mdui-col-xs-12 mdui-col-md-3 mdui-p-y-1\">\r\n                        <div class=\"mdui-typo-headline\">标题</div>\r\n                        <div class=\"mdui-typo-body-2-opacity\">\r\n                            法航客机的撒恢复健康了大厦看见返回的是\r\n                        </div>\r\n                    </div>\r\n                    <div class=\"mdui-col-xs-12 mdui-col-md-3 mdui-p-y-1\">\r\n                        <div class=\"mdui-typo-headline\">标题</div>\r\n                        <div class=\"mdui-typo-body-2-opacity\">\r\n                            法航客机的撒恢复健康了大厦看见返回的是\r\n                        </div>\r\n                    </div>\r\n                    <div class=\"mdui-col-xs-12 mdui-col-md-3 mdui-p-y-1\">\r\n                        <div class=\"mdui-typo-headline\">标题</div>\r\n                        <div class=\"mdui-typo-body-2-opacity\">\r\n                            法航客机的撒恢复健康了大厦看见返回的是\r\n                        </div>\r\n                    </div>\r\n                    <div class=\"mdui-col-xs-12 mdui-col-md-3 mdui-p-y-1\">\r\n                        <div class=\"mdui-typo-headline\">标题</div>\r\n                        <div class=\"mdui-typo-body-2-opacity\">\r\n                            法航客机的撒恢复健康了大厦看见返回的是\r\n                        </div>\r\n                    </div>'),
(11, 'LCEAPI', '12312'),
(12, 'siteCopyright', '©1995-2004 Eric A. and Kathryn S. Meyer. All Rights Reserved.All Rights Reserved－保留所有权利'),
(13, 'siteTitle', 'site21312213123'),
(14, 'smtpSecure', '231312'),
(15, 'smtpName', '21321321');

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `user`
--

INSERT INTO `user` (`id`, `userName`, `password`, `power`, `uuid`) VALUES
(1, 'admin', '7c4a8d09ca3762af61e59520943dc26494f8941b', 0, '19df3be5-2c72-cd76-9e75-ccd16298a1e3');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `system`
--
ALTER TABLE `system`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- 使用表AUTO_INCREMENT `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
