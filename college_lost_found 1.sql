-- College Lost & Found System - Complete Database Setup
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS college_lost_found;
CREATE DATABASE college_lost_found;
USE college_lost_found;

-- Users table
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('student','staff','admin') DEFAULT 'student',
  reset_token VARCHAR(255) DEFAULT NULL,
  reset_expiry DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Items table
CREATE TABLE items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  user_name VARCHAR(100),
  user_email VARCHAR(100),
  item_name VARCHAR(100) NOT NULL,
  item_desc TEXT,
  location VARCHAR(100),
  category VARCHAR(50) DEFAULT 'General',
  status ENUM('Lost','Found') DEFAULT 'Lost',
  image_path VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Claims table
CREATE TABLE claims (
  id INT AUTO_INCREMENT PRIMARY KEY,
  item_id INT NOT NULL,
  claimer_user_id INT NULL,
  claimer_name VARCHAR(100),
  claimer_email VARCHAR(100),
  status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
  FOREIGN KEY (claimer_user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Audit logs table
CREATE TABLE audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  action VARCHAR(100),
  table_name VARCHAR(50),
  record_id INT,
  old_data JSON,
  new_data JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create indexes
CREATE INDEX idx_items_status ON items(status);
CREATE INDEX idx_items_category ON items(category);
CREATE INDEX idx_items_created_at ON items(created_at);
CREATE INDEX idx_claims_status ON claims(status);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_audit_logs_created_at ON audit_logs(created_at);
CREATE INDEX idx_claims_item_id ON claims(item_id);
CREATE INDEX idx_items_user_id ON items(user_id);

-- Insert default admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES 
('Administrator', 'admin@college.edu', '$2y$10$r3uWpVcBjT1pYpLkQ8qZJeZ8Q9aN2mK1bF5gT7nH4vS1wX3yR6tG', 'admin');

-- Insert sample data
INSERT INTO users (name, email, password, role) VALUES 
('John Student', 'student@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('Jane Staff', 'staff@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff');

INSERT INTO items (user_id, user_name, user_email, item_name, item_desc, location, category, status) VALUES
(2, 'John Student', 'student@college.edu', 'Black Backpack', 'Nike black backpack with laptop compartment', 'Library', 'General', 'Lost'),
(3, 'Jane Staff', 'staff@college.edu', 'MacBook Pro', 'Space Gray MacBook Pro 13-inch', 'Science Building', 'Electronics', 'Found');

SET FOREIGN_KEY_CHECKS = 1;

-- Display confirmation
SELECT 'Database setup completed successfully!' as message;
SELECT COUNT(*) as users_count FROM users;
SELECT COUNT(*) as items_count FROM items;