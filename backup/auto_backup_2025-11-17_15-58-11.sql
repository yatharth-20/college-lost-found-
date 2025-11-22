-- Auto Backup - College Lost & Found
-- Generated: 2025-11-17 15:58:11

-- Table: users
INSERT INTO users (id, name, email, password, role, reset_token, reset_expiry, created_at) VALUES ('1', 'Administrator', 'admin@college.edu', '$2y$10$r3uWpVcBjT1pYpLkQ8qZJeZ8Q9aN2mK1bF5gT7nH4vS1wX3yR6tG', 'admin', '', '', '2025-11-17 20:26:45');

-- Table: items

-- Table: claims

-- Table: audit_logs
INSERT INTO audit_logs (id, user_id, action, table_name, record_id, old_data, new_data, created_at) VALUES ('1', '1', 'AUTO_BACKUP', 'system', '0', '[]', '{\"backup_file\":\"backup\\/auto_backup_2025-11-17_15-57-46.sql\"}', '2025-11-17 20:27:46');

