-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2026-05-14 03:40:31
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
-- 转存表中的数据 `cards`
--

INSERT INTO `cards` (`id`, `is_top`, `status`, `user_id`, `data`, `cover`, `content`, `tags`, `goods`, `views`, `comments`, `post_ip`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 0, 0, 1, '{\"model\": \"0\", \"taName\": \"\", \"woName\": \"\", \"woContact\": \"\"}', NULL, '撒旦撒', '[]', 1, 1, 0, '127.0.0.1', '2026-04-18 16:59:59', '2026-04-18 16:59:59', NULL);

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
  `original_name` varchar(255) DEFAULT NULL COMMENT '原始文件名',
  `file_path` varchar(500) NOT NULL COMMENT '存储路径',
  `file_url` varchar(1000) NOT NULL COMMENT '访问URL',
  `file_size` int(11) NOT NULL COMMENT '文件大小(字节)',
  `file_ext` varchar(20) NOT NULL COMMENT '文件扩展名',
  `mime_type` varchar(100) DEFAULT NULL COMMENT 'MIME类型',
  `driver_path` varchar(500) DEFAULT NULL COMMENT '驱动特定标识',
  `metadata` json DEFAULT NULL COMMENT '额外元数据',
  `status` tinyint(1) DEFAULT '0' COMMENT '0:上传中 1:已完成 2:失败',
  `upload_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '上传状态：0=上传中 1=已完成 2=失败',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `expire_at` datetime DEFAULT NULL COMMENT '凭证过期时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文件记录表';

--
-- 转存表中的数据 `files`
--

INSERT INTO `files` (`id`, `channel_slug`, `user_id`, `is_public`, `scene`, `ref_type`, `ref_id`, `original_name`, `file_path`, `file_url`, `file_size`, `file_ext`, `mime_type`, `driver_path`, `metadata`, `status`, `upload_status`, `created_at`, `updated_at`, `deleted_at`, `expire_at`) VALUES
(1, 'local', NULL, 1, 'direct', NULL, NULL, '屏幕截图 2025-08-25 002000.png', 'image/images/20260430/69f25c9ae474b.png', 'http://127.0.0.1:8001/storage/image/images/20260430/69f25c9ae474b.png', 393, 'png', 'image/png', 'image/images/20260430/69f25c9ae474b.png', NULL, 3, 1, '2026-04-30 03:31:38', '2026-05-14 00:15:58', '2026-05-14 00:15:58', NULL),
(2, 'local', NULL, 1, 'direct', NULL, NULL, 'avatar.png', 'image/images/20260509/69fe1f7df1362.png', 'http://127.0.0.1:8001/storage/image/images/20260509/69fe1f7df1362.png', 3999, 'png', 'image/png', 'image/images/20260509/69fe1f7df1362.png', NULL, 3, 1, '2026-05-09 01:38:06', '2026-05-14 00:13:59', '2026-05-14 00:13:59', NULL),
(3, 'local', NULL, 0, 'direct', NULL, NULL, 'avatar.png', 'image/images/20260509/69fe20dab46e6.png', 'http://127.0.0.1:8001/storage/image/images/20260509/69fe20dab46e6.png', 3999, 'png', 'image/png', 'image/images/20260509/69fe20dab46e6.png', NULL, 3, 2, '2026-05-09 01:43:54', '2026-05-14 00:14:00', NULL, NULL),
(5, 'cos', NULL, 0, 'direct', NULL, NULL, 'avatar.png', 'images/20260509/69fe25489263c', 'https://test-1253544066.cos.ap-guangzhou.myqcloud.com/images/20260509/69fe25489263c', 3999, 'png', 'image/png', 'images/20260509/69fe25489263c', NULL, 3, 1, '2026-05-09 02:02:49', '2026-05-14 00:12:14', NULL, NULL),
(9, 'cos', 7, 1, 'direct', NULL, NULL, '屏幕截图 2025-08-25 002000.png', 'images/20260513/6a037879a72be', 'https://test-1253544066.cos.ap-guangzhou.myqcloud.com/images/20260513/6a037879a72be', 393, 'png', 'image/png', 'images/20260513/6a037879a72be', NULL, 0, 2, '2026-05-13 02:59:06', '2026-05-14 02:40:46', '2026-05-14 03:00:11', NULL),
(10, 'cos', 1, 1, 'direct', NULL, NULL, 'avatar.png', 'images/20260513/6a037abd09094', 'https://test-1253544066.cos.ap-guangzhou.myqcloud.com/images/20260513/6a037abd09094', 3999, 'png', 'image/png', 'images/20260513/6a037abd09094', NULL, 0, 1, '2026-05-13 03:08:45', '2026-05-14 02:56:21', '2026-05-14 03:00:11', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `files_test`
--

CREATE TABLE `files_test` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '上传用户ID',
  `disk` varchar(32) NOT NULL DEFAULT 'local' COMMENT '存储驱动: local/oss/cos/chitu',
  `path` varchar(255) NOT NULL DEFAULT '' COMMENT '存储相对路径/图床标识',
  `url` varchar(512) NOT NULL DEFAULT '' COMMENT '访问URL',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '原始文件名',
  `mime_type` varchar(128) NOT NULL DEFAULT '' COMMENT 'MIME类型',
  `size` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '文件大小(字节)',
  `hash` varchar(64) NOT NULL DEFAULT '' COMMENT '文件哈希(MD5)',
  `scene` varchar(32) NOT NULL DEFAULT '' COMMENT '业务场景: avatar/card/comment',
  `ref_type` varchar(32) NOT NULL DEFAULT '' COMMENT '关联类型: card/comment/user',
  `ref_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '关联ID',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1正常 0禁用',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文件表';

--
-- 转存表中的数据 `files_test`
--

INSERT INTO `files_test` (`id`, `user_id`, `disk`, `path`, `url`, `name`, `mime_type`, `size`, `hash`, `scene`, `ref_type`, `ref_id`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(5, 5, 'cos', 'cards/5/20260422/20260422\\a7ee4cfdbbf5d5e94ac45e13b63e4c91.png', 'testt-1253544066.cos.ap-guangzhou.myqcloud.com/cards/5/20260422/20260422\\a7ee4cfdbbf5d5e94ac45e13b63e4c91.png', 'cos_test.png', 'image/png', 8, 'e9dd2797018cad79186e03e8c5aec8dc', 'card', '', 0, 1, '2026-04-22 11:35:39', '2026-04-22 11:35:39', NULL),
(6, 1, 'cos', 'cards/1/20260422/20260422\\cea4a921af9f0e6bf52f91fa8c7ac5a8.png', 'testt-1253544066.cos.ap-guangzhou.myqcloud.com/cards/1/20260422/20260422\\cea4a921af9f0e6bf52f91fa8c7ac5a8.png', '屏幕截图 2025-08-25 002043.png', 'image/png', 1686, 'afe30f2f9880d1b28cd6d9b512cb5e5d', 'card', 'card', 0, 1, '2026-04-22 11:37:14', '2026-04-22 11:37:14', NULL);

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
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
-- 转存表中的数据 `likes`
--

INSERT INTO `likes` (`id`, `aid`, `pid`, `uid`, `ip`, `created_at`) VALUES
(1, 1, 1, 1, '127.0.0.1', '2026-04-18 17:00:03');

-- --------------------------------------------------------

--
-- 表的结构 `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT '权限名称',
  `slug` varchar(100) NOT NULL COMMENT '权限标识（唯一）',
  `path` varchar(255) NOT NULL COMMENT '权限路径（如：/api/admin/users）',
  `method` varchar(10) NOT NULL DEFAULT 'GET' COMMENT 'HTTP方法：GET,POST,PUT,PATCH,DELETE,*',
  `description` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限表';

--
-- 转存表中的数据 `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `path`, `method`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '系统更新检查', 'system-update', '/api/system/updata', 'GET', '检查系统更新', '2026-01-19 04:01:18', '2026-01-19 04:01:18', NULL),
(2, '获取主题列表', 'system-themes', '/api/system/themes', 'GET', '获取可用主题列表', '2026-01-19 04:01:18', '2026-01-19 04:01:18', NULL),
(3, '获取系统配置', 'system-config-get', '/api/system/config', 'GET', '获取系统配置信息', '2026-01-19 04:01:18', '2026-01-19 04:01:18', NULL),
(4, '设置系统配置', 'system-config-set', '/api/system/config', 'POST', '设置系统配置', '2026-01-19 04:01:18', '2026-01-19 04:01:18', NULL),
(5, '系统站点设置', 'system-site', '/api/system/site', 'POST', '系统站点设置', '2026-01-19 04:01:18', '2026-01-19 04:01:18', NULL),
(6, '系统邮箱设置', 'system-email', '/api/system/email', 'PATCH', '系统邮箱设置', '2026-01-19 04:01:18', '2026-01-19 04:01:18', NULL),
(7, '设置主题配置', 'system-theme-config', '/api/system/theme-config', 'POST', '设置主题配置', '2026-01-19 04:01:18', '2026-01-19 04:01:18', NULL),
(8, '设置主题', 'system-set-theme', '/api/system/set-theme', 'POST', '设置当前主题', '2026-01-19 04:01:18', '2026-01-19 04:01:18', NULL),
(9, '获取用户列表', 'admin-users-list', '/api/admin/users', 'GET', '获取用户列表', '2026-01-19 04:01:18', '2026-01-19 04:01:18', NULL),
(10, '更新用户', 'admin-user-update', '/api/admin/user', 'PATCH', '更新用户信息', '2026-01-19 04:01:18', '2026-01-19 04:01:18', NULL),
(11, '删除用户', 'admin-user-delete', '/api/admin/user', 'DELETE', '删除用户', '2026-01-19 04:01:18', '2026-01-19 04:01:18', NULL),
(12, '批量操作用户', 'admin-users-batch', '/api/admin/users/batch-operate', 'POST', '批量操作用户', '2026-01-19 04:01:18', '2026-01-19 04:01:18', NULL),
(13, '获取单个卡片', 'admin-card-get', '/api/admin/card', 'GET', '获取单个卡片详情', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(14, '获取卡片列表', 'admin-cards-list', '/api/admin/cards', 'GET', '获取卡片列表', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(15, '更新卡片', 'admin-card-update', '/api/admin/card', 'PATCH', '更新卡片信息', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(16, '删除卡片', 'admin-cards-delete', '/api/admin/cards', 'DELETE', '删除卡片', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(17, '批量操作卡片', 'admin-cards-batch', '/api/admin/cards/batch-operate', 'POST', '批量操作卡片', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(18, '获取评论列表', 'admin-comments-list', '/api/admin/comments', 'GET', '获取评论列表', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(19, '更新评论', 'admin-comment-update', '/api/admin/comment', 'PATCH', '更新评论信息', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(20, '删除评论', 'admin-comment-delete', '/api/admin/comment', 'DELETE', '删除评论', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(21, '批量操作评论', 'admin-comments-batch', '/api/admin/comments/batch-operate', 'POST', '批量操作评论', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(22, '获取标签列表', 'admin-tags-list', '/api/admin/tags', 'GET', '获取标签列表', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(23, '创建标签', 'admin-tag-create', '/api/admin/tag', 'POST', '创建标签', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(24, '更新标签', 'admin-tag-update', '/api/admin/tag', 'PATCH', '更新标签信息', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(25, '删除标签', 'admin-tag-delete', '/api/admin/tag', 'DELETE', '删除标签', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(26, '批量操作标签', 'admin-tags-batch', '/api/admin/tags/batch-operate', 'POST', '批量操作标签', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(27, '控制台', 'admin-dashboard', '/api/admin/dashboard', 'GET', '访问控制台', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(28, '获取角色列表', 'admin-roles-list', '/api/admin/roles', 'GET', '获取角色列表', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(29, '获取单个角色', 'admin-role-get', '/api/admin/role', 'GET', '获取单个角色详情', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(30, '创建角色', 'admin-role-create', '/api/admin/role', 'POST', '创建角色', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(31, '更新角色', 'admin-role-update', '/api/admin/role', 'PATCH', '更新角色信息', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(32, '删除角色', 'admin-role-delete', '/api/admin/role', 'DELETE', '删除角色', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(33, '分配权限', 'admin-role-assign', '/api/admin/role/assign-permissions', 'POST', '为角色分配权限', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(34, '获取角色权限', 'admin-role-permissions', '/api/admin/role/permissions', 'GET', '获取角色的权限列表', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(35, '获取权限列表', 'admin-permissions-list', '/api/admin/permissions', 'GET', '获取权限列表', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(36, '获取单个权限', 'admin-permission-get', '/api/admin/permission', 'GET', '获取单个权限详情', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(37, '创建权限', 'admin-permission-create', '/api/admin/permission', 'POST', '创建权限', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(38, '更新权限', 'admin-permission-update', '/api/admin/permission', 'PATCH', '更新权限信息', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(39, '删除权限', 'admin-permission-delete', '/api/admin/permission', 'DELETE', '删除权限', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(40, '获取所有权限', 'admin-permissions-all', '/api/admin/permissions/all', 'GET', '获取所有权限（不分页）', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(41, '添加角色权限', 'admin-role-permission-add', '/api/admin/role-permission', 'POST', '为角色添加权限', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(42, '移除角色权限', 'admin-role-permission-remove', '/api/admin/role-permission', 'DELETE', '移除角色权限', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(43, '批量添加角色权限', 'admin-role-permissions-batch-add', '/api/admin/role-permissions/batch-add', 'POST', '批量添加角色权限', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(44, '批量移除角色权限', 'admin-role-permissions-batch-remove', '/api/admin/role-permissions/batch-remove', 'POST', '批量移除角色权限', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(45, '获取标签列表', 'user-tags-list', '/api/tags', 'GET', '获取标签列表', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(46, '获取卡片图集', 'user-card-images', '/api/card/images', 'GET', '获取卡片图集', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(47, '获取卡片列表', 'user-cards-list', '/api/cards', 'GET', '获取卡片列表', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(48, '喜欢卡片', 'user-card-like', '/api/card/like', 'POST', '喜欢卡片', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(49, '创建评论', 'user-card-comment', '/api/card/comment', 'POST', '创建评论', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(50, '创建卡片', 'user-card-create', '/api/card', 'POST', '创建卡片', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(51, '删除卡片', 'user-card-delete', '/api/card', 'DELETE', '删除卡片', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(52, '删除评论', 'user-comment-delete', '/api/comment', 'DELETE', '删除评论', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(53, '取消喜欢', 'user-like-delete', '/api/like', 'DELETE', '取消喜欢', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(54, '获取评论列表', 'user-comments-list', '/api/comments', 'GET', '获取评论列表', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(55, '获取喜欢列表', 'user-likes-list', '/api/likes', 'GET', '获取喜欢列表', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(56, '更新用户信息', 'user-info-update', '/api/user/info', 'PATCH', '更新用户信息', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(57, '获取用户信息', 'user-info-get', '/api/user/info', 'GET', '获取用户信息', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(58, '修改密码', 'user-password', '/api/user/password', 'POST', '修改密码', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(59, '绑定邮箱', 'user-email', '/api/user/email', 'POST', '绑定邮箱', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(60, '获取邮箱验证码', 'user-email-captcha', '/api/user/email-captcha', 'POST', '获取邮箱验证码', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(61, '上传用户图片', 'upload-user-images', '/api/upload/user-images', 'POST', '上传用户图片', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(62, '访客-获取卡片列表', 'guest-cards-list', '/api/cards', 'GET', '访客获取卡片列表', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(63, '访客-获取评论列表', 'guest-comments-list', '/api/comments', 'GET', '访客获取评论列表', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(64, '访客-获取喜欢列表', 'guest-likes-list', '/api/likes', 'GET', '访客获取喜欢列表', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(65, '访客-获取用户信息', 'guest-user-info', '/api/user/info', 'GET', '访客获取用户信息', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(66, '访客-获取标签列表', 'guest-tags-list', '/api/tags', 'GET', '访客获取标签列表', '2026-01-19 04:01:19', '2026-01-19 04:01:19', NULL),
(67, '上传文件', 'upload-file', '/api/upload/upload', 'POST', '场景化文件上传', '2026-04-22 04:09:10', '2026-04-22 04:09:10', NULL),
(68, '获取上传凭证', 'upload-credentials', '/api/upload/credentials', 'GET', '获取直传临时凭证', '2026-04-22 04:09:10', '2026-04-22 04:09:10', NULL),
(69, '确认上传', 'upload-confirm', '/api/upload/confirm', 'POST', '确认直传上传结果', '2026-04-22 04:09:10', '2026-04-22 04:09:10', NULL),
(70, '删除文件', 'upload-delete', '/api/upload', 'DELETE', '删除已上传文件', '2026-04-22 04:09:10', '2026-04-22 04:09:10', NULL),
(71, '上传头像', 'upload-avatar', '/api/upload/avatar', 'POST', '上传头像专用', '2026-04-22 04:23:51', '2026-05-13 03:46:04', NULL),
(72, '上传卡片图片', 'upload-card', '/api/upload/card', 'POST', '上传卡片图片', '2026-04-22 16:40:28', '2026-04-22 16:40:28', NULL),
(73, '查看文件', 'storage-view', '/api/upload/get-file', 'GET', '查看文件信息', '2026-05-12 21:31:57', '2026-05-12 21:31:57', NULL),
(74, '文件列表', 'storage-list', '/api/upload/files', 'GET', '获取文件列表', '2026-05-12 21:31:57', '2026-05-12 21:31:57', NULL),
(75, '删除恢复文件', 'storage-toggle-delete', '/api/upload/toggle-delete', 'POST', '删除或恢复文件', '2026-05-12 21:31:57', '2026-05-12 21:31:57', NULL),
(76, '设置公开', 'storage-toggle-public', '/api/upload/toggle-public', 'POST', '切换文件公开状态', '2026-05-12 21:31:57', '2026-05-12 21:31:57', NULL),
(77, '审核文件', 'storage-review', '/api/upload/review', 'POST', '审核文件', '2026-05-12 21:31:57', '2026-05-12 21:31:57', NULL),
(78, '批量审核', 'storage-batch-review', '/api/upload/batch-review', 'POST', '批量审核文件', '2026-05-12 21:31:57', '2026-05-12 21:31:57', NULL),
(79, '硬删除文件', 'storage-hard-delete', '/api/upload/hard-delete', 'POST', '硬删除文件', '2026-05-12 21:31:57', '2026-05-12 21:31:57', NULL),
(80, '清理过期', 'storage-cleanup', '/api/upload/cleanup', 'POST', '清理过期文件', '2026-05-12 21:31:57', '2026-05-12 21:31:57', NULL),
(82, '批量操作文件', 'storage-batch-operate', '/api/upload/batch-operate', 'POST', '批量操作文件（审核/封禁/公开/恢复/删除）', '2026-05-14 02:51:37', '2026-05-14 02:51:37', NULL);

-- --------------------------------------------------------

--
-- 表的结构 `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL COMMENT '角色名称',
  `slug` varchar(50) NOT NULL COMMENT '角色标识（唯一）',
  `description` varchar(255) DEFAULT NULL COMMENT '角色描述',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色表';

--
-- 转存表中的数据 `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '超级管理员', 'root', NULL, '2026-01-19 03:07:29', '2026-01-19 03:07:29', NULL),
(2, '管理员', 'admin', NULL, '2026-01-19 03:07:58', '2026-01-19 03:07:58', NULL),
(3, '用户', 'user', NULL, '2026-01-19 03:08:26', '2026-01-19 03:08:26', NULL),
(4, '访客', 'guest', NULL, '2026-01-19 03:08:40', '2026-01-19 03:08:40', NULL),
(5, '测试', 'test', '11111', '2026-05-13 04:41:21', '2026-05-13 04:41:43', '2026-05-13 04:41:43');

-- --------------------------------------------------------

--
-- 表的结构 `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL COMMENT '角色ID',
  `permission_id` int(11) NOT NULL COMMENT '权限ID',
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色权限关联表';

--
-- 转存表中的数据 `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES
(1, 1, 1, '2026-01-18 20:01:19'),
(2, 1, 2, '2026-01-18 20:01:19'),
(3, 1, 3, '2026-01-18 20:01:19'),
(4, 1, 4, '2026-01-18 20:01:19'),
(5, 1, 5, '2026-01-18 20:01:19'),
(6, 1, 6, '2026-01-18 20:01:19'),
(7, 1, 7, '2026-01-18 20:01:19'),
(8, 1, 8, '2026-01-18 20:01:19'),
(9, 1, 9, '2026-01-18 20:01:19'),
(10, 1, 10, '2026-01-18 20:01:19'),
(11, 1, 11, '2026-01-18 20:01:19'),
(12, 1, 12, '2026-01-18 20:01:19'),
(13, 1, 13, '2026-01-18 20:01:19'),
(14, 1, 14, '2026-01-18 20:01:19'),
(15, 1, 15, '2026-01-18 20:01:19'),
(16, 1, 16, '2026-01-18 20:01:19'),
(17, 1, 17, '2026-01-18 20:01:19'),
(18, 1, 18, '2026-01-18 20:01:19'),
(19, 1, 19, '2026-01-18 20:01:19'),
(20, 1, 20, '2026-01-18 20:01:19'),
(21, 1, 21, '2026-01-18 20:01:19'),
(22, 1, 22, '2026-01-18 20:01:19'),
(23, 1, 23, '2026-01-18 20:01:19'),
(24, 1, 24, '2026-01-18 20:01:19'),
(25, 1, 25, '2026-01-18 20:01:19'),
(26, 1, 26, '2026-01-18 20:01:19'),
(27, 1, 27, '2026-01-18 20:01:19'),
(28, 1, 28, '2026-01-18 20:01:19'),
(29, 1, 29, '2026-01-18 20:01:19'),
(30, 1, 30, '2026-01-18 20:01:19'),
(31, 1, 31, '2026-01-18 20:01:19'),
(32, 1, 32, '2026-01-18 20:01:19'),
(33, 1, 33, '2026-01-18 20:01:19'),
(34, 1, 34, '2026-01-18 20:01:19'),
(35, 1, 35, '2026-01-18 20:01:19'),
(36, 1, 36, '2026-01-18 20:01:19'),
(37, 1, 37, '2026-01-18 20:01:19'),
(38, 1, 38, '2026-01-18 20:01:19'),
(39, 1, 39, '2026-01-18 20:01:19'),
(40, 1, 40, '2026-01-18 20:01:19'),
(41, 1, 41, '2026-01-18 20:01:19'),
(42, 1, 42, '2026-01-18 20:01:19'),
(43, 1, 43, '2026-01-18 20:01:19'),
(44, 1, 44, '2026-01-18 20:01:19'),
(45, 1, 45, '2026-01-18 20:01:19'),
(46, 1, 46, '2026-01-18 20:01:19'),
(47, 1, 47, '2026-01-18 20:01:19'),
(48, 1, 48, '2026-01-18 20:01:19'),
(49, 1, 49, '2026-01-18 20:01:19'),
(50, 1, 50, '2026-01-18 20:01:19'),
(51, 1, 51, '2026-01-18 20:01:19'),
(52, 1, 52, '2026-01-18 20:01:19'),
(53, 1, 53, '2026-01-18 20:01:19'),
(54, 1, 54, '2026-01-18 20:01:19'),
(55, 1, 55, '2026-01-18 20:01:19'),
(56, 1, 56, '2026-01-18 20:01:19'),
(57, 1, 57, '2026-01-18 20:01:19'),
(58, 1, 58, '2026-01-18 20:01:19'),
(59, 1, 59, '2026-01-18 20:01:19'),
(60, 1, 60, '2026-01-18 20:01:19'),
(61, 1, 61, '2026-01-18 20:01:19'),
(62, 1, 62, '2026-01-18 20:01:19'),
(63, 1, 63, '2026-01-18 20:01:19'),
(64, 1, 64, '2026-01-18 20:01:19'),
(65, 1, 65, '2026-01-18 20:01:19'),
(66, 1, 66, '2026-01-18 20:01:19'),
(128, 2, 13, '2026-01-18 20:01:19'),
(129, 2, 15, '2026-01-18 20:01:19'),
(130, 2, 17, '2026-01-18 20:01:19'),
(131, 2, 16, '2026-01-18 20:01:19'),
(132, 2, 14, '2026-01-18 20:01:19'),
(133, 2, 20, '2026-01-18 20:01:19'),
(134, 2, 19, '2026-01-18 20:01:19'),
(135, 2, 21, '2026-01-18 20:01:19'),
(136, 2, 18, '2026-01-18 20:01:19'),
(137, 2, 27, '2026-01-18 20:01:19'),
(138, 2, 23, '2026-01-18 20:01:19'),
(139, 2, 25, '2026-01-18 20:01:19'),
(140, 2, 24, '2026-01-18 20:01:19'),
(141, 2, 26, '2026-01-18 20:01:19'),
(142, 2, 22, '2026-01-18 20:01:19'),
(143, 3, 61, '2026-01-18 20:01:19'),
(144, 3, 49, '2026-01-18 20:01:19'),
(145, 3, 50, '2026-01-18 20:01:19'),
(146, 3, 51, '2026-01-18 20:01:19'),
(147, 3, 46, '2026-01-18 20:01:19'),
(148, 3, 48, '2026-01-18 20:01:19'),
(149, 3, 47, '2026-01-18 20:01:19'),
(150, 3, 52, '2026-01-18 20:01:19'),
(151, 3, 54, '2026-01-18 20:01:19'),
(152, 3, 59, '2026-01-18 20:01:19'),
(153, 3, 60, '2026-01-18 20:01:19'),
(154, 3, 57, '2026-01-18 20:01:19'),
(155, 3, 56, '2026-01-18 20:01:19'),
(156, 3, 53, '2026-01-18 20:01:19'),
(157, 3, 55, '2026-01-18 20:01:19'),
(158, 3, 58, '2026-01-18 20:01:19'),
(159, 3, 45, '2026-01-18 20:01:19'),
(174, 4, 62, '2026-01-18 20:01:19'),
(175, 4, 63, '2026-01-18 20:01:19'),
(176, 4, 64, '2026-01-18 20:01:19'),
(177, 4, 66, '2026-01-18 20:01:19'),
(178, 4, 65, '2026-01-18 20:01:19'),
(181, 1, 67, '2026-04-21 20:09:25'),
(182, 1, 68, '2026-04-21 20:09:25'),
(183, 1, 69, '2026-04-21 20:09:25'),
(184, 1, 70, '2026-04-21 20:09:25'),
(185, 2, 67, '2026-04-21 20:09:25'),
(186, 2, 68, '2026-04-21 20:09:25'),
(187, 2, 69, '2026-04-21 20:09:25'),
(188, 2, 70, '2026-04-21 20:09:25'),
(189, 3, 67, '2026-04-21 20:09:25'),
(190, 3, 68, '2026-04-21 20:09:25'),
(191, 3, 69, '2026-04-21 20:09:25'),
(192, 3, 71, '2026-04-21 20:23:54'),
(193, 3, 70, '2026-04-21 20:28:34'),
(194, 3, 72, '2026-04-22 08:40:31'),
(195, 3, 73, '2026-05-12 13:33:36'),
(196, 3, 74, '2026-05-12 13:33:36'),
(197, 3, 75, '2026-05-12 13:33:36'),
(198, 3, 76, '2026-05-12 13:33:36'),
(199, 2, 73, '2026-05-12 13:33:42'),
(200, 2, 74, '2026-05-12 13:33:42'),
(201, 2, 75, '2026-05-12 13:33:42'),
(202, 2, 76, '2026-05-12 13:33:42'),
(203, 2, 77, '2026-05-12 13:33:42'),
(204, 2, 78, '2026-05-12 13:33:42'),
(205, 2, 79, '2026-05-12 13:33:42'),
(206, 2, 80, '2026-05-12 13:33:42'),
(208, 1, 73, '2026-05-12 19:29:55'),
(209, 1, 74, '2026-05-12 19:29:55'),
(210, 1, 75, '2026-05-12 19:29:55'),
(211, 1, 76, '2026-05-12 19:29:55'),
(212, 1, 77, '2026-05-12 19:29:55'),
(213, 1, 78, '2026-05-12 19:29:55'),
(214, 1, 79, '2026-05-12 19:29:55'),
(215, 1, 80, '2026-05-12 19:29:55'),
(304, 1, 82, '2026-05-13 18:51:42'),
(305, 2, 82, '2026-05-13 18:51:42');

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
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`id`, `number`, `avatar`, `email`, `phone`, `username`, `password`, `status`, `roles_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '1000000000', '', 'admin@lovecards.cn', '', '超级管理员', '$2y$10$uBowOFgOBNTx1NT1uYJTleEo1r8d91R9iwxRCqncPJUShfsJoMvr6', 0, '[1, 2, 3]', '2023-12-06 12:09:26', '2025-08-01 12:50:25', NULL),
(2, '2841962312', '', '8079E68A9@g.com', '', 'GUEST37C57', '$2y$10$X5Dol3dVmUg71L9Me6lRceD824oPCgG5DnmAK3YGm/WrMynfaBc22', 0, '[3]', '2026-04-21 20:17:46', '2026-04-21 20:17:46', NULL),
(3, '4542393157', '', '1FCBFFCD9@g.com', '', 'GUEST12655', '$2y$10$kvgGVGAYsw28rejheg58Oe/fcVcrZyKHJUaYykOXE6KrnAxEdKm5G', 0, '[3]', '2026-04-22 08:40:49', '2026-04-22 08:40:49', NULL),
(4, '6222720845', '', '61A91936D@g.com', '', 'GUESTD9255', '$2y$10$kdbnHAwet9EYyR7OMJEwUeTPc9/jWvCnSqIgSN0AgxSJS8WsV12wq', 0, '[3]', '2026-04-22 10:52:53', '2026-04-22 10:52:53', NULL),
(5, '6808639375', '', '9D8801C4A@g.com', '', 'GUEST0486A', '$2y$10$oNFTdH4O0X24HN8X/o9I2.KHjx6DhaKYI3pjIsndFren4YcRrdpkO', 0, '[3]', '2026-04-22 11:33:45', '2026-04-22 11:33:45', NULL),
(6, '1059537860', '', '5848072BA@g.com', '', 'GUEST0BA66', '$2y$10$u3iK8MnSxztxep4vYz9MSuG6TeVgXriEAZ6kGRxChxwKv59GB.KBC', 0, '[3]', '2026-04-29 18:55:31', '2026-04-29 18:55:31', NULL),
(7, '0424474167', '', 'FD816F879@g.com', '', 'GUEST14DB1', '$2y$10$z8b9veqjJqzLZf/32BkhK.GJc3ceqsj.oWpVrkUjc/bLb1MSS9oVe', 0, '[3]', '2026-05-04 18:49:08', '2026-05-04 18:49:08', NULL);

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
-- 表的索引 `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_scene` (`scene`),
  ADD KEY `idx_ref` (`ref_type`,`ref_id`);

--
-- 表的索引 `files_test`
--
ALTER TABLE `files_test`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_hash` (`hash`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_scene` (`scene`),
  ADD KEY `idx_ref` (`ref_type`,`ref_id`),
  ADD KEY `idx_disk_path` (`disk`,`path`(191));

--
-- 表的索引 `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `path_method` (`path`,`method`);

--
-- 表的索引 `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- 表的索引 `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_permission` (`role_id`,`permission_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `permission_id` (`permission_id`);

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
-- 使用表AUTO_INCREMENT `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- 使用表AUTO_INCREMENT `files_test`
--
ALTER TABLE `files_test`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `images`
--
ALTER TABLE `images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- 使用表AUTO_INCREMENT `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=306;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- 限制导出的表
--

--
-- 限制表 `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
