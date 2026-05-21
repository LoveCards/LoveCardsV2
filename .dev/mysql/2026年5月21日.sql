-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- 主机�?localhost
-- 生成日期�?2026-05-21 22:15:30
-- 服务器版本： 5.7.26
-- PHP 版本�?7.3.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `testtest`
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
  `goods` int(11) NOT NULL DEFAULT '0',
  `views` int(11) NOT NULL DEFAULT '0',
  `comments` int(11) NOT NULL DEFAULT '0',
  `post_ip` varchar(39) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数�?`cards`
--

INSERT INTO `cards` (`id`, `is_top`, `status`, `user_id`, `data`, `cover`, `content`, `tags`, `goods`, `views`, `comments`, `post_ip`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 0, 0, 1, '{\"model\": \"0\", \"taName\": \"\", \"woName\": \"\", \"woContact\": \"\"}', NULL, '撒旦�?, '[]', 1, 1, 0, '127.0.0.1', '2026-04-18 16:59:59', '2026-04-18 16:59:59', NULL);

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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 表的结构 `configs`
--

CREATE TABLE `configs` (
  `id` int(11) NOT NULL,
  `group` varchar(50) NOT NULL COMMENT '分组',
  `key` varchar(100) NOT NULL COMMENT '配置�?,
  `value` text COMMENT '配置�?,
  `type` varchar(20) DEFAULT 'string' COMMENT '类型：string/bool/int/json',
  `description` varchar(255) DEFAULT NULL COMMENT '配置说明',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数�?`configs`
--

INSERT INTO `configs` (`id`, `group`, `key`, `value`, `type`, `description`, `created_at`, `updated_at`) VALUES
(1, 'core', 'url', 'lovecards.cn', 'string', '站点域名', '2026-05-14 18:08:03', '2026-05-21 16:11:43'),
(2, 'core', 'name', 'LoveCardsV2.41', 'string', '站点名称', '2026-05-14 18:08:03', '2026-05-21 16:11:43'),
(3, 'core', 'icp_id', '', 'string', 'ICP备案�?, '2026-05-14 18:08:03', '2026-05-21 16:11:43'),
(4, 'core', 'keywords', '', 'string', '关键�?, '2026-05-14 18:08:03', '2026-05-21 16:11:43'),
(5, 'core', 'description', '', 'string', '站点描述', '2026-05-14 18:08:03', '2026-05-21 16:11:43'),
(6, 'core', 'footer', '', 'string', '页脚信息', '2026-05-14 18:08:03', '2026-05-14 18:08:03'),
(7, 'core', 'LCEAPI', '', 'string', 'LCEAPI', '2026-05-14 18:08:03', '2026-05-14 18:08:03'),
(8, 'core', 'copyright', '', 'string', '版权信息', '2026-05-14 18:08:03', '2026-05-21 16:11:43'),
(9, 'core', 'title', 'LoveCards', 'string', '站点标题', '2026-05-14 18:08:03', '2026-05-21 16:11:43'),
(10, 'core', 'smtpSecure', '', 'string', 'smtpSecure', '2026-05-14 18:08:03', '2026-05-14 18:08:03'),
(11, 'core', 'smtpName', '', 'string', 'smtpName', '2026-05-14 18:08:03', '2026-05-14 18:08:03'),
(16, 'core', 'theme_directory', 'index', 'string', '主题目录', '2026-05-14 18:08:52', '2026-05-14 18:08:52'),
(17, 'core', 'visitor_mode', '1', 'bool', '访客模式', '2026-05-14 18:08:52', '2026-05-14 22:28:45'),
(18, 'upload', 'user_image_size', '2', 'int', '用户图片大小(MB)', '2026-05-14 18:08:52', '2026-05-14 22:28:45'),
(19, 'upload', 'user_image_ext', 'jpg,png,gif,webp,jpeg', 'string', '允许的图片扩展名', '2026-05-14 18:08:52', '2026-05-14 22:28:45'),
(20, 'cards', 'approve', '', 'bool', '卡片审核开�?, '2026-05-14 18:08:52', '2026-05-14 22:28:45'),
(21, 'cards', 'picture_limit', '15', 'int', '卡片图片限制', '2026-05-14 18:08:52', '2026-05-14 22:28:45'),
(22, 'cards', 'tag_limit', '3', 'int', '卡片标签限制', '2026-05-14 18:08:52', '2026-05-14 22:28:45'),
(23, 'cards', 'image_size', '3', 'int', '卡片图片大小(MB)', '2026-05-14 18:08:52', '2026-05-14 18:08:52'),
(24, 'cards', 'comments_status', '1', 'bool', '卡片评论开�?, '2026-05-14 18:08:52', '2026-05-14 18:08:52'),
(25, 'comments', 'approve', '', 'bool', '评论审核开�?, '2026-05-14 18:08:52', '2026-05-14 22:28:45'),
(26, 'comments', 'picture_limit', '9', 'int', '评论图片限制', '2026-05-14 18:08:52', '2026-05-14 18:08:52'),
(27, 'user', 'captcha', '', 'bool', '验证码开�?, '2026-05-14 18:08:52', '2026-05-14 22:28:45'),
(28, 'geetest', 'status', '', 'bool', '极验开�?, '2026-05-14 18:08:52', '2026-05-14 22:28:45'),
(29, 'geetest', 'id', '', 'string', '极验ID', '2026-05-14 18:08:52', '2026-05-14 22:28:45'),
(30, 'geetest', 'key', '', 'string', '极验Key', '2026-05-14 18:08:52', '2026-05-14 22:28:45'),
(31, 'storage', 'default', 'local', 'string', '默认存储渠道', '2026-05-14 18:08:52', '2026-05-21 16:11:32'),
(32, 'storage', 'rate_limit_max', '10', 'int', '速率限制-最大请求数', '2026-05-14 18:08:52', '2026-05-21 16:11:32'),
(33, 'storage', 'rate_limit_window', '60', 'int', '速率限制-时间窗口(�?', '2026-05-14 18:08:52', '2026-05-21 16:11:32'),
(34, 'storage', 'direct_upload_expire', '3600', 'int', '直传凭证有效�?�?', '2026-05-14 18:08:52', '2026-05-21 16:11:32'),
(35, 'storage_local', 'root', 'public/storage', 'string', '本地存储根目�?, '2026-05-14 18:08:52', '2026-05-21 16:10:46'),
(36, 'storage_local', 'url_prefix', '/storage', 'string', 'URL前缀', '2026-05-14 18:08:52', '2026-05-21 16:10:46'),
(37, 'storage_local', 'allow_mime_types', 'image/jpeg,image/png,image/gif,image/webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'string', '允许的MIME类型', '2026-05-14 18:08:52', '2026-05-21 16:10:46'),
(38, 'storage_local', 'max_file_size', '10485760', 'int', '最大文件大�?字节)', '2026-05-14 18:08:52', '2026-05-21 16:10:46'),
(39, 'storage_oss', 'access_key', 'YOUR_ALIBABA_ACCESS_KEY_ID', 'string', 'AccessKey', '2026-05-14 18:08:52', '2026-05-17 18:41:01'),
(40, 'storage_oss', 'secret_key', 'YOUR_ALIBABA_SECRET_KEY', 'string', 'SecretKey', '2026-05-14 18:08:52', '2026-05-17 18:41:01'),
(41, 'storage_oss', 'bucket', 'test-j8d278', 'string', 'Bucket', '2026-05-14 18:08:52', '2026-05-17 18:41:01'),
(42, 'storage_oss', 'endpoint', 'oss-cn-beijing.aliyuncs.com', 'string', 'Endpoint', '2026-05-14 18:08:52', '2026-05-17 18:41:01'),
(43, 'storage_oss', 'url_prefix', 'https://test-j8d278.oss-cn-beijing.aliyuncs.com', 'string', 'URL前缀', '2026-05-14 18:08:52', '2026-05-17 18:41:01'),
(44, 'storage_oss', 'allow_mime_types', 'image/jpeg,image/png,image/gif,image/webp', 'string', '允许的MIME类型', '2026-05-14 18:08:52', '2026-05-17 18:41:01'),
(45, 'storage_oss', 'max_file_size', '52428800', 'int', '最大文件大�?字节)', '2026-05-14 18:08:52', '2026-05-17 18:41:01'),
(46, 'storage_cos', 'secret_id', 'YOUR_TENCENT_SECRET_ID', 'string', 'SecretId', '2026-05-14 18:08:52', '2026-05-14 18:29:29'),
(47, 'storage_cos', 'secret_key', 'YOUR_TENCENT_SECRET_KEY', 'string', 'SecretKey', '2026-05-14 18:08:52', '2026-05-14 18:29:29'),
(48, 'storage_cos', 'bucket', 'test-1253544066', 'string', 'Bucket', '2026-05-14 18:08:52', '2026-05-14 18:29:29'),
(49, 'storage_cos', 'region', 'ap-guangzhou', 'string', 'Region', '2026-05-14 18:08:52', '2026-05-14 18:08:52'),
(50, 'storage_cos', 'cdn_url', 'https://test-1253544066.cos.ap-guangzhou.myqcloud.com', 'string', 'CDN域名', '2026-05-14 18:08:52', '2026-05-14 18:29:29'),
(51, 'storage_cos', 'allow_mime_types', 'image/jpeg,image/png,image/gif,image/webp', 'string', '允许的MIME类型', '2026-05-14 18:08:52', '2026-05-14 18:08:52'),
(52, 'storage_cos', 'max_file_size', '52428800', 'int', '最大文件大�?字节)', '2026-05-14 18:08:52', '2026-05-14 18:08:52'),
(53, 'storage_qiniu', 'access_key', '', 'string', 'AccessKey', '2026-05-14 18:08:52', '2026-05-14 18:08:52'),
(54, 'storage_qiniu', 'secret_key', '', 'string', 'SecretKey', '2026-05-14 18:08:52', '2026-05-14 18:08:52'),
(55, 'storage_qiniu', 'bucket', '', 'string', 'Bucket', '2026-05-14 18:08:52', '2026-05-14 18:08:52'),
(56, 'storage_qiniu', 'domain', '', 'string', '域名', '2026-05-14 18:08:52', '2026-05-14 18:08:52'),
(57, 'storage_qiniu', 'allow_mime_types', 'image/jpeg,image/png,image/gif,image/webp', 'string', '允许的MIME类型', '2026-05-14 18:08:52', '2026-05-14 18:08:52'),
(58, 'storage_qiniu', 'max_file_size', '52428800', 'int', '最大文件大�?字节)', '2026-05-14 18:08:52', '2026-05-14 18:08:52'),
(62, 'core', 'test_key', '', 'string', '', '2026-05-15 02:53:09', '2026-05-15 02:54:38'),
(63, 'mail', 'driver', 'smtp', 'string', '邮件驱动', '2026-05-15 03:46:25', '2026-05-15 03:46:25'),
(64, 'mail', 'host', 'smtp.test.com', 'string', 'SMTP主机', '2026-05-15 03:46:25', '2026-05-15 03:52:00'),
(65, 'mail', 'port', '587', 'int', 'SMTP端口', '2026-05-15 03:46:25', '2026-05-15 03:52:00'),
(66, 'mail', 'addr', '', 'string', '发件人地址', '2026-05-15 03:46:25', '2026-05-15 03:46:25'),
(67, 'mail', 'pass', '', 'string', '密码', '2026-05-15 03:46:25', '2026-05-15 03:46:25'),
(68, 'mail', 'name', 'FatDa邮递员111', 'string', '发件人昵�?, '2026-05-15 03:46:25', '2026-05-15 03:59:14'),
(69, 'mail', 'security', 'tls', 'string', '加密方式', '2026-05-15 03:46:25', '2026-05-15 03:46:25'),
(70, 'storage_local', 'path_template', '{date}/{uuid}.{ext}', 'string', '', '2026-05-17 22:38:09', '2026-05-21 16:10:46');

-- --------------------------------------------------------

--
-- 表的结构 `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `channel_slug` varchar(50) NOT NULL DEFAULT 'local',
  `user_id` int(11) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT '0',
  `scene` varchar(32) DEFAULT 'direct',
  `ref_type` varchar(32) DEFAULT NULL,
  `ref_id` int(11) DEFAULT NULL,
  `original_name` varchar(255) DEFAULT NULL COMMENT '原始文件�?,
  `file_path` varchar(500) NOT NULL COMMENT '存储路径',
  `file_url` varchar(1000) NOT NULL COMMENT '访问URL',
  `file_size` int(11) NOT NULL COMMENT '文件大小(字节)',
  `file_ext` varchar(20) NOT NULL COMMENT '文件扩展�?,
  `mime_type` varchar(100) DEFAULT NULL COMMENT 'MIME类型',
  `driver_path` varchar(500) DEFAULT NULL COMMENT '驱动特定标识',
  `metadata` json DEFAULT NULL COMMENT '额外元数�?,
  `status` tinyint(1) DEFAULT '0',
  `upload_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '上传状态：0=上传�?1=已完�?2=失败',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `expire_at` datetime DEFAULT NULL COMMENT '凭证过期时间',
  `hash` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文件记录�?;

--
-- 转存表中的数�?`files`
--

INSERT INTO `files` (`id`, `channel_slug`, `user_id`, `is_public`, `scene`, `ref_type`, `ref_id`, `original_name`, `file_path`, `file_url`, `file_size`, `file_ext`, `mime_type`, `driver_path`, `metadata`, `status`, `upload_status`, `created_at`, `updated_at`, `deleted_at`, `expire_at`, `hash`) VALUES
(1, 'local', NULL, 1, 'direct', NULL, NULL, '屏幕截图 2025-08-25 002000.png', 'image/images/20260430/69f25c9ae474b.png', 'http://127.0.0.1:8001/storage/image/images/20260430/69f25c9ae474b.png', 393, 'png', 'image/png', 'image/images/20260430/69f25c9ae474b.png', NULL, 0, 1, '2026-04-30 03:31:38', '2026-05-14 00:15:58', NULL, NULL, '67bda4e5e3b0d3f9'),
(2, 'local', NULL, 1, 'direct', NULL, NULL, 'avatar.png', 'image/images/20260509/69fe1f7df1362.png', 'http://127.0.0.1:8001/storage/image/images/20260509/69fe1f7df1362.png', 3999, 'png', 'image/png', 'image/images/20260509/69fe1f7df1362.png', NULL, 0, 1, '2026-05-09 01:38:06', '2026-05-14 00:13:59', NULL, NULL, 'faea20f8c237e7f7'),
(3, 'local', NULL, 0, 'direct', NULL, NULL, 'avatar.png', 'image/images/20260509/69fe20dab46e6.png', 'http://127.0.0.1:8001/storage/image/images/20260509/69fe20dab46e6.png', 3999, 'png', 'image/png', 'image/images/20260509/69fe20dab46e6.png', NULL, 0, 2, '2026-05-09 01:43:54', '2026-05-14 00:14:00', NULL, NULL, '2bb56abb52561fdf'),
(5, 'cos', NULL, 0, 'direct', NULL, NULL, 'avatar.png', 'images/20260509/69fe25489263c', 'https://test-1253544066.cos.ap-guangzhou.myqcloud.com/images/20260509/69fe25489263c', 3999, 'png', 'image/png', 'images/20260509/69fe25489263c', NULL, 0, 1, '2026-05-09 02:02:49', '2026-05-14 00:12:14', NULL, NULL, '664182d7455222ba'),
(9, 'cos', 7, 1, 'direct', NULL, NULL, '屏幕截图 2025-08-25 002000.png', 'images/20260513/6a037879a72be', 'https://test-1253544066.cos.ap-guangzhou.myqcloud.com/images/20260513/6a037879a72be', 393, 'png', 'image/png', 'images/20260513/6a037879a72be', NULL, 0, 2, '2026-05-13 02:59:06', '2026-05-14 04:13:43', '2026-05-14 04:13:43', NULL, 'e33ab4f9ceb9d687'),
(10, 'cos', 1, 0, 'direct', NULL, NULL, 'avatar.png', 'images/20260513/6a037abd09094', 'https://test-1253544066.cos.ap-guangzhou.myqcloud.com/images/20260513/6a037abd09094', 3999, 'png', 'image/png', 'images/20260513/6a037abd09094', NULL, 0, 1, '2026-05-13 03:08:45', '2026-05-14 04:13:42', NULL, NULL, '99d80900060cf5c0'),
(12, 'cos', 1, 0, 'direct', NULL, NULL, 'avatar.png', 'images/20260514/6a05a452970e1', 'https://test-1253544066.cos.ap-guangzhou.myqcloud.com/images/20260514/6a05a452970e1', 3999, 'png', 'image/png', 'images/20260514/6a05a452970e1', NULL, 0, 0, '2026-05-14 18:30:44', '2026-05-14 18:30:44', NULL, NULL, 'db3a7d1f29e46e78'),
(14, 'cos', 1, 0, 'direct', NULL, NULL, 'test.jpg', 'images/test', '', 1024, 'jpg', 'image/jpeg', '', NULL, 0, 0, '2026-05-14 18:57:00', '2026-05-14 18:57:00', '2026-05-14 18:57:00', '2026-05-14 19:57:00', 'c0875b83e9582eec'),
(15, 'cos', 1, 0, 'direct', NULL, NULL, 'avatar.png', 'images/20260514/6a05ac2818525', 'https://test-1253544066.cos.ap-guangzhou.myqcloud.com/images/20260514/6a05ac2818525', 3999, 'png', 'image/png', 'images/20260514/6a05ac2818525', NULL, 0, 0, '2026-05-14 19:04:10', '2026-05-14 19:04:10', NULL, NULL, '055e64787897c9ae'),
(16, 'cos', 1, 0, 'direct', NULL, NULL, 'avatar.png', 'images/20260514/6a05ac66315af', 'https://test-1253544066.cos.ap-guangzhou.myqcloud.com/images/20260514/6a05ac66315af', 3999, 'png', 'image/png', 'images/20260514/6a05ac66315af', NULL, 0, 0, '2026-05-14 19:05:12', '2026-05-14 19:05:12', NULL, NULL, 'e350c36a6172a99e'),
(17, 'cos', 1, 0, 'direct', NULL, NULL, '屏幕截图 2025-08-25 002000.png', 'images/20260517/6a0994c0e7d60', 'https://test-1253544066.cos.ap-guangzhou.myqcloud.com/images/20260517/6a0994c0e7d60', 393, 'png', 'image/png', 'images/20260517/6a0994c0e7d60', NULL, 0, 0, '2026-05-17 18:13:22', '2026-05-17 18:13:22', NULL, NULL, '8ad2ce9aaba3a74c'),
(22, 'local', 1, 0, 'direct', NULL, NULL, '屏幕截图 2025-08-25 002000.png', 'storage/20260517/200b4b88-3f86-44e5-a7a9-a730b1b9135c.png', 'http://127.0.0.1:8001/storage/storage/20260517/200b4b88-3f86-44e5-a7a9-a730b1b9135c.png', 393, 'png', 'image/png', 'storage/20260517/200b4b88-3f86-44e5-a7a9-a730b1b9135c.png', NULL, 0, 1, '2026-05-17 22:37:52', '2026-05-17 22:37:52', NULL, NULL, '42a2ca7b5fa384e1'),
(23, 'local', 1, 0, 'direct', NULL, NULL, '屏幕截图 2025-08-25 002000.png', '20260517/99f99642-999c-4591-871a-f6b0164e2adc.png', 'http://127.0.0.1:8001/storage/20260517/99f99642-999c-4591-871a-f6b0164e2adc.png', 393, 'png', 'image/png', '20260517/99f99642-999c-4591-871a-f6b0164e2adc.png', NULL, 0, 1, '2026-05-17 22:38:12', '2026-05-17 22:38:12', NULL, NULL, 'd6d66c84fefecc6e'),
(24, 'local', 1, 0, 'direct', NULL, NULL, '屏幕截图 2025-08-25 002000.png', '20260517/cca38ba8-ca54-428f-b32d-a2e709b32e15.png', 'http://127.0.0.1:8001/storage/20260517/cca38ba8-ca54-428f-b32d-a2e709b32e15.png', 393, 'png', 'image/png', '20260517/cca38ba8-ca54-428f-b32d-a2e709b32e15.png', NULL, 0, 1, '2026-05-17 22:38:21', '2026-05-17 22:38:21', NULL, NULL, '283d2c266dcbf83d'),
(25, 'local', 1, 0, 'direct', NULL, NULL, '屏幕截图 2025-08-25 002000.png', '20260517/618b2bc5-524e-4115-9d3f-cba624a90140.png', 'http://127.0.0.1:8001/storage/20260517/618b2bc5-524e-4115-9d3f-cba624a90140.png', 393, 'png', 'image/png', '20260517/618b2bc5-524e-4115-9d3f-cba624a90140.png', NULL, 0, 1, '2026-05-17 22:38:22', '2026-05-17 22:38:22', NULL, NULL, 'c45262dd7e2b0c4c'),
(26, 'local', 1, 0, 'direct', NULL, NULL, '屏幕截图 2025-08-25 002000.png', '20260517/04ce1591-1c3e-409e-be1c-bbce2771106a.png', 'http://127.0.0.1:8001/storage/20260517/04ce1591-1c3e-409e-be1c-bbce2771106a.png', 393, 'png', 'image/png', '20260517/04ce1591-1c3e-409e-be1c-bbce2771106a.png', NULL, 0, 1, '2026-05-17 22:38:22', '2026-05-17 22:38:22', NULL, NULL, '96ca73e38e45e3d9'),
(27, 'local', 1, 0, 'direct', NULL, NULL, '屏幕截图 2025-08-25 002000.png', '20260517/169f422e-4db1-4b37-8c24-3838b399db9f.png', 'http://127.0.0.1:8001/storage/20260517/169f422e-4db1-4b37-8c24-3838b399db9f.png', 393, 'png', 'image/png', '20260517/169f422e-4db1-4b37-8c24-3838b399db9f.png', NULL, 0, 1, '2026-05-17 22:38:23', '2026-05-17 22:38:23', NULL, NULL, 'f68b0845553e8029'),
(32, 'local', 1, 0, 'direct', NULL, NULL, '屏幕截图 2025-08-25 002000.png', '20260518/29339d72-eb5d-44dc-958a-ba4cdc2395a7.png', 'http://127.0.0.1:8001/storage/20260518/29339d72-eb5d-44dc-958a-ba4cdc2395a7.png', 393, 'png', 'image/png', '20260518/29339d72-eb5d-44dc-958a-ba4cdc2395a7.png', NULL, 0, 1, '2026-05-18 02:27:13', '2026-05-18 02:27:13', NULL, NULL, 'd35f0d8fedf69de2'),
(33, 'local', 1, 0, 'direct', NULL, NULL, '屏幕截图 2025-08-25 002000.png', '20260519/7bcc88f1-2db9-44da-8dd4-56eb75e921e0.png', 'http://127.0.0.1:8001/storage/20260519/7bcc88f1-2db9-44da-8dd4-56eb75e921e0.png', 393, 'png', 'image/png', '20260519/7bcc88f1-2db9-44da-8dd4-56eb75e921e0.png', NULL, 0, 1, '2026-05-19 05:22:06', '2026-05-19 05:22:06', NULL, NULL, 'a61e10b862e764be');

-- --------------------------------------------------------

--
-- 表的结构 `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `aid` int(11) NOT NULL COMMENT '应用ID',
  `pid` int(11) NOT NULL COMMENT '条目ID',
  `uid` int(11) NOT NULL,
  `ip` varchar(32) NOT NULL COMMENT '发布IP',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '发布时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数�?`likes`
--

INSERT INTO `likes` (`id`, `aid`, `pid`, `uid`, `ip`, `created_at`) VALUES
(1, 1, 1, 1, '127.0.0.1', '2026-04-18 17:00:03');

-- --------------------------------------------------------

--
-- 表的结构 `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL COMMENT '角色名称',
  `slug` varchar(50) NOT NULL COMMENT '角色标识（唯一�?,
  `description` varchar(255) DEFAULT NULL COMMENT '角色描述',
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色�?;

--
-- 转存表中的数�?`roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `is_system`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '超级管理�?, 'root', NULL, 1, '2026-01-19 03:07:29', '2026-01-19 03:07:29', NULL),
(2, '管理�?, 'admin', NULL, 1, '2026-01-19 03:07:58', '2026-01-19 03:07:58', NULL),
(3, '用户', 'user', NULL, 1, '2026-01-19 03:08:26', '2026-01-19 03:08:26', NULL),
(4, '访客', 'guest', '11111', 1, '2026-01-19 03:08:40', '2026-05-21 17:32:42', NULL),
(5, '测试', 'test', '11111', 0, '2026-05-13 04:41:21', '2026-05-13 04:41:43', '2026-05-13 04:41:43'),
(6, 'test_role', 'test_role', NULL, 0, '2026-05-21 17:29:27', '2026-05-21 17:31:54', '2026-05-21 17:31:54');

-- --------------------------------------------------------

--
-- 表的结构 `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_hash` varchar(32) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数�?`role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_hash`, `created_at`) VALUES
(964, 1, '57803a52b9b3409627ef6ab163df4c7b', '2026-05-21 09:13:29'),
(965, 1, '80297b772c17fba790892cf31d39e8d4', '2026-05-21 09:13:29'),
(966, 1, '4e3b0f03bcc7473a04d154a1f7cc782e', '2026-05-21 09:13:29'),
(967, 1, '882b2b51c92e6b934ef33e455de1278a', '2026-05-21 09:13:29'),
(968, 1, '362cc75831f865291c87841a35addfff', '2026-05-21 09:13:29'),
(969, 1, '524d158eda284920a572f616e318b9ec', '2026-05-21 09:13:29'),
(970, 1, '49bfc767977cb39397db84ca44725ad9', '2026-05-21 09:13:29'),
(971, 1, 'ca0ec1f8043058e143bb4fba909618e1', '2026-05-21 09:13:29'),
(972, 1, '0294adb5fd940262b93353eeb6ef9d75', '2026-05-21 09:13:29'),
(973, 1, 'ddecc68b8395811eba778869b558727e', '2026-05-21 09:13:29'),
(974, 1, '9320f36e69f08335e3f0408081561663', '2026-05-21 09:13:29'),
(975, 1, '58c49989f206faa406cc0dae9f7f89ac', '2026-05-21 09:13:29'),
(976, 1, '1781286a1bc8f90a7ca0b5ea713f1fbe', '2026-05-21 09:13:29'),
(977, 1, '70fae84a8a8ca4068760981b48ce15a3', '2026-05-21 09:13:29'),
(978, 1, 'd974e11dc530e328927c56029c0ce45f', '2026-05-21 09:13:29'),
(979, 1, '44752202e24fb75f7bb47c32efb2e046', '2026-05-21 09:13:29'),
(980, 1, '6a5de705281af2f188ccf6c946030bf2', '2026-05-21 09:13:29'),
(981, 1, 'edc4c9e10faf5e2202330cece045f85f', '2026-05-21 09:13:29'),
(982, 1, 'ec20012c4f2d55c51579ee79d15fe9dc', '2026-05-21 09:13:29'),
(983, 1, '141bbf90821d98b61bdb72e2b99c0114', '2026-05-21 09:13:29'),
(984, 1, '820da6f7f491dd2f0b451d5152a1c7e4', '2026-05-21 09:13:29'),
(985, 1, '101cc359a33b098518b39a52a4400f35', '2026-05-21 09:13:29'),
(986, 1, '1dee45a95214a0cd318cc4e34a02dd00', '2026-05-21 09:13:29'),
(987, 1, '05c0d9ec1f4fcaacb981dc4ea3de768b', '2026-05-21 09:13:29'),
(988, 1, 'cdfbe93c2134807b41c496c6d630292a', '2026-05-21 09:13:29'),
(989, 1, 'a3af924c510501e503b12e256c4edeae', '2026-05-21 09:13:29'),
(990, 1, '1ad653af330839426981a3cc388972b2', '2026-05-21 09:13:29'),
(991, 1, '036c22b8309ec82d21b69a288690e005', '2026-05-21 09:13:29'),
(992, 1, '922b62a4ed0417641c8549168072d4a7', '2026-05-21 09:13:29'),
(993, 1, '0209eff97cc2e2940a7198cf8511c335', '2026-05-21 09:13:29'),
(994, 1, '6b24189eb1b755e7f7b8b80812183452', '2026-05-21 09:13:29'),
(995, 1, 'd77f9447412f4a26baff8a70d0f02d9b', '2026-05-21 09:13:29'),
(996, 1, '5b763c9857ee092b0ab96804d9ac40f9', '2026-05-21 09:13:29'),
(997, 1, '3fd0a57c77bc324ef58aac1c937ab7bc', '2026-05-21 09:13:29'),
(998, 1, '92ed9f8ecbdbfe84cd95e61bf3138d56', '2026-05-21 09:13:29'),
(999, 1, '3f738295b8276eb8801004c6a27cd8f5', '2026-05-21 09:13:29'),
(1000, 1, 'a0ca0b157564fe18314129a77f050684', '2026-05-21 09:13:29'),
(1001, 1, 'a085c8631a9467adb42eb5f619192d7f', '2026-05-21 09:13:29'),
(1002, 1, '19a989c1e45e070e8e8fe919fb5d8345', '2026-05-21 09:13:29'),
(1003, 1, 'f7217b4fc7ad759edf7a22db5d368259', '2026-05-21 09:13:29'),
(1004, 1, 'd7102263cf5b8529a79e2e72f0194a84', '2026-05-21 09:13:29'),
(1005, 1, 'bb035ea27fe74026aff5d2a48bd8ee50', '2026-05-21 09:13:29'),
(1006, 1, 'c91059dc56826c885ddcf870973a513a', '2026-05-21 09:13:29'),
(1007, 1, '610651a2d4c83398aefd5d67d5ba270c', '2026-05-21 09:13:29'),
(1008, 1, '8d4527b70feb175a00d0c20db558a408', '2026-05-21 09:13:29'),
(1009, 1, 'dbaf6289630541ad4b8e6454b8401d0b', '2026-05-21 09:13:29'),
(1010, 1, 'd48c13c857a3ad8e07b3ea4d66cb324e', '2026-05-21 09:13:29'),
(1011, 1, 'ad6565f1e38bee11447e7b2fd8101311', '2026-05-21 09:13:29'),
(1012, 1, '60e323133c58686de0fd0f4e06cd42af', '2026-05-21 09:13:29'),
(1013, 1, '22e9f6d02935b84b8495534d3bdc8f74', '2026-05-21 09:13:29'),
(1014, 1, '502bda58ed7a391d82ba4a2ea4aae70c', '2026-05-21 09:13:29'),
(1015, 1, 'dce47b51947a3ef2e63fdae3cf7ec92d', '2026-05-21 09:13:29'),
(1016, 1, '833a4f33050bab36abe9ef0e03a13429', '2026-05-21 09:13:29'),
(1017, 1, 'ad50036085a6235a78854b341540e623', '2026-05-21 09:13:29'),
(1018, 1, '8d18218da9bbd5d404ecdb33dd811b06', '2026-05-21 09:13:29'),
(1019, 1, '7792237d7bc8e76b5c5424c3a8b121de', '2026-05-21 09:13:29'),
(1020, 1, '5224dd5e6471e07afe84b91ae2302c7c', '2026-05-21 09:13:29'),
(1021, 1, 'c6931574551c4cdcf0c029bb45289d16', '2026-05-21 09:13:29'),
(1022, 1, 'b8efd3f05d8cc6903e5e6d4189abf712', '2026-05-21 09:13:29'),
(1023, 1, '2abea6c839bffc6c2156a886ade4915a', '2026-05-21 09:13:29'),
(1024, 1, '8846f3fc86a6f7eb7d42b43ff2122ad6', '2026-05-21 09:13:29'),
(1025, 1, 'b7c699dad94fd311ead98c6cad4d46eb', '2026-05-21 09:13:29'),
(1026, 1, '0c1c6ed5e99d4c94d037549f84964d9f', '2026-05-21 09:13:29'),
(1027, 1, '641fd2dca15554140853c9e0e539c514', '2026-05-21 09:13:29'),
(1028, 1, 'a449cb1fe71f30e1d2fd9c349b8dd339', '2026-05-21 09:13:29'),
(1029, 1, 'bc919f12b7f4654e051abe60e7a2d97e', '2026-05-21 09:13:29'),
(1030, 1, '5f179ae9475a2781f703c1fa8ee6b79d', '2026-05-21 09:13:29'),
(1031, 1, 'adf8322a8c87ebb5c38a1f35e6e9a11c', '2026-05-21 09:13:29'),
(1032, 1, 'e1e78d38a0ab0c6e1c7199091d2be7ef', '2026-05-21 09:13:29'),
(1033, 1, '87f971279bde4dc9a699c7abf60a5577', '2026-05-21 09:13:29'),
(1034, 1, '35f3191ead582602b1e084d142998e12', '2026-05-21 09:13:29'),
(1035, 1, '3d0974cf16696743bb79664dfdd41b40', '2026-05-21 09:13:29'),
(1036, 1, '14b04ed565e6cf8e29ed66e445073baf', '2026-05-21 09:13:29'),
(1037, 1, '36ffd3e3f998f1d4e908b497987fd3ad', '2026-05-21 09:13:29'),
(1038, 1, 'a3dada59533f110bb7405ef078492d6a', '2026-05-21 09:13:29'),
(1039, 1, 'fc3f6ad06a79b3d8d798d094ab1357bf', '2026-05-21 09:13:29'),
(1040, 1, '426450fc58e0ddee961f2085b1eba0f3', '2026-05-21 09:13:29'),
(1041, 2, 'ca0ec1f8043058e143bb4fba909618e1', '2026-05-21 09:13:29'),
(1042, 2, '0294adb5fd940262b93353eeb6ef9d75', '2026-05-21 09:13:29'),
(1043, 2, 'ddecc68b8395811eba778869b558727e', '2026-05-21 09:13:29'),
(1044, 2, '9320f36e69f08335e3f0408081561663', '2026-05-21 09:13:29'),
(1045, 2, '58c49989f206faa406cc0dae9f7f89ac', '2026-05-21 09:13:29'),
(1046, 2, 'edc4c9e10faf5e2202330cece045f85f', '2026-05-21 09:13:29'),
(1047, 2, 'ec20012c4f2d55c51579ee79d15fe9dc', '2026-05-21 09:13:29'),
(1048, 2, '141bbf90821d98b61bdb72e2b99c0114', '2026-05-21 09:13:29'),
(1049, 2, '820da6f7f491dd2f0b451d5152a1c7e4', '2026-05-21 09:13:29'),
(1050, 2, '101cc359a33b098518b39a52a4400f35', '2026-05-21 09:13:29'),
(1051, 2, '1dee45a95214a0cd318cc4e34a02dd00', '2026-05-21 09:13:29'),
(1052, 2, '05c0d9ec1f4fcaacb981dc4ea3de768b', '2026-05-21 09:13:29'),
(1053, 2, 'cdfbe93c2134807b41c496c6d630292a', '2026-05-21 09:13:29'),
(1054, 2, 'a3af924c510501e503b12e256c4edeae', '2026-05-21 09:13:29'),
(1055, 2, '1ad653af330839426981a3cc388972b2', '2026-05-21 09:13:29'),
(1056, 2, '036c22b8309ec82d21b69a288690e005', '2026-05-21 09:13:29'),
(1057, 2, '922b62a4ed0417641c8549168072d4a7', '2026-05-21 09:13:29'),
(1058, 2, 'a0ca0b157564fe18314129a77f050684', '2026-05-21 09:13:29'),
(1059, 2, 'f7217b4fc7ad759edf7a22db5d368259', '2026-05-21 09:13:29'),
(1060, 2, 'd7102263cf5b8529a79e2e72f0194a84', '2026-05-21 09:13:29'),
(1061, 2, 'bb035ea27fe74026aff5d2a48bd8ee50', '2026-05-21 09:13:29'),
(1062, 2, 'c91059dc56826c885ddcf870973a513a', '2026-05-21 09:13:29'),
(1063, 2, '610651a2d4c83398aefd5d67d5ba270c', '2026-05-21 09:13:29'),
(1064, 2, '8d4527b70feb175a00d0c20db558a408', '2026-05-21 09:13:29'),
(1065, 2, 'dbaf6289630541ad4b8e6454b8401d0b', '2026-05-21 09:13:29'),
(1066, 2, 'd48c13c857a3ad8e07b3ea4d66cb324e', '2026-05-21 09:13:29'),
(1067, 2, 'ad6565f1e38bee11447e7b2fd8101311', '2026-05-21 09:13:29'),
(1068, 2, '60e323133c58686de0fd0f4e06cd42af', '2026-05-21 09:13:29'),
(1069, 2, '22e9f6d02935b84b8495534d3bdc8f74', '2026-05-21 09:13:29'),
(1070, 2, '502bda58ed7a391d82ba4a2ea4aae70c', '2026-05-21 09:13:29'),
(1071, 2, 'dce47b51947a3ef2e63fdae3cf7ec92d', '2026-05-21 09:13:29'),
(1072, 2, '833a4f33050bab36abe9ef0e03a13429', '2026-05-21 09:13:29'),
(1073, 2, 'ad50036085a6235a78854b341540e623', '2026-05-21 09:13:29'),
(1074, 2, '8d18218da9bbd5d404ecdb33dd811b06', '2026-05-21 09:13:29'),
(1075, 2, '7792237d7bc8e76b5c5424c3a8b121de', '2026-05-21 09:13:29'),
(1076, 2, '5224dd5e6471e07afe84b91ae2302c7c', '2026-05-21 09:13:29'),
(1077, 2, 'c6931574551c4cdcf0c029bb45289d16', '2026-05-21 09:13:29'),
(1078, 2, 'b8efd3f05d8cc6903e5e6d4189abf712', '2026-05-21 09:13:29'),
(1079, 2, '0c1c6ed5e99d4c94d037549f84964d9f', '2026-05-21 09:13:29'),
(1080, 2, '641fd2dca15554140853c9e0e539c514', '2026-05-21 09:13:29'),
(1081, 2, 'a449cb1fe71f30e1d2fd9c349b8dd339', '2026-05-21 09:13:29'),
(1082, 2, 'bc919f12b7f4654e051abe60e7a2d97e', '2026-05-21 09:13:29'),
(1083, 2, '5f179ae9475a2781f703c1fa8ee6b79d', '2026-05-21 09:13:29'),
(1084, 2, '14b04ed565e6cf8e29ed66e445073baf', '2026-05-21 09:13:29'),
(1085, 2, '36ffd3e3f998f1d4e908b497987fd3ad', '2026-05-21 09:13:29'),
(1086, 2, 'a3dada59533f110bb7405ef078492d6a', '2026-05-21 09:13:29'),
(1087, 2, 'fc3f6ad06a79b3d8d798d094ab1357bf', '2026-05-21 09:13:29'),
(1088, 2, '426450fc58e0ddee961f2085b1eba0f3', '2026-05-21 09:13:29'),
(1089, 3, '57803a52b9b3409627ef6ab163df4c7b', '2026-05-21 09:13:29'),
(1090, 3, '80297b772c17fba790892cf31d39e8d4', '2026-05-21 09:13:29'),
(1091, 3, '4e3b0f03bcc7473a04d154a1f7cc782e', '2026-05-21 09:13:29'),
(1092, 3, '882b2b51c92e6b934ef33e455de1278a', '2026-05-21 09:13:29'),
(1093, 3, '362cc75831f865291c87841a35addfff', '2026-05-21 09:13:29'),
(1094, 3, '524d158eda284920a572f616e318b9ec', '2026-05-21 09:13:29'),
(1095, 3, '49bfc767977cb39397db84ca44725ad9', '2026-05-21 09:13:29'),
(1096, 3, '1781286a1bc8f90a7ca0b5ea713f1fbe', '2026-05-21 09:13:29'),
(1097, 3, '70fae84a8a8ca4068760981b48ce15a3', '2026-05-21 09:13:29'),
(1098, 3, 'd974e11dc530e328927c56029c0ce45f', '2026-05-21 09:13:29'),
(1099, 3, '44752202e24fb75f7bb47c32efb2e046', '2026-05-21 09:13:29'),
(1100, 3, '6a5de705281af2f188ccf6c946030bf2', '2026-05-21 09:13:29'),
(1101, 3, '0209eff97cc2e2940a7198cf8511c335', '2026-05-21 09:13:29'),
(1102, 3, '6b24189eb1b755e7f7b8b80812183452', '2026-05-21 09:13:29'),
(1103, 3, 'd77f9447412f4a26baff8a70d0f02d9b', '2026-05-21 09:13:29'),
(1104, 3, '5b763c9857ee092b0ab96804d9ac40f9', '2026-05-21 09:13:29'),
(1105, 3, '3fd0a57c77bc324ef58aac1c937ab7bc', '2026-05-21 09:13:29'),
(1106, 3, '92ed9f8ecbdbfe84cd95e61bf3138d56', '2026-05-21 09:13:29'),
(1107, 3, '3f738295b8276eb8801004c6a27cd8f5', '2026-05-21 09:13:29'),
(1108, 3, 'a085c8631a9467adb42eb5f619192d7f', '2026-05-21 09:13:29'),
(1109, 3, '19a989c1e45e070e8e8fe919fb5d8345', '2026-05-21 09:13:29'),
(1110, 3, '2abea6c839bffc6c2156a886ade4915a', '2026-05-21 09:13:29'),
(1111, 3, '8846f3fc86a6f7eb7d42b43ff2122ad6', '2026-05-21 09:13:29'),
(1112, 3, 'b7c699dad94fd311ead98c6cad4d46eb', '2026-05-21 09:13:29'),
(1113, 3, 'adf8322a8c87ebb5c38a1f35e6e9a11c', '2026-05-21 09:13:29'),
(1114, 3, 'e1e78d38a0ab0c6e1c7199091d2be7ef', '2026-05-21 09:13:29'),
(1115, 3, '87f971279bde4dc9a699c7abf60a5577', '2026-05-21 09:13:29'),
(1116, 3, '35f3191ead582602b1e084d142998e12', '2026-05-21 09:13:29'),
(1117, 3, '3d0974cf16696743bb79664dfdd41b40', '2026-05-21 09:13:29');

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
-- 转存表中的数�?`system`
--

INSERT INTO `system` (`id`, `name`, `value`) VALUES
(1, 'siteUrl', 'lovecards.cn'),
(2, 'siteName', 'LoveCardsV2.4'),
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
  `created_at` timestamp NULL DEFAULT NULL,
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
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

--
-- 转存表中的数�?`users`
--

INSERT INTO `users` (`id`, `number`, `avatar`, `email`, `phone`, `username`, `password`, `status`, `roles_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '1000000000', '', 'admin@lovecards.cn', '', '超级管理�?, '$2y$10$uBowOFgOBNTx1NT1uYJTleEo1r8d91R9iwxRCqncPJUShfsJoMvr6', 0, '[1, 2, 3]', '2023-12-06 12:09:26', '2025-08-01 12:50:25', NULL),
(2, '2841962312', '', '8079E68A9@g.com', '', 'GUEST37C57', '$2y$10$X5Dol3dVmUg71L9Me6lRceD824oPCgG5DnmAK3YGm/WrMynfaBc22', 0, '[3]', '2026-04-21 20:17:46', '2026-04-21 20:17:46', NULL),
(3, '4542393157', '', '1FCBFFCD9@g.com', '', 'GUEST12655', '$2y$10$kvgGVGAYsw28rejheg58Oe/fcVcrZyKHJUaYykOXE6KrnAxEdKm5G', 0, '[3]', '2026-04-22 08:40:49', '2026-04-22 08:40:49', NULL),
(4, '6222720845', '', '61A91936D@g.com', '', 'GUESTD9255', '$2y$10$kdbnHAwet9EYyR7OMJEwUeTPc9/jWvCnSqIgSN0AgxSJS8WsV12wq', 0, '[3]', '2026-04-22 10:52:53', '2026-04-22 10:52:53', NULL),
(5, '6808639375', '', '9D8801C4A@g.com', '', 'GUEST0486A', '$2y$10$oNFTdH4O0X24HN8X/o9I2.KHjx6DhaKYI3pjIsndFren4YcRrdpkO', 0, '[3]', '2026-04-22 11:33:45', '2026-04-22 11:33:45', NULL),
(6, '1059537860', '', '5848072BA@g.com', '', 'GUEST0BA66', '$2y$10$u3iK8MnSxztxep4vYz9MSuG6TeVgXriEAZ6kGRxChxwKv59GB.KBC', 0, '[3]', '2026-04-29 18:55:31', '2026-04-29 18:55:31', NULL),
(7, '0424474167', '', 'FD816F879@g.com', '', 'GUEST14DB1', '$2y$10$z8b9veqjJqzLZf/32BkhK.GJc3ceqsj.oWpVrkUjc/bLb1MSS9oVe', 0, '[3]', '2026-05-04 18:49:08', '2026-05-04 18:49:08', NULL),
(8, '7087057735', '', '9D6A40EA8@g.com', '', 'GUEST3D457', '$2y$10$Mmnnnf1gxvzdoFjenxIxoO7LIscZfI1uvNSt4DEJuJp498hYLiOKW', 0, '[null]', '2026-05-21 09:14:08', '2026-05-21 09:14:08', NULL);

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
-- 表的索引 `configs`
--
ALTER TABLE `configs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `group_key` (`group`,`key`);

--
-- 表的索引 `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_files_hash` (`hash`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_scene` (`scene`),
  ADD KEY `idx_ref` (`ref_type`,`ref_id`);

--
-- 表的索引 `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_pid_ip` (`pid`,`ip`);

--
-- 表的索引 `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `uk_slug` (`slug`);

--
-- 表的索引 `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_role_hash` (`role_id`,`permission_hash`),
  ADD KEY `idx_role_id` (`role_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `configs`
--
ALTER TABLE `configs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- 使用表AUTO_INCREMENT `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- 使用表AUTO_INCREMENT `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1118;

--
-- 使用表AUTO_INCREMENT `system`
--
ALTER TABLE `system`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- 使用表AUTO_INCREMENT `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tags_map`
--
ALTER TABLE `tags_map`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 限制导出的表
--

--
-- 限制�?`role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
