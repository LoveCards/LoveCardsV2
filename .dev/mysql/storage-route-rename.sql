-- Storage 路由迁移：/api/upload → /api/storage
-- 执行日期：2026-05-18

-- 更新已有权限记录的路径
UPDATE `permissions` SET `path` = REPLACE(`path`, '/api/upload/', '/api/storage/') WHERE `path` LIKE '/api/upload/%';
