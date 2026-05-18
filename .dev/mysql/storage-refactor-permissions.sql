-- Storage 重构：权限记录（RESTful 版本）
-- 执行日期：2026-05-18

-- 插入权限记录（如果不存在则插入）
INSERT IGNORE INTO `permissions` (`name`, `slug`, `path`, `method`, `description`, `created_at`, `updated_at`) VALUES
('代理上传文件', 'storage-upload', '/api/storage/files', 'POST', '代理上传文件', NOW(), NOW()),
('文件列表', 'storage-list', '/api/storage/files', 'GET', '获取文件列表', NOW(), NOW()),
('查看文件', 'storage-view', '/api/storage/files/{id}', 'GET', '查看单个文件', NOW(), NOW()),
('批量操作文件', 'storage-batch-operate', '/api/storage/files/batch', 'POST', '批量操作文件', NOW(), NOW()),
('清理过期文件', 'storage-cleanup', '/api/storage/files/expired', 'DELETE', '清理过期文件', NOW(), NOW()),
('获取直传凭证', 'storage-direct-credential', '/api/storage/files/direct', 'POST', '获取直传凭证', NOW(), NOW()),
('确认直传完成', 'storage-direct-confirm', '/api/storage/files/{id}/confirm', 'PATCH', '确认直传完成', NOW(), NOW()),
('获取存储渠道列表', 'admin-storage-channels', '/api/storage/channels', 'GET', '获取可用存储渠道元数据', NOW(), NOW()),
('测试存储渠道', 'admin-test-channel', '/api/storage/channels/{channel}/test', 'POST', '测试存储渠道连通性', NOW(), NOW()),
('获取渠道统计', 'admin-channel-stats', '/api/storage/channels/stats', 'GET', '获取存储渠道文件统计', NOW(), NOW());

-- 分配给超级管理员（role_id=1）和管理员（role_id=2）
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`, `created_at`)
SELECT 1, p.id, NOW() FROM permissions p WHERE p.slug IN (
    'storage-upload', 'storage-list', 'storage-view', 'storage-batch-operate',
    'storage-cleanup', 'storage-direct-credential', 'storage-direct-confirm',
    'admin-storage-channels', 'admin-test-channel', 'admin-channel-stats'
);

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`, `created_at`)
SELECT 2, p.id, NOW() FROM permissions p WHERE p.slug IN (
    'storage-upload', 'storage-list', 'storage-view', 'storage-batch-operate',
    'storage-cleanup', 'storage-direct-credential', 'storage-direct-confirm',
    'admin-storage-channels', 'admin-test-channel', 'admin-channel-stats'
);
