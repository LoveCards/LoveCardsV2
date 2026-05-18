-- RBAC Hash Migration
-- Date: 2026-05-18

-- 1. Drop FK
ALTER TABLE `role_permissions` DROP FOREIGN KEY `fk_role_permissions_permission`;

-- 2. Create new table
CREATE TABLE `role_permissions_new` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_id` INT NOT NULL,
  `permission_hash` VARCHAR(32) NOT NULL,
  `created_at` TIMESTAMP NULL,
  UNIQUE KEY `uk_role_hash` (`role_id`, `permission_hash`),
  KEY `idx_role_id` (`role_id`),
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Migrate data
INSERT INTO `role_permissions_new` (`role_id`, `permission_hash`, `created_at`)
SELECT `rp`.`role_id`, MD5(CONCAT(`p`.`route_name`, ':', `p`.`method`)), `rp`.`created_at`
FROM `role_permissions` `rp`
JOIN `permissions` `p` ON `rp`.`permission_id` = `p`.`id`
WHERE `p`.`deleted_at` IS NULL;

-- 4. Replace
RENAME TABLE `role_permissions` TO `role_permissions_old`, `role_permissions_new` TO `role_permissions`;
DROP TABLE `role_permissions_old`;
DROP TABLE `permissions`;