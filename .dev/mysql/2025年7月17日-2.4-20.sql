-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2025-07-17 14:11:42
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
-- 数据库： `test4_com`
--

-- --------------------------------------------------------

--
-- 表的结构 `cards`
--

CREATE TABLE `cards` (
  `id` int(11) NOT NULL,
  `is_top` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `data` json DEFAULT NULL,
  `cover` varchar(2083) DEFAULT NULL,
  `content` text,
  `tags` json DEFAULT NULL,
  `good` int(11) NOT NULL DEFAULT '0',
  `views` int(11) NOT NULL DEFAULT '0',
  `comments` int(11) NOT NULL DEFAULT '0',
  `post_ip` varchar(39) DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `cards`
--

INSERT INTO `cards` (`id`, `is_top`, `status`, `user_id`, `data`, `cover`, `content`, `tags`, `good`, `views`, `comments`, `post_ip`, `created_at`, `updated_at`, `deleted_at`) VALUES
(28, 0, 0, 1, NULL, NULL, '发布内容', '[1, 2, 3]', 0, 0, 0, '192.168.3.4', '2025-07-15 15:05:37', '2025-07-15 15:05:37', NULL),
(29, 0, 0, 1, '{\"model\": \"\", \"taName\": \"\", \"woName\": \"\", \"woContact\": \"\"}', 'http://192.168.3.142:7001/storage/image/20250716/92db5a22d28c0d86beae982f7eae8465.webp', '法撒旦撒打算', '[]', 0, 0, 0, '192.168.3.4', '2025-07-16 05:07:38', '2025-07-16 05:07:38', NULL),
(30, 0, 0, 1, '{\"model\": \"\", \"taName\": \"\", \"woName\": \"\", \"woContact\": \"\"}', NULL, '范德萨范德萨', '[]', 0, 0, 0, '192.168.3.4', '2025-07-16 05:08:30', '2025-07-16 05:08:30', NULL),
(31, 0, 0, 1, '{\"model\": \"1\", \"taName\": \"\", \"woName\": \"\", \"taContact\": \"\", \"woContact\": \"\"}', NULL, '645645645645654', '[]', 0, 0, 0, '192.168.3.4', '2025-07-16 05:09:47', '2025-07-16 05:09:47', NULL),
(32, 0, 0, 1, '{\"model\": \"0\", \"taName\": \"\", \"woName\": \"\", \"woContact\": \"\"}', NULL, '高浮雕高浮雕', '[]', 0, 1, 0, '192.168.3.4', '2025-07-16 05:10:16', '2025-07-16 05:10:16', NULL),
(33, 0, 0, 1, '{\"model\": \"0\", \"taName\": \"\", \"woName\": \"\", \"woContact\": \"\"}', 'http://192.168.3.142:7001/storage/image/20250716/7b0cfc8551dd7d152901c63c41fe71d1.webp', '吧v女兵', '[\"5\", \"9\", \"16\"]', 0, 2, 0, '192.168.3.4', '2025-07-16 05:10:35', '2025-07-16 05:10:35', NULL),
(34, 0, 0, 1, NULL, NULL, '这是一条评论', NULL, 0, 0, 0, '192.168.3.4', '2025-07-17 04:34:23', '2025-07-17 04:34:23', NULL),
(35, 0, 0, 1, NULL, NULL, '这是一条评论', NULL, 0, 0, 0, '192.168.3.4', '2025-07-17 04:34:37', '2025-07-17 04:34:37', NULL),
(36, 0, 0, 1, NULL, NULL, '这是一条评论', NULL, 0, 0, 0, '192.168.3.4', '2025-07-17 04:35:21', '2025-07-17 04:35:21', NULL),
(37, 0, 0, 1, NULL, NULL, '这是一条评论', NULL, 0, 0, 0, '192.168.3.4', '2025-07-17 04:35:33', '2025-07-17 04:35:33', NULL),
(38, 0, 0, 1, NULL, NULL, '这是一条评论', NULL, 0, 0, 0, '192.168.3.4', '2025-07-17 04:38:04', '2025-07-17 04:38:04', NULL),
(39, 0, 0, 1, NULL, NULL, '这是一条评论', NULL, 0, 3, 0, '192.168.3.4', '2025-07-17 04:44:41', '2025-07-17 04:44:41', NULL),
(40, 0, 0, 1, '{\"model\": \"0\", \"taName\": \"432432\", \"woName\": \"3423423\", \"woContact\": \"32423\"}', NULL, '423423432', '[]', 0, 1, 0, '192.168.3.4', '2025-07-17 05:21:38', '2025-07-17 05:21:38', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `aid` int(11) NOT NULL DEFAULT '0',
  `pid` int(11) NOT NULL DEFAULT '0',
  `parent_id` int(11) DEFAULT '0',
  `is_top` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `data` json DEFAULT NULL,
  `content` text,
  `goods` int(11) NOT NULL DEFAULT '0',
  `post_ip` varchar(39) DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

-- --------------------------------------------------------

--
-- 表的结构 `images`
--

CREATE TABLE `images` (
  `id` int(11) NOT NULL,
  `aid` int(11) NOT NULL COMMENT '应用ID',
  `pid` int(11) NOT NULL COMMENT '条目ID',
  `user_id` int(11) NOT NULL,
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
(2, 'siteName', 'LoveCardsV2.3'),
(3, 'siteICPId', ''),
(4, 'siteKeywords', ''),
(5, 'siteDes', ''),
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
  `id` int(11) NOT NULL,
  `aid` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) DEFAULT '',
  `status` int(11) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `tags_map`
--

CREATE TABLE `tags_map` (
  `id` int(11) NOT NULL,
  `aid` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
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
  `phone` varchar(20) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` int(11) NOT NULL,
  `roles_id` json DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`id`, `number`, `avatar`, `email`, `phone`, `username`, `password`, `status`, `roles_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '0140952094', 'http://192.168.3.142:7001/storage/image/20250715/1f4f4608e9e48145a2d854af8628488b.webp', 'admin@qq.com', '', '测试用户111', '$2y$10$uBowOFgOBNTx1NT1uYJTleEo1r8d91R9iwxRCqncPJUShfsJoMvr6', 0, '[0, 1, 2]', '2023-12-06 20:09:26', '2025-07-15 11:12:15', NULL);

--
-- 转储表的索引
--

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
-- 使用表AUTO_INCREMENT `cards`
--
ALTER TABLE `cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- 使用表AUTO_INCREMENT `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- 使用表AUTO_INCREMENT `good`
--
ALTER TABLE `good`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=182;

--
-- 使用表AUTO_INCREMENT `images`
--
ALTER TABLE `images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=324;

--
-- 使用表AUTO_INCREMENT `system`
--
ALTER TABLE `system`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- 使用表AUTO_INCREMENT `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- 使用表AUTO_INCREMENT `tags_map`
--
ALTER TABLE `tags_map`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
