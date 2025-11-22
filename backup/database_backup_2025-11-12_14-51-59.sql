-- College Lost & Found Database Backup
-- Generated: 2025-11-12 14:51:59

-- Table: users
INSERT INTO users (id, name, email, password, role, reset_token, reset_expiry, created_at) VALUES ('1', 'Administrator', 'admin@college.edu', '$2y$10$SHpYYRN6SrU4EPUT0ndE2.c5dHIGmMtMDsxRdsg9iecUBVDcndeWi', 'admin', '', '', '2025-11-11 01:37:03');
INSERT INTO users (id, name, email, password, role, reset_token, reset_expiry, created_at) VALUES ('2', 'yatharthverma', 'vermayath2023@gmail.com', '$2y$10$f5PZSDbgkhSlSZdZZ8iB6.JcYHOjqqjSHxtchAwo.twekqfyaAF6a', 'student', '', '', '2025-11-11 01:37:14');
INSERT INTO users (id, name, email, password, role, reset_token, reset_expiry, created_at) VALUES ('3', 'yatharthverma', 'vermasha726@gmail.com', '$2y$10$MEZN6YRc94U7v5PMRFiODOb393Zyoe66MQTQKprN6KGpKhiQhcRI.', 'staff', '', '', '2025-11-11 01:39:00');
INSERT INTO users (id, name, email, password, role, reset_token, reset_expiry, created_at) VALUES ('5', 'yatharth verma', 'vyatharth43@gmail.com', '$2y$10$nttbdZv0i8UOGpRpooRiS.2wT4HuhWnt4tVKGxzPl6l6l43x1ZnLW', 'student', '', '', '2025-11-11 16:31:28');
INSERT INTO users (id, name, email, password, role, reset_token, reset_expiry, created_at) VALUES ('6', 'yatharthverma', '230211178@geu.ac.in', '$2y$10$9ow60l.XjpHCHR133pPUgOOUovBHmH2AAykAWjzuNL5GgyggDfp5m', 'staff', '', '', '2025-11-12 18:21:43');

-- Table: items
INSERT INTO items (id, user_id, user_name, user_email, item_name, item_desc, location, category, status, image_path, created_at) VALUES ('3', '2', 'yatharthverma', 'vermayath2023@gmail.com', 'phone', 'samsung galaxy a 14 pink colour body', 'csit lab 4', 'Electronics', 'Lost', 'uploads/items/1762858839_691317578b63e.jpg', '2025-11-11 16:30:39');
INSERT INTO items (id, user_id, user_name, user_email, item_name, item_desc, location, category, status, image_path, created_at) VALUES ('4', '5', 'yatharth verma', 'vyatharth43@gmail.com', 'phone', 'samsung galaxy a 14 pink colour body', 'csit lab 4', 'Electronics', 'Found', 'uploads/items/1762858984_691317e82aebf.jpg', '2025-11-11 16:33:04');
INSERT INTO items (id, user_id, user_name, user_email, item_name, item_desc, location, category, status, image_path, created_at) VALUES ('5', '2', 'yatharthverma', 'vermayath2023@gmail.com', 'phone', 'i phone 17 pro max orange color body', 'csit lab 4', 'Electronics', 'Lost', 'uploads/items/1762944378_6914657ae736a.jpg', '2025-11-12 16:16:18');

-- Table: claims
INSERT INTO claims (id, item_id, claimer_user_id, claimer_name, claimer_email, status, created_at) VALUES ('5', '4', '2', 'yatharthverma', 'vermayath2023@gmail.com', 'Approved', '2025-11-11 16:34:47');
INSERT INTO claims (id, item_id, claimer_user_id, claimer_name, claimer_email, status, created_at) VALUES ('6', '4', '2', 'yatharthverma', 'vermayath2023@gmail.com', 'Approved', '2025-11-12 16:17:24');
INSERT INTO claims (id, item_id, claimer_user_id, claimer_name, claimer_email, status, created_at) VALUES ('7', '4', '2', 'yatharthverma', 'vermayath2023@gmail.com', 'Pending', '2025-11-12 19:19:28');

-- Table: audit_logs
INSERT INTO audit_logs (id, user_id, action, table_name, record_id, old_data, new_data, created_at) VALUES ('1', '2', 'DELETE', 'items', '2', '{\"item_name\":\"phone\",\"user_id\":2}', '[]', '2025-11-11 16:28:34');
INSERT INTO audit_logs (id, user_id, action, table_name, record_id, old_data, new_data, created_at) VALUES ('2', '2', 'DELETE', 'items', '1', '{\"item_name\":\"phone\",\"user_id\":2}', '[]', '2025-11-11 16:28:36');
INSERT INTO audit_logs (id, user_id, action, table_name, record_id, old_data, new_data, created_at) VALUES ('3', '1', 'APPROVE', 'claims', '6', '{\"status\":\"Pending\"}', '{\"status\":\"Approved\"}', '2025-11-12 18:27:53');
INSERT INTO audit_logs (id, user_id, action, table_name, record_id, old_data, new_data, created_at) VALUES ('4', '1', 'APPROVE', 'claims', '5', '{\"status\":\"Pending\"}', '{\"status\":\"Approved\"}', '2025-11-12 18:27:58');
INSERT INTO audit_logs (id, user_id, action, table_name, record_id, old_data, new_data, created_at) VALUES ('5', '1', 'UPDATE', 'items', '4', '{\"item_name\":\"phone\",\"item_desc\":\"samsung galaxy a 14 pink colour body\",\"location\":\"csit lab 4\",\"category\":\"Electronics\",\"status\":\"Found\"}', '{\"item_name\":\"phone\",\"item_desc\":\"samsung galaxy a 14 pink colour body\",\"location\":\"csit lab 4\",\"category\":\"Electronics\",\"status\":\"Found\"}', '2025-11-12 19:18:34');

