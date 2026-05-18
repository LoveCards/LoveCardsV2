-- RBAC upgrade migration
-- Date: 2026-05-18

-- 1. roles add is_system
ALTER TABLE `roles` ADD `is_system` TINYINT(1) NOT NULL DEFAULT 0 AFTER `description`;

-- 2. mark system roles
UPDATE `roles` SET `is_system` = 1 WHERE `id` IN (1, 2, 3, 4);

-- 3. user route permission route_name migration
UPDATE `permissions` SET `route_name` = 'user.tags.index' WHERE `slug` = 'user-tags-list';
UPDATE `permissions` SET `route_name` = 'user.card-images.index' WHERE `slug` = 'user-card-images';
UPDATE `permissions` SET `route_name` = 'user.cards.index' WHERE `slug` = 'user-cards-list';
UPDATE `permissions` SET `route_name` = 'user.cards.like' WHERE `slug` = 'user-card-like';
UPDATE `permissions` SET `route_name` = 'user.cards.comment' WHERE `slug` = 'user-card-comment';
UPDATE `permissions` SET `route_name` = 'user.cards.store' WHERE `slug` = 'user-card-create';
UPDATE `permissions` SET `route_name` = 'user.cards.destroy' WHERE `slug` = 'user-card-delete';
UPDATE `permissions` SET `route_name` = 'user.comments.index' WHERE `slug` = 'user-comments-list';
UPDATE `permissions` SET `route_name` = 'user.comments.destroy' WHERE `slug` = 'user-comment-delete';
UPDATE `permissions` SET `route_name` = 'user.likes.index' WHERE `slug` = 'user-likes-list';
UPDATE `permissions` SET `route_name` = 'user.likes.destroy' WHERE `slug` = 'user-like-delete';
UPDATE `permissions` SET `route_name` = 'user.info.show' WHERE `slug` = 'user-info-get';
UPDATE `permissions` SET `route_name` = 'user.info.update' WHERE `slug` = 'user-info-update';
UPDATE `permissions` SET `route_name` = 'user.info.password' WHERE `slug` = 'user-password';
UPDATE `permissions` SET `route_name` = 'user.info.email' WHERE `slug` = 'user-email';
UPDATE `permissions` SET `route_name` = 'user.info.email-captcha' WHERE `slug` = 'user-email-captcha';

-- 4. storage route permission route_name fix
UPDATE `permissions` SET `route_name` = 'storage.files.store' WHERE `slug` = 'storage-upload';
UPDATE `permissions` SET `route_name` = 'storage.files.show' WHERE `slug` = 'storage-view';
UPDATE `permissions` SET `route_name` = 'storage.files.index' WHERE `slug` = 'storage-list';
UPDATE `permissions` SET `route_name` = 'storage.files.batch' WHERE `slug` = 'storage-batch-operate';
UPDATE `permissions` SET `route_name` = 'storage.files.cleanup' WHERE `slug` = 'storage-cleanup';
UPDATE `permissions` SET `route_name` = 'storage.files.direct' WHERE `slug` = 'storage-direct-credential';
UPDATE `permissions` SET `route_name` = 'storage.files.confirm' WHERE `slug` = 'storage-direct-confirm';
UPDATE `permissions` SET `route_name` = 'storage.channels.index' WHERE `slug` = 'admin-storage-channels';
UPDATE `permissions` SET `route_name` = 'storage.channels.test' WHERE `slug` = 'admin-test-channel';
UPDATE `permissions` SET `route_name` = 'storage.channels.stats' WHERE `slug` = 'admin-channel-stats';

-- 5. delete guest duplicate permissions
DELETE FROM `role_permissions` WHERE `permission_id` IN (SELECT `id` FROM `permissions` WHERE `slug` LIKE 'guest-%');
DELETE FROM `permissions` WHERE `slug` LIKE 'guest-%';

-- 6. delete old URL path permissions
DELETE FROM `role_permissions` WHERE `permission_id` IN (SELECT `id` FROM `permissions` WHERE `slug` IN ('upload-credentials','upload-confirm','upload-avatar','upload-card','upload-delete','upload-user-images','storage-toggle-delete','storage-toggle-public','storage-review','storage-batch-review','storage-hard-delete'));
DELETE FROM `permissions` WHERE `slug` IN ('upload-credentials','upload-confirm','upload-avatar','upload-card','upload-delete','upload-user-images','storage-toggle-delete','storage-toggle-public','storage-review','storage-batch-review','storage-hard-delete');

-- 7. guest role (role 4) assign user shared permissions
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`, `created_at`) SELECT 4, p.id, NOW() FROM `permissions` p WHERE p.`route_name` IN ('user.tags.index','user.cards.index','user.card-images.index','user.comments.index','user.likes.index','user.info.show');