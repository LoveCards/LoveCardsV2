-- Storage RESTful 路由迁移
-- 执行日期：2026-05-18

-- 更新已有权限记录路径（旧路径 → 新路径）
UPDATE `permissions` SET `path` = '/api/storage/files', `method` = 'POST' WHERE `slug` = 'upload-file';
UPDATE `permissions` SET `path` = '/api/storage/files' WHERE `slug` = 'storage-list';
UPDATE `permissions` SET `path` = '/api/storage/files/0' WHERE `slug` = 'storage-view';
UPDATE `permissions` SET `path` = '/api/storage/files/batch' WHERE `slug` = 'storage-batch-operate';
UPDATE `permissions` SET `path` = '/api/storage/files/expired', `method` = 'DELETE' WHERE `slug` = 'storage-cleanup';

-- 更新已有直传相关权限（如果存在）
UPDATE `permissions` SET `path` = '/api/storage/files/direct' WHERE `slug` = 'upload-credentials';
UPDATE `permissions` SET `path` = '/api/storage/files/0/confirm', `method` = 'PATCH' WHERE `slug` = 'upload-confirm';

-- 更新渠道管理权限（之前插入的）
UPDATE `permissions` SET `path` = '/api/storage/channels' WHERE `slug` = 'admin-storage-channels';
UPDATE `permissions` SET `path` = '/api/storage/channels/test' WHERE `slug` = 'admin-test-channel';
UPDATE `permissions` SET `path` = '/api/storage/channels/stats' WHERE `slug` = 'admin-channel-stats';

-- 更新其他旧路径
UPDATE `permissions` SET `path` = '/api/storage/files' WHERE `path` = '/api/storage/upload';
UPDATE `permissions` SET `path` = '/api/storage/files/0' WHERE `path` = '/api/storage/get-file';
UPDATE `permissions` SET `path` = '/api/storage/files/batch' WHERE `path` = '/api/storage/batch-operate';
UPDATE `permissions` SET `path` = '/api/storage/files/direct' WHERE `path` = '/api/storage/direct-upload-credential';
UPDATE `permissions` SET `path` = '/api/storage/files/0/confirm' WHERE `path` = '/api/storage/direct-upload-confirm';
UPDATE `permissions` SET `path` = '/api/storage/files/expired' WHERE `path` = '/api/storage/cleanup';
UPDATE `permissions` SET `path` = '/api/storage/channels' WHERE `path` = '/api/storage/storage-channels';
UPDATE `permissions` SET `path` = '/api/storage/channels/test' WHERE `path` = '/api/storage/test-channel';
UPDATE `permissions` SET `path` = '/api/storage/channels/stats' WHERE `path` = '/api/storage/channel-stats';
