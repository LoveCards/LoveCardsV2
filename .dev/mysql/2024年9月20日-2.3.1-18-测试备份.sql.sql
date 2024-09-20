-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2024-09-20 00:48:22
-- 服务器版本： 5.7.44-log
-- PHP 版本： 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `chizg_cn`
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
(1, 'admin', 'd033e22ae348aeb5660fc2140aec35850c4da997', 0, '');

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
(1, 1, '急需帮忙，丢失了我的钱包！', 'https://free-img.400040.xyz/4/2024/09/20/66ec4e5dd8902.webp', '张伟', '13912345678', '李娜', '13887654321', 9, 2, 16, '[\"4\",\"5\"]', 1, '2024-09-19 16:38:59', '192.168.1.10', '1', '0'),
(2, 2, '寻物启事，请帮忙寻找我的手机！', 'https://free-img.400040.xyz/4/2024/09/20/66ec4e5d6b27d.webp', '王芳', '13923456789', '赵敏', '13998765432', 5, 3, 11, '[\"2\",\"3\",\"4\"]', 0, '2024-09-19 16:42:25', '192.168.1.11', '0', '0'),
(3, 3, '发布新书，欢迎大家购买！', 'https://free-img.400040.xyz/4/2024/09/20/66ec4e5e3584c.webp', '李四', '13934567890', '刘强', '13987654321', 12, 4, 20, '[\"4\",\"5\"]', 1, '2024-09-19 16:42:47', '192.168.1.12', '1', '0'),
(4, 4, '寻找合适的兼职工作，有意者请联系！', 'https://free-img.400040.xyz/4/2024/09/20/66ec4e5e3584c.webp', '赵云', '13945678901', '孙红', '13976543210', 7, 1, 12, '[\"5\",\"6\"]', 0, '2024-09-19 16:42:47', '192.168.1.13', '0', '0'),
(5, 5, '出售二手电脑，有兴趣的联系我！', 'https://free-img.400040.xyz/4/2024/09/20/66ec4e5e3584c.webp', '周杰', '13956789012', '吴丽', '13965432109', 15, 2, 25, '[\"6\",\"7\",\"8\"]', 1, '2024-09-19 16:42:47', '192.168.1.14', '1', '1'),
(6, 6, '求购二手家具，价格面议！', 'https://free-img.400040.xyz/4/2024/09/20/66ec4e5f26277.webp', '郑美', '13967890123', '陈强', '13954321098', 7, 4, 19, '[\"7\"]', 0, '2024-09-19 16:47:22', '192.168.1.15', '0', '0'),
(7, 7, '寻找有经验的编程伙伴一起学习！', 'https://free-img.400040.xyz/4/2024/09/20/66ec4e5fbc59a.webp', '王雷', '13978901234', '蒋磊', '13943210987', 9, 2, 22, '[\"1\",\"8\"]', 1, '2024-09-19 16:42:47', '192.168.1.16', '0', '1'),
(8, 8, '急需招聘平面设计师，欢迎推荐！', 'https://free-img.400040.xyz/4/2024/09/20/66ec4e5fbc59a.webp', '陈静', '13989012345', '李梅', '13932109876', 11, 1, 14, '[\"2\",\"3\"]', 1, '2024-09-19 16:42:47', '192.168.1.17', '0', '0'),
(9, 1, '出售全新手机，价格优惠！', 'https://free-img.400040.xyz/4/2024/09/20/66ec4e5fbc59a.webp', '刘洋', '13990123456', '吴军', '13921098765', 20, 5, 30, '[\"4\",\"5\"]', 0, '2024-09-19 16:42:47', '192.168.1.18', '1', '1'),
(10, 2, '寻找翻译人员，合作机会！', 'https://free-img.400040.xyz/4/2024/09/20/66ec4e5fbc59a.webp', '王平', '13901234567', '陈丹', '13910987654', 14, 4, 30, '[\"6\",\"7\"]', 1, '2024-09-19 16:46:37', '192.168.1.19', '1', '0'),
(11, 3, '出售几本经典书籍，有意者联系！', 'https://free-img.400040.xyz/4/2024/09/20/66ec4e5fbc59a.webp', '李莉', '13912345678', '赵磊', '13987654321', 8, 2, 16, '[\"7\",\"8\"]', 0, '2024-09-19 16:42:47', '192.168.1.20', '0', '1'),
(12, 4, '求助，急需借用一台投影仪！', 'https://free-img.400040.xyz/4/2024/09/20/66ec4e5fbc59a.webp', '张晨', '13923456789', '刘波', '13998765432', 5, 1, 12, '[\"1\"]', 1, '2024-09-19 16:17:57', '192.168.1.21', '0', '0'),
(13, 5, '分享我的旅行经历，欢迎交流！', 'https://free-img.400040.xyz/4/2024/09/20/66ec4e5fbc59a.webp', '王欣', '13934567890', '孙亮', '13987654321', 14, 3, 25, '[\"3\",\"4\"]', 1, '2024-09-19 16:42:47', '192.168.1.22', '0', '1'),
(14, 6, '寻找兼职教师，欢迎应聘！', 'https://free-img.400040.xyz/4/2024/09/20/66ec4ed2ece01.webp', '陈玲', '13945678901', '李娜', '13976543210', 11, 2, 18, '[\"5\",\"6\"]', 0, '2024-09-19 16:42:47', '192.168.1.23', '0', '0'),
(15, 7, '出售电动滑板车，有意者联系！', 'https://free-img.400040.xyz/4/2024/09/20/66ec4e5fbc59a.webp', '郑亮', '13956789012', '吴杰', '13965432109', 19, 4, 30, '[\"7\",\"8\"]', 1, '2024-09-19 16:42:47', '192.168.1.24', '0', '1'),
(16, 8, '寻找婚礼摄影师，价格面议！', 'https://free-img.400040.xyz/4/2024/09/20/66ec4e5e3584c.webp', '蒋文', '13967890123', '张媛', '13954321098', 23, 5, 36, '[\"1\",\"2\"]', 0, '2024-09-19 16:45:08', '192.168.1.25', '1', '0'),
(17, 1, '出售家电，价格优惠，欢迎联系！', 'https://free-img.400040.xyz/4/2024/09/20/66ec4ed2ece01.webp', '孙秀', '13978901234', '赵刚', '13943210987', 17, 6, 32, '[\"3\",\"4\"]', 1, '2024-09-19 16:45:04', '192.168.1.26', '0', '0'),
(18, 2, '寻找靠谱的网络工程师，有意者请联系！', 'https://free-img.400040.xyz/4/2024/09/20/66ec4ed2ece01.webp', '吴峰', '13989012345', '李强', '13932109876', 18, 2, 32, '[\"5\",\"6\"]', 0, '2024-09-19 16:42:47', '192.168.1.27', '0', '1'),
(19, 3, '求助，急需借用一个麦克风！', 'https://free-img.400040.xyz/4/2024/09/20/66ec4ed2ece01.webp', '陈伟', '13990123456', '刘畅', '13921098765', 12, 3, 24, '[\"7\",\"8\"]', 1, '2024-09-19 16:42:47', '192.168.1.28', '0', '0'),
(20, 4, '分享最新的科技动态，欢迎讨论！', 'https://free-img.400040.xyz/4/2024/09/20/66ec4ed2ece01.webp', '李佳', '13901234567', '王磊', '13910987654', 16, 4, 29, '[\"1\",\"2\",\"3\"]', 1, '2024-09-19 16:42:47', '192.168.1.29', '0', '1');

-- --------------------------------------------------------

--
-- 表的结构 `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL COMMENT 'pid/aid:2',
  `aid` int(11) NOT NULL COMMENT '应用ID',
  `pid` varchar(256) NOT NULL COMMENT '条目ID',
  `uid` int(11) NOT NULL,
  `content` varchar(256) NOT NULL COMMENT '内容',
  `name` varchar(256) NOT NULL COMMENT '我的名字',
  `ip` varchar(256) NOT NULL COMMENT '发布IP',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '发布时间',
  `status` int(11) NOT NULL COMMENT '封禁状态'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `comments`
--

INSERT INTO `comments` (`id`, `aid`, `pid`, `uid`, `content`, `name`, `ip`, `time`, `status`) VALUES
(1, 1, '10', 1, '你好同学，可以联系我哦！', '王蒙', '192.168.3.9', '2024-09-19 16:46:37', 0),
(2, 1, '6', 1, '哈哈哈哈笑死我了好抽象啊', '可爱捏', '192.168.3.9', '2024-09-19 16:47:22', 0);

-- --------------------------------------------------------

--
-- 表的结构 `good`
--

CREATE TABLE `good` (
  `id` int(11) NOT NULL,
  `aid` int(11) NOT NULL COMMENT '应用ID',
  `pid` int(11) NOT NULL COMMENT '条目ID',
  `uid` int(11) NOT NULL,
  `ip` varchar(32) NOT NULL COMMENT '发布IP',
  `time` datetime NOT NULL COMMENT '发布时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `good`
--

INSERT INTO `good` (`id`, `aid`, `pid`, `uid`, `ip`, `time`) VALUES
(1, 1, 16, 1, '192.168.3.9', '2024-09-20 00:21:14'),
(2, 1, 10, 1, '192.168.3.9', '2024-09-20 00:21:15'),
(3, 1, 1, 1, '192.168.3.9', '2024-09-20 00:21:16'),
(4, 1, 8, 1, '192.168.3.9', '2024-09-20 00:21:18'),
(5, 1, 6, 1, '192.168.3.9', '2024-09-20 00:21:19');

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
(2, 'siteName', 'LoveCardsV2.3.1'),
(3, 'siteICPId', ''),
(4, 'siteKeywords', ''),
(5, 'siteDes', ''),
(10, 'siteFooter', '                    <div class=\"mdui-col-xs-12 mdui-col-md-3 mdui-p-y-1\">\r\n                        <div class=\"mdui-typo-headline\">标题</div>\r\n                        <div class=\"mdui-typo-body-2-opacity\">\r\n                            法航客机的撒恢复健康了大厦看见返回的是\r\n                        </div>\r\n                    </div>\r\n                    <div class=\"mdui-col-xs-12 mdui-col-md-3 mdui-p-y-1\">\r\n                        <div class=\"mdui-typo-headline\">标题</div>\r\n                        <div class=\"mdui-typo-body-2-opacity\">\r\n                            法航客机的撒恢复健康了大厦看见返回的是\r\n                        </div>\r\n                    </div>\r\n                    <div class=\"mdui-col-xs-12 mdui-col-md-3 mdui-p-y-1\">\r\n                        <div class=\"mdui-typo-headline\">标题</div>\r\n                        <div class=\"mdui-typo-body-2-opacity\">\r\n                            法航客机的撒恢复健康了大厦看见返回的是\r\n                        </div>\r\n                    </div>\r\n                    <div class=\"mdui-col-xs-12 mdui-col-md-3 mdui-p-y-1\">\r\n                        <div class=\"mdui-typo-headline\">标题</div>\r\n                        <div class=\"mdui-typo-body-2-opacity\">\r\n                            法航客机的撒恢复健康了大厦看见返回的是\r\n                        </div>\r\n                    </div>'),
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
  `id` int(11) NOT NULL COMMENT 'tid/pid',
  `aid` int(11) NOT NULL COMMENT '应用ID',
  `name` varchar(8) DEFAULT '' COMMENT '标签名',
  `tip` varchar(64) DEFAULT '' COMMENT '提示',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '封禁状态',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '发布时间',
  `deleted_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `tags`
--

INSERT INTO `tags` (`id`, `aid`, `name`, `tip`, `status`, `time`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 1, '说出你的悄悄话', '测试标签', 0, '2024-09-19 15:23:09', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(2, 1, '寻物', '测试标签', 0, '2024-09-19 15:23:11', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(3, 1, '表白', '测试标签', 0, '2024-09-19 15:23:13', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(4, 1, '分享', '测试标签', 0, '2024-09-19 15:23:15', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(5, 1, '我要上热门', '测试标签', 0, '2024-09-19 15:23:17', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(6, 1, '交友', '测试标签', 1, '2024-09-19 15:23:35', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(7, 1, '找搭子', '测试标签', 0, '2024-09-19 15:23:43', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(8, 1, '吐槽', '测试标签', 0, '2024-09-19 15:23:46', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(9, 1, '灌水', '测试标签', 0, '2024-09-19 15:23:57', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(11, 1, '话题1', '测试标签', 0, '2024-09-19 15:24:24', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(12, 1, '话题2', '测试标签', 0, '2024-09-19 15:24:32', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- 表的结构 `tags_map`
--

CREATE TABLE `tags_map` (
  `id` int(11) NOT NULL COMMENT 'pid',
  `aid` int(11) NOT NULL COMMENT '应用ID',
  `pid` int(11) DEFAULT NULL COMMENT 'AID[PID]',
  `tid` int(11) DEFAULT NULL COMMENT 'TagID',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

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
(1, '0140952094', '/storage/image/20240916/15f23df7c4e643f4eb85cd1c6ed2167a.png', '2635435377@qq.com', '', 'USERA4893', '$2y$10$syNMoYk8TGblVq9SKwtTw.r9qttSagKTJq8ASMnTklRHHOyrw.oGq', '2023-12-06 20:09:26', '2024-09-16 18:56:19', NULL, 1),
(2, '3417137480', '/storage/image/20240202/e2b4ef022eaf3e1370fc6ec38cde6cc0.jpg', '26354353771@qq.com', '', 'USERA4B93', '$2y$10$pF0g4G4OVeBnL7a27WDOL.Z2.G.BZIC/Jbh9DiFzRfnyYc2FwB5LC', '2023-12-06 20:16:19', '2024-02-02 20:37:33', NULL, 0),
(3, '4651188601', '/storage/image/20231230/5c8e38eef698c4155dc31586028fed1e.jpg', '', '17638522991', 'USER8107A', '$2y$10$wke16H/pM9TlnEP3/VWY5.NNk3q0HiWbqjhGm9D5pI6VCj38LooI2', '2023-12-06 20:16:35', '2024-01-09 15:08:22', '2024-01-09 15:08:22', 0),
(4, '8698744880', '', '263543531@qq.com', '', 'USER09209', '$2y$10$MS8gmrd9eCdPgY5ne7wcBunWo92fGfhR.zq3LqsVGGl5NMpYyfkpy', '2023-12-06 20:36:09', '2023-12-28 17:25:59', '2023-12-28 17:25:59', 0),
(5, '8947581505', '/storage/image/20231225\\77b3e5c77158c3610abbca6d206839fa.jpg', '26354331@qq.com', '', 'USER12F8F', '$2y$10$ua89gOp8uiPa1bEhz7.vKOiNGGUiDAeGmJyPdU9moogBPRLGHg062', '2023-12-06 20:36:28', '2023-12-28 17:25:28', '2023-12-28 17:25:28', 0),
(6, '8947581506', '/storage/image/20231225\\a687cd931e49ee83182567f821f9ee13.webp', '', '17638522990', 'USER601F6', '$2y$10$nHJQ2FPokKQQSEkV2jjesu1ugUZ8m7Sk65gFKbqlyM.5WhjH3mTye', '2023-12-06 20:36:40', '2023-12-28 17:25:11', '2023-12-28 17:25:11', 0),
(7, '0662296211', '/storage/image/20231225\\ce9d7d90627f4709c37adc35e760ee05.webp', '783546756@qq.com', '', 'USER3F8A0', '$2y$10$Wh3vvy4Ta6UbVL/K.gZ2fOI5WqXLMPt/J0sZKuJk1iblVJ9WYMULy', '2023-12-15 00:57:00', '2023-12-28 11:54:47', '2023-12-28 11:54:47', 0),
(8, '5904910356', '', 'zs@zs.zs', '', 'USER309FB', '$2y$10$lnnSEfbbd/3GKfe141bqBetK6sQkotJ3vc4VTHhr6GkyStR9FFqru', '2024-01-30 17:40:09', '2024-01-30 17:40:09', NULL, 0);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `cards`
--
ALTER TABLE `cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'cid/pid/aid:1', AUTO_INCREMENT=21;

--
-- 使用表AUTO_INCREMENT `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'pid/aid:2', AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `good`
--
ALTER TABLE `good`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `images`
--
ALTER TABLE `images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `system`
--
ALTER TABLE `system`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- 使用表AUTO_INCREMENT `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'tid/pid', AUTO_INCREMENT=13;

--
-- 使用表AUTO_INCREMENT `tags_map`
--
ALTER TABLE `tags_map`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'pid';

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
