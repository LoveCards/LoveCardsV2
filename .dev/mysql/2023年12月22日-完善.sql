-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2023-12-22 17:35:32
-- 服务器版本： 5.7.26
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
-- 数据库： `lovecardsdev`
--

-- --------------------------------------------------------

--
-- 表的结构 `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `userName` varchar(32) NOT NULL,
  `password` varchar(64) NOT NULL,
  `power` int(11) NOT NULL DEFAULT '0',
  `uuid` varchar(64) DEFAULT '' COMMENT '登入凭证'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

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
(41, '123450', '5f92fe66cb96a9c52c30cd1fd4087031221e13dd', 0, '');

-- --------------------------------------------------------

--
-- 表的结构 `cards`
--

CREATE TABLE `cards` (
  `id` int(11) NOT NULL COMMENT 'cid/pid/aid:1',
  `uid` int(11) NOT NULL DEFAULT '0',
  `content` mediumtext NOT NULL COMMENT '内容',
  `img` varchar(256) DEFAULT '' COMMENT '封面',
  `woName` varchar(256) DEFAULT '' COMMENT '发布者名字',
  `woContact` varchar(256) DEFAULT '' COMMENT '我的联系方式',
  `taName` varchar(256) DEFAULT '' COMMENT '对方的名字',
  `taContact` varchar(256) DEFAULT '' COMMENT '对方的联系方式',
  `good` int(11) NOT NULL DEFAULT '0' COMMENT '点赞数',
  `comments` int(11) NOT NULL DEFAULT '0' COMMENT '评论数',
  `look` int(11) NOT NULL DEFAULT '0' COMMENT '浏览量',
  `tag` varchar(256) DEFAULT '' COMMENT '标签Json',
  `model` int(11) DEFAULT '0' COMMENT '卡片模式',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '发布时间',
  `ip` varchar(256) DEFAULT '' COMMENT '发布IP',
  `top` enum('0','1') DEFAULT '0' COMMENT '置顶状态',
  `status` enum('0','1') DEFAULT '0' COMMENT '封禁状态'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `cards`
--

INSERT INTO `cards` (`id`, `uid`, `content`, `img`, `woName`, `woContact`, `taName`, `taContact`, `good`, `comments`, `look`, `tag`, `model`, `time`, `ip`, `top`, `status`) VALUES
(12, 0, '找找我的🐱求求了!!\n重金感谢！！！', 'https://img1.imgtp.com/2023/09/17/SMuer2ky.png', '王二0', '', '', NULL, 16, 0, 5, '[\"2\"]', 1, '2023-11-24 09:19:59', '127.0.0.1', '0', '0'),
(13, 0, '今天出去玩拍到了，真的很不错分享给大家！！', 'https://img1.imgtp.com/2023/09/17/5vELqxuF.jpg', '马腾', '', '', '', 14, 0, 1, '[\"4\"]', 1, '2023-09-17 07:03:02', '127.0.0.1', '0', '0'),
(14, 0, '真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！真的很漂亮哎！！！', 'https://img1.imgtp.com/2023/09/17/yLC3M1D6.jpg', '纸怪', '', '', '', 15, 1, 19, '[\"4\",\"5\"]', 1, '2023-09-17 07:04:19', '127.0.0.1', '0', '0'),
(15, 0, '哪有什么突然想起，其实是一直藏在心底。12.以往落空落单的每天，在今年被拉住了手，成为了街上一点都不孤单的人，还被小心翼翼的保护了起来。', '/storage/image/20230917\\6f9bd5d6e1f8e0da3acd3528416c78b1.jpg', '', '', '李婷婷', NULL, 13, 0, 6, '[\"3\",\"5\"]', 0, '2023-09-17 07:13:24', '127.0.0.1', '0', '0'),
(16, 0, '妈妈说，人最好不要错过两样东西：最后一班回家的车和一个深爱你的人。这一次，我不想再错过你了。', 'https://img1.imgtp.com/2023/09/17/WVpo8zFA.jpg', '王聪聪', '', '孙菲菲', NULL, 18, 1, 18, '[\"1\",\"3\",\"5\"]', 0, '2023-09-17 07:14:31', '127.0.0.1', '0', '0'),
(17, 0, '不是吧不是吧感谢这位同学', 'https://img1.imgtp.com/2023/09/17/ooOFlMbE.jpg', '1888', '', '2333', '', 18, 0, 4, '[\"4\",\"5\"]', 1, '2023-09-17 07:16:15', '127.0.0.1', '0', '0'),
(25, 0, '发v但是', 'http://test.fatda.cn/uploads/20210704538378743.jpg', '阿斯顿发射点324', '阿斯蒂芬十大ds32432', '', NULL, 18, 0, 1, '[\"1\",\"3\",\"6\"]', 1, '2023-11-24 07:26:00', '127.0.0.1', '0', '0'),
(29, 0, '546456', '/storage/image/20231127\\6a6847118b8756694c6efb2a1142a144.png', '456456', '456645', '6456', NULL, 17, 0, 0, '[\"1\",\"5\",\"8\"]', 0, '2023-11-27 09:46:25', '127.0.0.1', '0', '0'),
(30, 0, '321321', '/storage/image/20231201\\d1aa4fdcb163c871a994c40ed1f500c9.png', '321', '3213', '21312', '213123', 0, 0, 0, '[\"1\",\"5\"]', 1, '2023-11-30 16:15:40', '127.0.0.1', '0', '0'),
(31, 0, '321321', '/storage/image/20231201\\d1aa4fdcb163c871a994c40ed1f500c9.png', '321', '3213', '21312', '213123', 0, 0, 0, '[\"1\",\"5\"]', 1, '2023-11-30 16:17:50', '127.0.0.1', '0', '0'),
(32, 0, '321312321', '/storage/image/20231201\\61a9be5fd55b35db435afe2463349a6d.png', '321321', '321312', '321321', '312321', 0, 0, 0, '[\"1\",\"4\"]', 1, '2023-11-30 16:20:46', '127.0.0.1', '0', '0'),
(33, 0, '321312321', '/storage/image/20231201\\61a9be5fd55b35db435afe2463349a6d.png', '321321', '321312', '321321', '312321', 1, 0, 0, '[\"1\",\"4\"]', 1, '2023-11-30 16:21:01', '127.0.0.1', '0', '0'),
(34, 0, '1231231', '/storage/image/20231201\\da79e2fba9a1f6e86590ae53a362dd8e.png', '312312', '312312', '213312312', '312312', 0, 0, 0, '[\"1\",\"3\"]', 1, '2023-11-30 16:28:01', '127.0.0.1', '0', '0'),
(35, 0, '123123', '', '21312', '321312', '31232', '312312', 0, 0, 0, '[\"1\",\"5\"]', 1, '2023-11-30 16:30:18', '127.0.0.1', '0', '0'),
(36, 0, '12312312', '', '321321', '321321', '312312', '312321', 1, 0, 1, '[\"1\",\"3\"]', 1, '2023-11-30 16:31:32', '127.0.0.1', '0', '0'),
(37, 0, '3213', '', '321321', '23321', '321', '312312', 0, 0, 0, '', 1, '2023-11-30 16:31:58', '127.0.0.1', '0', '0'),
(38, 0, '321312312', '', '312321', '321321', '123123', '312312', 1, 0, 2, '', 1, '2023-12-19 16:15:05', '127.0.0.1', '0', '0'),
(39, 0, '2313213', '', '312312', '3213', '321312', '321312', 1, 1, 4, '', 1, '2023-11-30 16:37:46', '127.0.0.1', '0', '0');

-- --------------------------------------------------------

--
-- 表的结构 `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL COMMENT 'pid/aid:2',
  `aid` int(11) NOT NULL COMMENT '应用ID',
  `pid` varchar(256) NOT NULL COMMENT '条目ID',
  `content` varchar(256) NOT NULL COMMENT '内容',
  `name` varchar(256) NOT NULL COMMENT '我的名字',
  `ip` varchar(256) NOT NULL COMMENT '发布IP',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '发布时间',
  `status` int(11) NOT NULL COMMENT '封禁状态'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `comments`
--

INSERT INTO `comments` (`id`, `aid`, `pid`, `content`, `name`, `ip`, `time`, `status`) VALUES
(9, 1, '11', '3123213', '12312', '127.0.0.1', '2023-09-19 17:33:36', 0),
(10, 1, '11', '这是一条评论', '评论者名称', '127.0.0.1', '2023-11-10 08:22:11', 0),
(11, 1, '11', '这是一条评论', '评论者名称', '127.0.0.1', '2023-11-10 09:57:27', 0),
(12, 1, '11', '这是一条评论432423', '评论者名称', '127.0.0.1', '2023-11-10 10:41:22', 0),
(15, 1, '39', '这是一条评论', '评论者名称', '127.0.0.1', '2023-12-01 02:48:49', 0),
(16, 1, '16', '434', '3443', '127.0.0.1', '2023-12-01 07:39:23', 0),
(17, 1, '14', '32321321', '21321', '127.0.0.1', '2023-12-07 17:05:28', 0);

-- --------------------------------------------------------

--
-- 表的结构 `good`
--

CREATE TABLE `good` (
  `id` int(11) NOT NULL,
  `aid` int(11) NOT NULL COMMENT '应用ID',
  `pid` int(11) NOT NULL COMMENT '条目ID',
  `ip` varchar(32) NOT NULL COMMENT '发布IP',
  `time` datetime NOT NULL COMMENT '发布时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `good`
--

INSERT INTO `good` (`id`, `aid`, `pid`, `ip`, `time`) VALUES
(129, 1, 16, '127.0.0.1', '2023-11-30 23:07:29'),
(130, 1, 17, '127.0.0.1', '2023-11-30 23:07:31'),
(131, 1, 25, '127.0.0.1', '2023-11-30 23:07:33'),
(132, 1, 29, '127.0.0.1', '2023-11-30 23:07:34'),
(133, 1, 12, '127.0.0.1', '2023-11-30 23:07:35'),
(134, 1, 14, '127.0.0.1', '2023-11-30 23:07:36'),
(135, 1, 13, '127.0.0.1', '2023-12-08 00:18:57'),
(136, 1, 36, '127.0.0.1', '2023-12-08 00:21:02'),
(137, 1, 33, '127.0.0.1', '2023-12-08 00:21:04'),
(138, 1, 39, '127.0.0.1', '2023-12-11 08:35:25'),
(139, 1, 15, '127.0.0.1', '2023-12-13 22:58:12'),
(140, 1, 38, '127.0.0.1', '2023-12-13 22:58:19');

-- --------------------------------------------------------

--
-- 表的结构 `images`
--

CREATE TABLE `images` (
  `id` int(11) NOT NULL,
  `aid` int(11) NOT NULL COMMENT '应用ID',
  `pid` int(11) NOT NULL COMMENT '条目ID',
  `uid` int(11) NOT NULL,
  `url` varchar(256) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `images`
--

INSERT INTO `images` (`id`, `aid`, `pid`, `uid`, `url`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 6, 0, 'https://th.bing.com/th/id/R.d8c8a25dad20c6312e18a82ba026a148?rik=U9SIBedUJ%2fmWkw&riu=http%3a%2f%2fi2.hdslb.com%2fbfs%2farchive%2f6f4462d85ce8fd6ce67dd378dc15d302b5d88d18.png&ehk=d0ShZ76TEhgUrSdWz7RQ374YXx2B64rFXBN0yKLp6U0%3d&risl=&pid=ImgRaw&r=0', '2023-08-19 00:59:39', NULL, NULL),
(2, 1, 6, 0, 'https://th.bing.com/th/id/OIP.QUckL7512reTbaawTM00LwHaIt?pid=ImgDet&rs=1', '2023-08-19 00:59:39', NULL, NULL),
(7, 1, 10, 0, 'https://test.fatda.cn/uploads/20210808421334740.jpeg', '2023-08-28 17:43:29', NULL, NULL),
(8, 1, 10, 0, 'https://test.fatda.cn/uploads/20210704538378743.jpg', '2023-08-28 17:43:29', NULL, NULL),
(9, 1, 10, 0, 'https://test.fatda.cn/uploads/202106122043387554.jpg', '2023-08-28 17:43:29', NULL, NULL),
(10, 1, 10, 0, 'https://test.fatda.cn/uploads/202109071745186233.jpg', '2023-08-28 17:43:29', NULL, NULL),
(11, 1, 9, 0, 'http://test.fatda.cn/uploads/20210704538378743.jpg', '2023-08-28 17:56:17', NULL, NULL),
(12, 1, 9, 0, '/storage/image/20230108\\d4775810d03b4db4afa9503bdbff1cb8.jpg', '2023-08-28 17:56:17', NULL, NULL),
(13, 1, 9, 0, 'https://test.fatda.cn/uploads/20210704538378743.jpg', '2023-08-28 17:56:17', NULL, NULL),
(17, 1, 13, 0, 'https://img1.imgtp.com/2023/09/17/5vELqxuF.jpg', '2023-09-17 15:03:02', NULL, NULL),
(18, 1, 14, 0, 'https://img1.imgtp.com/2023/09/17/yLC3M1D6.jpg', '2023-09-17 15:04:19', NULL, NULL),
(19, 1, 15, 0, '/storage/image/20230917\\6f9bd5d6e1f8e0da3acd3528416c78b1.jpg', '2023-09-17 15:13:24', NULL, NULL),
(20, 1, 16, 0, 'https://img1.imgtp.com/2023/09/17/WVpo8zFA.jpg', '2023-09-17 15:14:31', NULL, NULL),
(21, 1, 17, 0, 'https://img1.imgtp.com/2023/09/17/ooOFlMbE.jpg', '2023-09-17 15:16:15', NULL, NULL),
(38, 1, 27, 0, 'https://test.fatda.cn/uploads/20210808421334740.jpeg', '2023-11-23 11:33:55', NULL, NULL),
(39, 1, 27, 0, 'https://test.fatda.cn/uploads/20210704538378743.jpg', '2023-11-23 11:33:55', NULL, NULL),
(40, 1, 27, 0, 'https://test.fatda.cn/uploads/202106122043387554.jpg', '2023-11-23 11:33:55', NULL, NULL),
(41, 1, 27, 0, 'https://test.fatda.cn/uploads/202109071745186233.jpg', '2023-11-23 11:33:55', NULL, NULL),
(110, 1, 25, 0, 'http://test.fatda.cn/uploads/20210704538378743.jpg', '2023-11-24 15:26:00', NULL, NULL),
(111, 1, 25, 0, '/storage/image/20230108\\d4775810d03b4db4afa9503bdbff1cb8.jpg', '2023-11-24 15:26:00', NULL, NULL),
(112, 1, 25, 0, 'https://test.fatda.cn/uploads/20210704538378743.jpg', '2023-11-24 15:26:00', NULL, NULL),
(113, 1, 12, 0, 'https://img1.imgtp.com/2023/09/17/SMuer2ky.png', '2023-11-24 17:19:59', NULL, NULL),
(114, 1, 29, 0, '/storage/image/20231127\\6a6847118b8756694c6efb2a1142a144.png', '2023-11-27 17:46:25', NULL, NULL),
(115, 1, 30, 0, '/storage/image/20231201\\d1aa4fdcb163c871a994c40ed1f500c9.png', '2023-12-01 00:15:40', NULL, NULL),
(116, 1, 31, 0, '/storage/image/20231201\\d1aa4fdcb163c871a994c40ed1f500c9.png', '2023-12-01 00:17:50', NULL, NULL),
(117, 1, 32, 0, '/storage/image/20231201\\61a9be5fd55b35db435afe2463349a6d.png', '2023-12-01 00:20:46', NULL, NULL),
(118, 1, 33, 0, '/storage/image/20231201\\61a9be5fd55b35db435afe2463349a6d.png', '2023-12-01 00:21:01', NULL, NULL),
(119, 1, 34, 0, '/storage/image/20231201\\da79e2fba9a1f6e86590ae53a362dd8e.png', '2023-12-01 00:28:01', NULL, NULL),
(120, 1, 2, 1, 'image/20231222\\046baeca67598784002c01ac9a5e73a8.webp', '2023-12-22 17:33:27', '2023-12-22 17:33:27', NULL);

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
  `name` varchar(8) DEFAULT '' COMMENT '标签名',
  `tip` varchar(64) DEFAULT '' COMMENT '提示',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '封禁状态',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '发布时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `tags`
--

INSERT INTO `tags` (`id`, `aid`, `name`, `tip`, `status`, `time`) VALUES
(1, 1, '说出你的悄悄话', '测试标签', 0, '2023-08-13 17:14:16'),
(2, 1, '寻物', '6576576576', 0, '2023-09-17 06:47:56'),
(3, 1, '表白', '', 0, '2023-09-17 06:49:10'),
(4, 1, '分享', '', 0, '2023-09-17 06:51:47'),
(5, 1, '我要上热门', '', 0, '2023-09-17 07:03:29'),
(6, 1, '31232', '213', 1, '2023-11-08 07:22:56'),
(7, 1, '风格的和广泛的', '官方的花费更多', 0, '2023-11-08 07:56:17'),
(8, 1, '2132121', '312321321321321', 0, '2023-11-22 12:12:07'),
(9, 1, '32432', '3242342432', 0, '2023-11-22 12:12:17'),
(11, 1, '32432', '34242342', 0, '2023-11-22 15:00:24'),
(12, 1, '韩国军方巨化股份', '巨化股份巨化股份', 0, '2023-11-22 15:00:30'),
(14, 1, '43', '45353453543543风格的恢复供电21321312v', 0, '2023-11-22 15:48:42'),
(15, 1, '54543', '53453453', 0, '2023-11-22 17:15:18');

-- --------------------------------------------------------

--
-- 表的结构 `tags_map`
--

CREATE TABLE `tags_map` (
  `id` int(11) NOT NULL COMMENT 'pid',
  `aid` int(11) NOT NULL COMMENT '应用ID',
  `pid` int(11) DEFAULT NULL COMMENT 'AID[PID]',
  `tid` int(11) DEFAULT NULL COMMENT 'TagID',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `tags_map`
--

INSERT INTO `tags_map` (`id`, `aid`, `pid`, `tid`, `time`) VALUES
(1, 0, 5, 1, '2023-08-13 17:14:40'),
(2, 0, 6, 1, '2023-08-18 16:59:39'),
(6, 0, 10, 1, '2023-08-28 09:43:29'),
(7, 0, 10, 2, '2023-08-28 09:43:29'),
(8, 0, 10, 3, '2023-08-28 09:43:29'),
(9, 0, 9, 1, '2023-08-28 09:56:17'),
(10, 0, 9, 2, '2023-08-28 09:56:17'),
(11, 0, 9, 3, '2023-08-28 09:56:17'),
(12, 0, 11, 1, '2023-09-17 06:57:03'),
(14, 0, 13, 4, '2023-09-17 07:03:02'),
(15, 0, 14, 4, '2023-09-17 07:04:19'),
(16, 0, 14, 5, '2023-09-17 07:04:19'),
(17, 0, 15, 3, '2023-09-17 07:13:24'),
(18, 0, 15, 5, '2023-09-17 07:13:24'),
(19, 0, 16, 1, '2023-09-17 07:14:31'),
(20, 0, 16, 3, '2023-09-17 07:14:31'),
(21, 0, 16, 5, '2023-09-17 07:14:31'),
(22, 0, 17, 4, '2023-09-17 07:16:15'),
(23, 0, 17, 5, '2023-09-17 07:16:15'),
(30, 1, 27, 1, '2023-11-23 03:33:55'),
(31, 1, 27, 2, '2023-11-23 03:33:55'),
(32, 1, 27, 3, '2023-11-23 03:33:55'),
(89, 1, 25, 1, '2023-11-24 07:26:00'),
(90, 1, 25, 3, '2023-11-24 07:26:00'),
(91, 1, 25, 6, '2023-11-24 07:26:00'),
(92, 1, 12, 2, '2023-11-24 09:19:59'),
(93, 1, 29, 1, '2023-11-27 09:46:25'),
(94, 1, 29, 5, '2023-11-27 09:46:25'),
(95, 1, 29, 8, '2023-11-27 09:46:25'),
(96, 1, 30, 1, '2023-11-30 16:15:40'),
(97, 1, 30, 5, '2023-11-30 16:15:40'),
(98, 1, 31, 1, '2023-11-30 16:17:50'),
(99, 1, 31, 5, '2023-11-30 16:17:50'),
(100, 1, 32, 1, '2023-11-30 16:20:46'),
(101, 1, 32, 4, '2023-11-30 16:20:46'),
(102, 1, 33, 1, '2023-11-30 16:21:01'),
(103, 1, 33, 4, '2023-11-30 16:21:01'),
(104, 1, 34, 1, '2023-11-30 16:28:01'),
(105, 1, 34, 3, '2023-11-30 16:28:01'),
(106, 1, 35, 1, '2023-11-30 16:30:18'),
(107, 1, 35, 5, '2023-11-30 16:30:18'),
(108, 1, 36, 1, '2023-11-30 16:31:32'),
(109, 1, 36, 3, '2023-11-30 16:31:32');

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `number` varchar(32) NOT NULL,
  `avatar` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(320) NOT NULL,
  `phone` varchar(32) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`id`, `number`, `avatar`, `email`, `phone`, `username`, `password`, `created_at`, `updated_at`, `deleted_at`, `status`) VALUES
(1, '0140952094', '', '2635435377@qq.com', '', 'USERE62F5', '$2y$10$XdbyN5QhHBLrejg3vC4m3eTjIliB7t/xJj1We7ZIrT4PUT4I3h3ze', '2023-12-06 20:09:26', '2023-12-21 11:41:25', NULL, 0),
(2, '3417137480', '', '26354353771@qq.com', '', 'USERA4B93', '$2y$10$pF0g4G4OVeBnL7a27WDOL.Z2.G.BZIC/Jbh9DiFzRfnyYc2FwB5LC', '2023-12-06 20:16:19', '2023-12-21 18:27:25', NULL, 1),
(3, '4651182860', '', '', '17638522991', 'USER8107A', '$2y$10$wke16H/pM9TlnEP3/VWY5.NNk3q0HiWbqjhGm9D5pI6VCj38LooI2', '2023-12-06 20:16:35', '2023-12-06 20:16:35', NULL, 0),
(4, '8698744880', '', '263543531@qq.com', '', 'USER09209', '$2y$10$MS8gmrd9eCdPgY5ne7wcBunWo92fGfhR.zq3LqsVGGl5NMpYyfkpy', '2023-12-06 20:36:09', '2023-12-06 20:36:09', NULL, 0),
(5, '8947581505', '', '26354331@qq.com', '', 'USER12F8F', '$2y$10$ua89gOp8uiPa1bEhz7.vKOiNGGUiDAeGmJyPdU9moogBPRLGHg062', '2023-12-06 20:36:28', '2023-12-06 20:36:28', NULL, 0),
(6, '1234345', '', '', '17638522990', 'USER601F6', '$2y$10$nHJQ2FPokKQQSEkV2jjesu1ugUZ8m7Sk65gFKbqlyM.5WhjH3mTye', '2023-12-06 20:36:40', '2023-12-21 01:01:05', NULL, 0),
(7, '0662296211', '', '783546756@qq.com', '', 'USER3F8A0', '$2y$10$Wh3vvy4Ta6UbVL/K.gZ2fOI5WqXLMPt/J0sZKuJk1iblVJ9WYMULy', '2023-12-15 00:57:00', '2023-12-15 00:57:00', NULL, 0);

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
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- 使用表AUTO_INCREMENT `cards`
--
ALTER TABLE `cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'cid/pid/aid:1', AUTO_INCREMENT=40;

--
-- 使用表AUTO_INCREMENT `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'pid/aid:2', AUTO_INCREMENT=18;

--
-- 使用表AUTO_INCREMENT `good`
--
ALTER TABLE `good`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- 使用表AUTO_INCREMENT `images`
--
ALTER TABLE `images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- 使用表AUTO_INCREMENT `system`
--
ALTER TABLE `system`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- 使用表AUTO_INCREMENT `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'tid/pid', AUTO_INCREMENT=16;

--
-- 使用表AUTO_INCREMENT `tags_map`
--
ALTER TABLE `tags_map`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'pid', AUTO_INCREMENT=110;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
