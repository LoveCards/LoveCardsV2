-- RBAC Route Name 迁移
-- 执行日期：2026-05-18

-- 1. 列重命名
ALTER TABLE `permissions` CHANGE `path` `route_name` varchar(255) NOT NULL COMMENT '路由标识';

-- 2. 索引重命名
ALTER TABLE `permissions` DROP INDEX `path_method`, ADD KEY `route_name_method` (`route_name`, `method`);

-- 3. 数据迁移：URL 路径 → 路由名
UPDATE `permissions` SET `route_name` = 'system.update' WHERE `slug` = 'system-update';
UPDATE `permissions` SET `route_name` = 'system.themes' WHERE `slug` = 'system-themes';
UPDATE `permissions` SET `route_name` = 'system.config.index' WHERE `slug` = 'system-config-get';
UPDATE `permissions` SET `route_name` = 'system.config.save' WHERE `slug` = 'system-config-set';
UPDATE `permissions` SET `route_name` = 'system.site' WHERE `slug` = 'system-site';
UPDATE `permissions` SET `route_name` = 'system.email' WHERE `slug` = 'system-email';
UPDATE `permissions` SET `route_name` = 'system.theme-config' WHERE `slug` = 'system-theme-config';
UPDATE `permissions` SET `route_name` = 'system.set-theme' WHERE `slug` = 'system-set-theme';
UPDATE `permissions` SET `route_name` = 'admin.users.index' WHERE `slug` = 'admin-users-list';
UPDATE `permissions` SET `route_name` = 'admin.users.update' WHERE `slug` = 'admin-user-update';
UPDATE `permissions` SET `route_name` = 'admin.users.destroy' WHERE `slug` = 'admin-user-delete';
UPDATE `permissions` SET `route_name` = 'admin.users.batch' WHERE `slug` = 'admin-users-batch';
UPDATE `permissions` SET `route_name` = 'admin.cards.show' WHERE `slug` = 'admin-card-get';
UPDATE `permissions` SET `route_name` = 'admin.cards.index' WHERE `slug` = 'admin-cards-list';
UPDATE `permissions` SET `route_name` = 'admin.cards.update' WHERE `slug` = 'admin-card-update';
UPDATE `permissions` SET `route_name` = 'admin.cards.destroy' WHERE `slug` = 'admin-cards-delete';
UPDATE `permissions` SET `route_name` = 'admin.cards.batch' WHERE `slug` = 'admin-cards-batch';
UPDATE `permissions` SET `route_name` = 'admin.comments.index' WHERE `slug` = 'admin-comments-list';
UPDATE `permissions` SET `route_name` = 'admin.comments.update' WHERE `slug` = 'admin-comment-update';
UPDATE `permissions` SET `route_name` = 'admin.comments.destroy' WHERE `slug` = 'admin-comment-delete';
UPDATE `permissions` SET `route_name` = 'admin.comments.batch' WHERE `slug` = 'admin-comments-batch';
UPDATE `permissions` SET `route_name` = 'admin.tags.index' WHERE `slug` = 'admin-tags-list';
UPDATE `permissions` SET `route_name` = 'admin.tags.store' WHERE `slug` = 'admin-tag-create';
UPDATE `permissions` SET `route_name` = 'admin.tags.update' WHERE `slug` = 'admin-tag-update';
UPDATE `permissions` SET `route_name` = 'admin.tags.destroy' WHERE `slug` = 'admin-tag-delete';
UPDATE `permissions` SET `route_name` = 'admin.tags.batch' WHERE `slug` = 'admin-tags-batch';
UPDATE `permissions` SET `route_name` = 'admin.dashboard' WHERE `slug` = 'admin-dashboard';
UPDATE `permissions` SET `route_name` = 'admin.roles.index' WHERE `slug` = 'admin-roles-list';
UPDATE `permissions` SET `route_name` = 'admin.roles.show' WHERE `slug` = 'admin-role-get';
UPDATE `permissions` SET `route_name` = 'admin.roles.store' WHERE `slug` = 'admin-role-create';
UPDATE `permissions` SET `route_name` = 'admin.roles.update' WHERE `slug` = 'admin-role-update';
UPDATE `permissions` SET `route_name` = 'admin.roles.destroy' WHERE `slug` = 'admin-role-delete';
UPDATE `permissions` SET `route_name` = 'admin.roles.assign' WHERE `slug` = 'admin-role-assign';
UPDATE `permissions` SET `route_name` = 'admin.roles.permissions' WHERE `slug` = 'admin-role-permissions';
UPDATE `permissions` SET `route_name` = 'admin.permissions.index' WHERE `slug` = 'admin-permissions-list';
UPDATE `permissions` SET `route_name` = 'admin.permissions.show' WHERE `slug` = 'admin-permission-get';
UPDATE `permissions` SET `route_name` = 'admin.permissions.store' WHERE `slug` = 'admin-permission-create';
UPDATE `permissions` SET `route_name` = 'admin.permissions.update' WHERE `slug` = 'admin-permission-update';
UPDATE `permissions` SET `route_name` = 'admin.permissions.destroy' WHERE `slug` = 'admin-permission-delete';
UPDATE `permissions` SET `route_name` = 'admin.permissions.all' WHERE `slug` = 'admin-permissions-all';
UPDATE `permissions` SET `route_name` = 'admin.role-permissions.store' WHERE `slug` = 'admin-role-permission-add';
UPDATE `permissions` SET `route_name` = 'admin.role-permissions.destroy' WHERE `slug` = 'admin-role-permission-remove';
UPDATE `permissions` SET `route_name` = 'admin.role-permissions.batch-store' WHERE `slug` = 'admin-role-permissions-batch-add';
UPDATE `permissions` SET `route_name` = 'admin.role-permissions.batch-destroy' WHERE `slug` = 'admin-role-permissions-batch-remove';
UPDATE `permissions` SET `route_name` = 'user.images.card' WHERE `slug` = 'upload-user-images';
UPDATE `permissions` SET `route_name` = 'storage.files.store' WHERE `slug` = 'upload-file';
UPDATE `permissions` SET `route_name` = 'storage.files.show' WHERE `slug` = 'storage-view';
UPDATE `permissions` SET `route_name` = 'storage.files.index' WHERE `slug` = 'storage-list';
UPDATE `permissions` SET `route_name` = 'cards.setting' WHERE `slug` = 'admin-cards-setting';
UPDATE `permissions` SET `route_name` = 'storage.files.batch' WHERE `slug` = 'storage-batch-operate';
UPDATE `permissions` SET `route_name` = 'storage.files.cleanup' WHERE `slug` = 'storage-cleanup';
UPDATE `permissions` SET `route_name` = 'storage.channels.index' WHERE `slug` = 'admin-storage-channels';
UPDATE `permissions` SET `route_name` = 'storage.channels.test' WHERE `slug` = 'admin-test-channel';
UPDATE `permissions` SET `route_name` = 'storage.channels.stats' WHERE `slug` = 'admin-channel-stats';
UPDATE `permissions` SET `route_name` = 'storage.files.direct' WHERE `slug` = 'storage-direct-credential';
UPDATE `permissions` SET `route_name` = 'storage.files.confirm' WHERE `slug` = 'storage-direct-confirm';
UPDATE `permissions` SET `route_name` = 'upload-credentials' WHERE `slug` = 'upload-credentials';
UPDATE `permissions` SET `route_name` = 'upload-confirm' WHERE `slug` = 'upload-confirm';
UPDATE `permissions` SET `route_name` = 'upload-avatar' WHERE `slug` = 'upload-avatar';
UPDATE `permissions` SET `route_name` = 'upload-card' WHERE `slug` = 'upload-card';
