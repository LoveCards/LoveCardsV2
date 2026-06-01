CREATE TABLE IF NOT EXISTS role_capabilities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    capability VARCHAR(100) NOT NULL,
    UNIQUE KEY uk_role_cap (role_id, capability),
    KEY idx_role_id (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
