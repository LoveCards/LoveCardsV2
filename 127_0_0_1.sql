-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2023-01-28 21:15:29
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
-- 表的结构 `cards`
--

CREATE TABLE `cards` (
  `id` int(11) NOT NULL COMMENT 'cid/pid/aid:1',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '内容',
  `img` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT '封面',
  `woName` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT '发布者名字',
  `woContact` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT '我的联系方式',
  `taName` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT '对方的名字',
  `taContact` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT '对方的联系方式',
  `good` int(11) NOT NULL DEFAULT '0' COMMENT '点赞数',
  `comments` int(11) NOT NULL DEFAULT '0' COMMENT '评论数',
  `tag` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT '标签Json',
  `model` int(11) DEFAULT '0' COMMENT '卡片模式',
  `time` timestamp NOT NULL COMMENT '上传时间',
  `ip` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT '上传IP',
  `top` int(11) DEFAULT '0' COMMENT '置顶',
  `state` int(11) DEFAULT '0' COMMENT '状态'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `cards`
--

INSERT INTO `cards` (`id`, `content`, `img`, `woName`, `woContact`, `taName`, `taContact`, `good`, `comments`, `tag`, `model`, `time`, `ip`, `top`, `state`) VALUES
(51, '撒打撒打撒噶地方是发大水范德萨发士大夫第三方的吗，放的那首，美国你的父母，丧命，的封神榜，你们地方不能刷卡，给你的付款了。丧礼。父母都是那么了。过度泛滥。给你们的吗，。十方明亮。发到你什么，。给你的父母，。桑，。对付你们，。对方感冒了。你', '', '', '', '大撒大撒', '', 3, 2, '[\"1\",\"2\",\"4\"]', 1, '2023-01-18 12:28:36', '127.0.0.1', 1, 0),
(52, 'asdasdsa', '', 'dsadas', 'sadsa', 'dasdsa', NULL, 2, 0, '', 0, '2022-12-29 13:18:14', '192.168.3.25', 0, 0),
(53, 'asdasdassad', '/storage/image/20221229\\f61cbe1d6f64a02a13f841f72a7c9d64.png', 'sadasdas', 'sadas', 'adasdas', NULL, 3, 5, '', 0, '2022-12-29 13:19:45', '192.168.3.25', 0, 0),
(54, 'asdasdas', '', '', '', 'sadasd', NULL, 1, 0, '', 0, '2022-12-29 13:21:34', '192.168.3.25', 0, 0),
(55, 'ADSFDAS', '', '', '', 'ADFDSAFSD', NULL, 3, 0, '', 0, '2022-12-29 13:22:41', '192.168.3.25', 0, 0),
(56, 'hgfjhgf', '', '', '', 'ghjfghjhg', NULL, 2, 0, '', 0, '2022-12-29 13:23:59', '192.168.3.25', 0, 0),
(57, 'dfsafdsa', '', '', '', 'fasdfdsa', NULL, 1, 0, '', 0, '2022-12-29 13:24:13', '192.168.3.25', 0, 0),
(58, 'dsafdsafd', '', '', '', 'asdfas', NULL, 3, 0, '', 0, '2022-12-29 13:24:30', '192.168.3.25', 0, 0),
(59, 'fdgfdgfd', '', '', '', 'fdgfd', NULL, 2, 0, '', 0, '2022-12-29 15:46:56', '192.168.3.25', 0, 0),
(60, 'fdgfdgfd', '', '', '', 'fdgfd', NULL, 0, 0, '', 0, '2022-12-29 15:47:01', '192.168.3.25', 0, 0),
(62, 'dasdasdsadsa', '', '', '', 'sadasdas', '', 1, 0, '', 0, '2023-01-06 11:26:42', '192.168.3.25', 0, 0),
(63, 'afdsafdsafsda', '', '', '', 'fsdafdsafds', '', 1, 0, '', 0, '2023-01-06 11:29:36', '192.168.3.25', 0, 0),
(64, '43543', '', '', '', '534534534', '', 1, 0, '', 0, '2023-01-06 11:31:37', '192.168.3.25', 0, 0),
(65, '43543', '', '', '', '534534534', '', 2, 0, '', 0, '2023-01-06 11:31:45', '192.168.3.25', 0, 0),
(66, '43543', '', '', '', '534534534', '', 1, 0, '', 0, '2023-01-06 11:31:49', '192.168.3.25', 0, 0),
(67, '43543', '', '', '', '534534534', '', 1, 0, '', 0, '2023-01-06 11:32:49', '192.168.3.25', 0, 0),
(68, '43543', '', '', '', '534534534', '', 1, 0, '', 0, '2023-01-06 11:33:08', '192.168.3.25', 0, 0),
(69, 'sadas', '', '', '', '搞活经济韩国', '', 1, 0, '[\"2\",\"3\",\"4\"]', 0, '2023-01-06 11:33:51', '192.168.3.25', 0, 0),
(70, '撒大大实打实', '', '大撒大撒', 'sdad', '大撒大撒大撒', '', 0, 0, '', 0, '2023-01-06 11:37:56', '192.168.3.25', 0, 0),
(71, '撒大大实打实', '', '大撒大撒', 'sdad', '大撒大撒大撒', '', 1, 0, '[\"1\",\"2\",\"70\"]', 0, '2023-01-06 11:38:00', '192.168.3.25', 0, 0),
(72, ' 的撒范德萨范德萨撒旦', 'https://test.fatda.cn/uploads/20210704538378743.jpg', '阿斯顿发射点', '阿斯蒂芬十大ds', '发的地方', '的萨芬的撒fdsa', 1, 0, '', 0, '2023-01-08 13:06:20', '192.168.3.25', 0, 0),
(73, '的撒范德萨范德萨撒旦432432', 'http://test.fatda.cn/uploads/20210704538378743.jpg', '阿斯顿发射点324', '阿斯蒂芬十大ds32432', '发的地方', '的萨芬的撒fdsa', 7, 0, '[\"1\",\"2\",\"3\"]', 1, '2023-01-08 14:47:17', '127.0.0.1', 0, 1),
(74, '的飞洒范德萨', '', '', '', '犯得上反对', '', 1, 0, '[\"2\",\"3\",\"4\"]', 0, '2023-01-10 10:48:47', '192.168.3.25', 0, 0),
(75, '432432432', 'https://test.fatda.cn/uploads/20210704538378743.jpg', '11111111111', '', '432432', '', 1, 1, '', 1, '2023-01-18 12:12:59', '127.0.0.1', 1, 0);

-- --------------------------------------------------------

--
-- 表的结构 `cards_comments`
--

CREATE TABLE `cards_comments` (
  `id` int(11) NOT NULL COMMENT 'pid/aid:2',
  `cid` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'CardsID',
  `content` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '内容',
  `name` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '我的名字',
  `ip` varchar(256) NOT NULL COMMENT '发布者IP',
  `time` timestamp NOT NULL COMMENT '发布时间',
  `state` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `cards_comments`
--

INSERT INTO `cards_comments` (`id`, `cid`, `content`, `name`, `ip`, `time`, `state`) VALUES
(24, '51', 'cvxzvcxz', 'cxvzcx', '127.0.0.1', '2023-01-20 11:14:31', 0),
(22, '53', 'rtgrtegtre342444', 'trgtre3244', '127.0.0.1', '2023-01-13 17:24:19', 0),
(21, '53', '231321', '21321', '127.0.0.1', '2023-01-13 17:24:11', 0),
(16, '53', '321321', '1321', '127.0.0.1', '2023-01-13 17:10:27', 0),
(17, '53', '213321', '231123', '127.0.0.1', '2023-01-13 17:15:13', 0),
(18, '53', '213321', '213213', '127.0.0.1', '2023-01-13 17:16:01', 0),
(19, '53', '1231', '123', '127.0.0.1', '2023-01-13 17:16:06', 0),
(20, '53', '321312', '21321', '127.0.0.1', '2023-01-13 17:24:07', 0),
(23, '51', '231321', '1213', '127.0.0.1', '2023-01-20 11:14:28', 0),
(14, '53', '213', '21321312', '127.0.0.1', '2023-01-12 16:46:10', 1),
(15, '53', '213', '21321312', '127.0.0.1', '2023-01-12 16:47:17', 0),
(25, '75', '432432', '342', '127.0.0.1', '2023-01-28 09:08:36', 0);

-- --------------------------------------------------------

--
-- 表的结构 `cards_tag`
--

CREATE TABLE `cards_tag` (
  `id` int(11) NOT NULL COMMENT 'pid',
  `name` varchar(8) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  `tip` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT '提示',
  `state` int(11) NOT NULL DEFAULT '0',
  `time` timestamp NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `cards_tag`
--

INSERT INTO `cards_tag` (`id`, `name`, `tip`, `state`, `time`) VALUES
(1, '标签23423', '2133453453454353453454399', 0, '2023-01-03 16:34:30'),
(2, '标签2', '官方的花费更多', 0, '2023-01-03 16:34:41'),
(3, '标签3', '官方的花费更多', 0, '2023-01-03 16:34:41'),
(4, '标签4', '官方的花费更多', 0, '2023-01-03 16:34:42'),
(70, '435', '43543543', 0, '2023-01-05 09:55:21');

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

--
-- 转存表中的数据 `cards_tag_map`
--

INSERT INTO `cards_tag_map` (`id`, `cid`, `tid`, `time`) VALUES
(24, 71, 1, '2023-01-06 11:38:00'),
(23, 69, 4, '2023-01-06 11:33:51'),
(22, 69, 3, '2023-01-06 11:33:51'),
(21, 69, 2, '2023-01-06 11:33:51'),
(20, 63, 3, '2023-01-06 11:29:36'),
(19, 63, 2, '2023-01-06 11:29:36'),
(25, 71, 2, '2023-01-06 11:38:00'),
(26, 71, 70, '2023-01-06 11:38:00'),
(107, 73, 3, '2023-01-08 14:47:17'),
(106, 73, 2, '2023-01-08 14:47:17'),
(105, 73, 1, '2023-01-08 14:47:17'),
(108, 74, 2, '2023-01-10 10:48:47'),
(109, 74, 3, '2023-01-10 10:48:47'),
(110, 74, 4, '2023-01-10 10:48:47'),
(125, 51, 4, '2023-01-18 12:28:36'),
(124, 51, 2, '2023-01-18 12:28:36'),
(123, 51, 1, '2023-01-18 12:28:36');

-- --------------------------------------------------------

--
-- 表的结构 `good`
--

CREATE TABLE `good` (
  `id` int(11) NOT NULL,
  `aid` int(11) NOT NULL COMMENT '应用ID',
  `pid` int(11) NOT NULL COMMENT '条目ID',
  `ip` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `time` date NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `good`
--

INSERT INTO `good` (`id`, `aid`, `pid`, `ip`, `time`) VALUES
(21, 1, 73, '192.168.3.25', '2023-01-10'),
(20, 1, 55, '192.168.3.25', '2023-01-10'),
(19, 1, 53, '192.168.3.25', '2023-01-10'),
(18, 1, 51, '192.168.3.25', '2023-01-10'),
(17, 1, 59, '192.168.3.25', '2023-01-10'),
(16, 1, 66, '192.168.3.25', '2023-01-10'),
(15, 1, 65, '192.168.3.25', '2023-01-10'),
(14, 1, 64, '192.168.3.25', '2023-01-10'),
(13, 1, 52, '192.168.3.25', '2023-01-10'),
(22, 1, 56, '192.168.3.25', '2023-01-11'),
(23, 1, 62, '192.168.3.25', '2023-01-11'),
(24, 1, 58, '192.168.3.25', '2023-01-11'),
(25, 1, 69, '192.168.3.25', '2023-01-11'),
(26, 1, 53, '127.0.0.1', '2023-01-12'),
(27, 1, 73, '127.0.0.1', '2023-01-12'),
(28, 1, 55, '127.0.0.1', '2023-01-13'),
(29, 1, 52, '127.0.0.1', '2023-01-14'),
(30, 1, 75, '127.0.0.1', '2023-01-16'),
(31, 1, 74, '127.0.0.1', '2023-01-16'),
(32, 1, 72, '127.0.0.1', '2023-01-16'),
(33, 1, 58, '127.0.0.1', '2023-01-16'),
(34, 1, 63, '127.0.0.1', '2023-01-16'),
(35, 1, 71, '127.0.0.1', '2023-01-17'),
(36, 1, 65, '127.0.0.1', '2023-01-18'),
(37, 1, 68, '127.0.0.1', '2023-01-18'),
(38, 1, 51, '127.0.0.1', '2023-01-20'),
(39, 1, 67, '127.0.0.1', '2023-01-28');

-- --------------------------------------------------------

--
-- 表的结构 `img`
--

CREATE TABLE `img` (
  `id` int(11) NOT NULL,
  `aid` int(11) NOT NULL COMMENT '应用ID',
  `pid` int(11) NOT NULL COMMENT '条目ID',
  `url` varchar(256) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `time` timestamp NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `img`
--

INSERT INTO `img` (`id`, `aid`, `pid`, `url`, `time`) VALUES
(57, 1, 49, '/storage/image/20221229\\cdf4ddbec5cd7bc6ac0fbf6981514a85.png', '2022-12-29 12:35:45'),
(56, 1, 48, '/storage/image/20221229\\cdf4ddbec5cd7bc6ac0fbf6981514a85.png', '2022-12-29 12:33:44'),
(55, 1, 47, '/storage/image/20221229\\cdf4ddbec5cd7bc6ac0fbf6981514a85.png', '2022-12-29 12:33:27'),
(54, 1, 46, '/storage/image/20221229\\471b7dd8c7f81320a7b95c692442e80c.png', '2022-12-29 12:22:47'),
(53, 1, 37, '/storage/image/20221224\\73dd93be319990a4e233d40f66ddefe2.png', '2022-12-24 06:34:14'),
(52, 1, 37, '/storage/image/20221224\\73dd93be319990a4e233d40f66ddefe2.png', '2022-12-24 06:34:14'),
(51, 1, 37, 'https://test.fatda.cn/uploads/20210704538378743.jpg', '2022-12-24 06:34:14'),
(50, 1, 37, 'https://test.fatda.cn/uploads/20210704538378743.jpg', '2022-12-24 06:34:14'),
(49, 1, 36, 'https://test.fatda.cn/uploads/20210704538378743.jpg', '2022-12-24 06:33:21'),
(58, 1, 53, '/storage/image/20221229\\f61cbe1d6f64a02a13f841f72a7c9d64.png', '2022-12-29 13:19:45'),
(59, 1, 72, 'https://test.fatda.cn/uploads/20210704538378743.jpg', '2023-01-08 13:06:20'),
(60, 1, 72, 'http://test.fatda.cn/uploads/20210704538378743.jpg', '2023-01-08 13:06:20'),
(61, 1, 72, '/storage/image/20230108\\d4775810d03b4db4afa9503bdbff1cb8.jpg', '2023-01-08 13:06:20'),
(218, 1, 73, 'https://test.fatda.cn/uploads/20210704538378743.jpg', '2023-01-08 14:47:17'),
(217, 1, 73, '/storage/image/20230108\\d4775810d03b4db4afa9503bdbff1cb8.jpg', '2023-01-08 14:47:17'),
(216, 1, 73, 'http://test.fatda.cn/uploads/20210704538378743.jpg', '2023-01-08 14:47:17'),
(263, 1, 75, 'http://test.fatda.cn/uploads/20210704538378743.jpg', '2023-01-18 12:12:59'),
(262, 1, 75, 'http://test.fatda.cn/uploads/20210704538378743.jpg', '2023-01-18 12:12:59'),
(261, 1, 75, 'https://test.fatda.cn/uploads/20210704538378743.jpg', '2023-01-18 12:12:59');

-- --------------------------------------------------------

--
-- 表的结构 `system`
--

CREATE TABLE `system` (
  `id` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '',
  `value` varchar(2555) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''
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
  `userName` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `password` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `power` int(11) NOT NULL DEFAULT '0',
  `uuid` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT '登入凭证'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `user`
--

INSERT INTO `user` (`id`, `userName`, `password`, `power`, `uuid`) VALUES
(34, 'adminadmin', 'dd94709528bb1c83d08f3088d4043f4742891f4f', 0, ''),
(14, 'testtest', '51abb9636078defbf888d8457a7c76f85c8f114c', 0, 'eea288a7-0a7c-e81e-c24d-4bf64dacacbe'),
(33, 'admin', 'd033e22ae348aeb5660fc2140aec35850c4da997', 1, NULL);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'cid/pid/aid:1', AUTO_INCREMENT=76;

--
-- 使用表AUTO_INCREMENT `cards_comments`
--
ALTER TABLE `cards_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'pid/aid:2', AUTO_INCREMENT=26;

--
-- 使用表AUTO_INCREMENT `cards_tag`
--
ALTER TABLE `cards_tag`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'pid', AUTO_INCREMENT=77;

--
-- 使用表AUTO_INCREMENT `cards_tag_map`
--
ALTER TABLE `cards_tag_map`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- 使用表AUTO_INCREMENT `good`
--
ALTER TABLE `good`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- 使用表AUTO_INCREMENT `img`
--
ALTER TABLE `img`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=264;

--
-- 使用表AUTO_INCREMENT `system`
--
ALTER TABLE `system`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- 使用表AUTO_INCREMENT `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
