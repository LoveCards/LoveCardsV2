-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2023-11-13 17:27:23
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
(14, 'testtest', '51abb9636078defbf888d8457a7c76f85c8f114c', 0, '734a1c67-f25d-f2db-9c28-c99912717d73'),
(36, 'admin', 'd033e22ae348aeb5660fc2140aec35850c4da997', 0, 'f0efd61f-becc-7534-a25d-66b4dd11a207'),
(37, 'testtest1', 'd41ff23e0e6147a8fd2722f68e53f993a92784b0', 0, ''),
(38, 'testtest2', 'd10a21cea804aa56aff509fedaae830a96e3a50b', 0, 'a7849d8a-c49f-2f64-0df6-a29ffe994fe3'),
(39, 'testtest4', '7aed54b497a5846e5904dc412d73561905436062', 0, ''),
(40, 'testtest5', '7aed54b497a5846e5904dc412d73561905436062', 0, ''),
(41, '12345', '7c4a8d09ca3762af61e59520943dc26494f8941b', 1, '');

-- --------------------------------------------------------

--
-- 表的结构 `cards`
--

CREATE TABLE `cards` (
  `id` int(11) NOT NULL COMMENT 'cid/pid/aid:1',
  `uid` int(11) NOT NULL,
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

--
-- 转存表中的数据 `cards`
--

INSERT INTO `cards` (`id`, `uid`, `content`, `img`, `woName`, `woContact`, `taName`, `taContact`, `good`, `comments`, `look`, `tag`, `model`, `time`, `ip`, `top`, `status`) VALUES
(11, 0, '只要你愿意，当你失落失意的时候，最需要一个肩膀的时候，告诉我，我会立即出现。', 'https://img1.imgtp.com/2023/09/17/jM853WH8.png', '', '', '王小美', '', 0, 6, 9, '[\"1\"]', 1, '2023-09-17 06:57:03', '127.0.0.1', '0', '0'),
(12, 0, '找找我的🐱求求了!!\n重金感谢！！！', 'https://img1.imgtp.com/2023/09/17/SMuer2ky.png', '王二', '', '', '', 0, 0, 3, '[\"2\"]', 1, '2023-09-17 07:02:01', '127.0.0.1', '0', '0'),
(13, 0, '今天出去玩拍到了，真的很不错分享给大家！！', 'https://img1.imgtp.com/2023/09/17/5vELqxuF.jpg', '马腾', '', '', '', 0, 0, 1, '[\"4\"]', 1, '2023-09-17 07:03:02', '127.0.0.1', '0', '0'),
(14, 0, '真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！', 'https://img1.imgtp.com/2023/09/17/yLC3M1D6.jpg', '纸怪', '', '', '', 1, 0, 5, '[\"4\",\"5\"]', 1, '2023-09-17 07:04:19', '127.0.0.1', '0', '0'),
(15, 0, '哪有什么突然想起，其实是一直藏在心底。12.以往落空落单的每天，在今年被拉住了手，成为了街上一点都不孤单的人，还被小心翼翼的保护了起来。', '/storage/image/20230917\\6f9bd5d6e1f8e0da3acd3528416c78b1.jpg', '', '', '李婷婷', NULL, 0, 0, 5, '[\"3\",\"5\"]', 0, '2023-09-17 07:13:24', '127.0.0.1', '0', '0'),
(16, 0, '妈妈说，人最好不要错过两样东西：最后一班回家的车和一个深爱你的人。这一次，我不想再错过你了。', 'https://img1.imgtp.com/2023/09/17/WVpo8zFA.jpg', '王聪聪', '', '孙菲菲', NULL, 0, 0, 2, '[\"1\",\"3\",\"5\"]', 0, '2023-09-17 07:14:31', '127.0.0.1', '0', '0'),
(17, 0, '不是吧不是吧感谢这位同学', 'https://img1.imgtp.com/2023/09/17/ooOFlMbE.jpg', '1888', '', '2333', '', 0, 0, 4, '[\"4\",\"5\"]', 1, '2023-09-17 07:16:15', '127.0.0.1', '0', '0');

-- --------------------------------------------------------

--
-- 表的结构 `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL COMMENT 'pid/aid:2',
  `aid` int(11) NOT NULL COMMENT '应用ID',
  `pid` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '条目ID',
  `content` varchar(256) COLLATE utf8mb4_general_ci NOT NULL COMMENT '内容',
  `name` varchar(256) COLLATE utf8mb4_general_ci NOT NULL COMMENT '我的名字',
  `ip` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '发布IP',
  `time` timestamp NOT NULL COMMENT '发布时间',
  `status` int(11) NOT NULL COMMENT '封禁状态'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `comments`
--

INSERT INTO `comments` (`id`, `aid`, `pid`, `content`, `name`, `ip`, `time`, `status`) VALUES
(9, 1, '11', '3123213', '12312', '127.0.0.1', '2023-09-19 17:33:36', 0),
(10, 1, '11', '这是一条评论', '评论者名称', '127.0.0.1', '2023-11-10 08:22:11', 0),
(11, 1, '11', '这是一条评论', '评论者名称', '127.0.0.1', '2023-11-10 09:57:27', 0),
(12, 1, '11', '这是一条评论', '评论者名称', '127.0.0.1', '2023-11-10 10:41:22', 0),
(13, 1, '11', '这是一条评论', '评论者名称', '127.0.0.1', '2023-11-10 10:41:34', 0),
(14, 1, '11', '这是一条评论', '评论者名称1', '127.0.0.1', '2023-11-10 10:41:41', 0);

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

--
-- 转存表中的数据 `good`
--

INSERT INTO `good` (`id`, `aid`, `pid`, `ip`, `time`) VALUES
(1, 1, 1, '127.0.0.1', '2023-08-05 00:35:14'),
(2, 1, 4, '127.0.0.1', '2023-08-14 17:58:02'),
(3, 1, 2, '127.0.0.1', '2023-08-22 16:54:46'),
(4, 1, 5, '127.0.0.1', '2023-08-23 18:22:16'),
(5, 1, 3, '127.0.0.1', '2023-08-28 17:22:55'),
(6, 1, 10, '127.0.0.1', '2023-08-30 02:12:43'),
(7, 1, 6, '127.0.0.1', '2023-08-30 02:12:49'),
(8, 1, 14, '127.0.0.1', '2023-10-19 01:02:21');

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

--
-- 转存表中的数据 `img`
--

INSERT INTO `img` (`id`, `aid`, `pid`, `url`, `time`) VALUES
(1, 1, 6, 'https://th.bing.com/th/id/R.d8c8a25dad20c6312e18a82ba026a148?rik=U9SIBedUJ%2fmWkw&riu=http%3a%2f%2fi2.hdslb.com%2fbfs%2farchive%2f6f4462d85ce8fd6ce67dd378dc15d302b5d88d18.png&ehk=d0ShZ76TEhgUrSdWz7RQ374YXx2B64rFXBN0yKLp6U0%3d&risl=&pid=ImgRaw&r=0', '2023-08-18 16:59:39'),
(2, 1, 6, 'https://th.bing.com/th/id/OIP.QUckL7512reTbaawTM00LwHaIt?pid=ImgDet&rs=1', '2023-08-18 16:59:39'),
(7, 1, 10, 'https://test.fatda.cn/uploads/20210808421334740.jpeg', '2023-08-28 09:43:29'),
(8, 1, 10, 'https://test.fatda.cn/uploads/20210704538378743.jpg', '2023-08-28 09:43:29'),
(9, 1, 10, 'https://test.fatda.cn/uploads/202106122043387554.jpg', '2023-08-28 09:43:29'),
(10, 1, 10, 'https://test.fatda.cn/uploads/202109071745186233.jpg', '2023-08-28 09:43:29'),
(11, 1, 9, 'http://test.fatda.cn/uploads/20210704538378743.jpg', '2023-08-28 09:56:17'),
(12, 1, 9, '/storage/image/20230108\\d4775810d03b4db4afa9503bdbff1cb8.jpg', '2023-08-28 09:56:17'),
(13, 1, 9, 'https://test.fatda.cn/uploads/20210704538378743.jpg', '2023-08-28 09:56:17'),
(14, 1, 11, 'https://img1.imgtp.com/2023/09/17/jM853WH8.png', '2023-09-17 06:57:03'),
(15, 1, 11, 'https://img1.imgtp.com/2023/09/17/cBjQG9nL.png', '2023-09-17 06:57:03'),
(16, 1, 12, 'https://img1.imgtp.com/2023/09/17/SMuer2ky.png', '2023-09-17 07:02:01'),
(17, 1, 13, 'https://img1.imgtp.com/2023/09/17/5vELqxuF.jpg', '2023-09-17 07:03:02'),
(18, 1, 14, 'https://img1.imgtp.com/2023/09/17/yLC3M1D6.jpg', '2023-09-17 07:04:19'),
(19, 1, 15, '/storage/image/20230917\\6f9bd5d6e1f8e0da3acd3528416c78b1.jpg', '2023-09-17 07:13:24'),
(20, 1, 16, 'https://img1.imgtp.com/2023/09/17/WVpo8zFA.jpg', '2023-09-17 07:14:31'),
(21, 1, 17, 'https://img1.imgtp.com/2023/09/17/ooOFlMbE.jpg', '2023-09-17 07:16:15');

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
(2, 'siteName', 'LoveCards'),
(3, 'siteICPId', ''),
(4, 'siteKeywords', ''),
(5, 'siteDes', ''),
(6, 'smtpUser', '32112321231312'),
(7, 'smtpHost', '21321'),
(8, 'smtpPort', '312312321321'),
(9, 'smtpPass', '2132132'),
(10, 'siteFooter', '                    <div class=\"mdui-col-xs-12 mdui-col-md-3 mdui-p-y-1\">\r\n                        <div class=\"mdui-typo-headline\">标题</div>\r\n                        <div class=\"mdui-typo-body-2-opacity\">\r\n                            法航客机的撒恢复健康了大厦看见返回的是\r\n                        </div>\r\n                    </div>\r\n                    <div class=\"mdui-col-xs-12 mdui-col-md-3 mdui-p-y-1\">\r\n                        <div class=\"mdui-typo-headline\">标题</div>\r\n                        <div class=\"mdui-typo-body-2-opacity\">\r\n                            法航客机的撒恢复健康了大厦看见返回的是\r\n                        </div>\r\n                    </div>\r\n                    <div class=\"mdui-col-xs-12 mdui-col-md-3 mdui-p-y-1\">\r\n                        <div class=\"mdui-typo-headline\">标题</div>\r\n                        <div class=\"mdui-typo-body-2-opacity\">\r\n                            法航客机的撒恢复健康了大厦看见返回的是\r\n                        </div>\r\n                    </div>\r\n                    <div class=\"mdui-col-xs-12 mdui-col-md-3 mdui-p-y-1\">\r\n                        <div class=\"mdui-typo-headline\">标题</div>\r\n                        <div class=\"mdui-typo-body-2-opacity\">\r\n                            法航客机的撒恢复健康了大厦看见返回的是\r\n                        </div>\r\n                    </div>'),
(11, 'LCEAPI', '12312'),
(12, 'siteCopyright', ''),
(13, 'siteTitle', 'LoveCards'),
(14, 'smtpSecure', '231312'),
(15, 'smtpName', '21321321');

-- --------------------------------------------------------

--
-- 表的结构 `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL COMMENT 'tid/pid',
  `aid` int(11) NOT NULL COMMENT '应用ID',
  `name` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '标签名',
  `tip` varchar(64) COLLATE utf8mb4_general_ci DEFAULT '' COMMENT '提示',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '封禁状态',
  `time` timestamp NOT NULL COMMENT '发布时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `tags`
--

INSERT INTO `tags` (`id`, `aid`, `name`, `tip`, `status`, `time`) VALUES
(1, 1, '说出你的悄悄话', '测试标签', 0, '2023-08-13 17:14:16'),
(2, 1, '寻物', '', 0, '2023-09-17 06:47:56'),
(3, 1, '表白', '', 0, '2023-09-17 06:49:10'),
(4, 1, '分享', '', 0, '2023-09-17 06:51:47'),
(5, 1, '我要上热门', '', 0, '2023-09-17 07:03:29'),
(6, 1, '31232', '213', 1, '2023-11-08 07:22:56'),
(7, 1, '风格的和广泛的', '官方的花费更多', 0, '2023-11-08 07:56:17');

-- --------------------------------------------------------

--
-- 表的结构 `tags_map`
--

CREATE TABLE `tags_map` (
  `id` int(11) NOT NULL COMMENT 'pid',
  `cid` int(11) DEFAULT NULL COMMENT 'CardsID',
  `tid` int(11) DEFAULT NULL COMMENT 'TagID',
  `time` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `tags_map`
--

INSERT INTO `tags_map` (`id`, `cid`, `tid`, `time`) VALUES
(1, 5, 1, '2023-08-13 17:14:40'),
(2, 6, 1, '2023-08-18 16:59:39'),
(6, 10, 1, '2023-08-28 09:43:29'),
(7, 10, 2, '2023-08-28 09:43:29'),
(8, 10, 3, '2023-08-28 09:43:29'),
(9, 9, 1, '2023-08-28 09:56:17'),
(10, 9, 2, '2023-08-28 09:56:17'),
(11, 9, 3, '2023-08-28 09:56:17'),
(12, 11, 1, '2023-09-17 06:57:03'),
(13, 12, 2, '2023-09-17 07:02:01'),
(14, 13, 4, '2023-09-17 07:03:02'),
(15, 14, 4, '2023-09-17 07:04:19'),
(16, 14, 5, '2023-09-17 07:04:19'),
(17, 15, 3, '2023-09-17 07:13:24'),
(18, 15, 5, '2023-09-17 07:13:24'),
(19, 16, 1, '2023-09-17 07:14:31'),
(20, 16, 3, '2023-09-17 07:14:31'),
(21, 16, 5, '2023-09-17 07:14:31'),
(22, 17, 4, '2023-09-17 07:16:15'),
(23, 17, 5, '2023-09-17 07:16:15');

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `account` varchar(32) COLLATE utf8mb4_general_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(320) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` int(20) NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime NOT NULL,
  `status` int(11) NOT NULL
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
-- 表的索引 `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- 使用表AUTO_INCREMENT `cards`
--
ALTER TABLE `cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'cid/pid/aid:1', AUTO_INCREMENT=18;

--
-- 使用表AUTO_INCREMENT `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'pid/aid:2', AUTO_INCREMENT=15;

--
-- 使用表AUTO_INCREMENT `good`
--
ALTER TABLE `good`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 使用表AUTO_INCREMENT `img`
--
ALTER TABLE `img`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- 使用表AUTO_INCREMENT `system`
--
ALTER TABLE `system`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- 使用表AUTO_INCREMENT `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'tid/pid', AUTO_INCREMENT=8;

--
-- 使用表AUTO_INCREMENT `tags_map`
--
ALTER TABLE `tags_map`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'pid', AUTO_INCREMENT=24;

--
-- 使用表AUTO_INCREMENT `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
