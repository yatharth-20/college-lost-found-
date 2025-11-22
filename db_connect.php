<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: text/html; charset=utf-8');

$host = 'localhost';
$username = 'root';
$password = 'WJ28@krhps';
$database = 'college_lost_found';
$port = 3307;

$conn = new mysqli($host, $username, $password, $database, $port);


if ($conn->connect_error) {
    die("MySQL Connection failed: " . $conn->connect_error);
}


$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) === FALSE) {
    die("Error creating database: " . $conn->error);
}


if (!$conn->select_db($database)) {
    die("Cannot select database: " . $conn->error);
}

/**
 * Safely creates an index only if it does not already exist.
 * @param mysqli $conn The database connection object.
 * @param string $tableName The table name.
 * @param string $indexName The name of the index.
 * @param string $columns The column(s) to index, in format (column1, column2).
 */
function createIndexIfNotExists($conn, $tableName, $indexName, $columns) {
   
    $result = $conn->query("SHOW INDEX FROM `$tableName` WHERE Key_name = '$indexName'");
    if ($result && $result->num_rows == 0) {
        $sql = "CREATE INDEX `$indexName` ON `$tableName` $columns";
        if (!$conn->query($sql)) {
            error_log("Index creation error: " . $conn->error);
        }
    }
}

function createTables($conn) {
    $tables_sql = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('student','staff','admin') DEFAULT 'student',
            reset_token VARCHAR(255) DEFAULT NULL,
            reset_expiry DATETIME DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
        
        "CREATE TABLE IF NOT EXISTS items (
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
        ) ENGINE=InnoDB",
        
        "CREATE TABLE IF NOT EXISTS claims (
            id INT AUTO_INCREMENT PRIMARY KEY,
            item_id INT NOT NULL,
            claimer_user_id INT NULL,
            claimer_name VARCHAR(100),
            claimer_email VARCHAR(100),
            status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
            FOREIGN KEY (claimer_user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB",
        
        "CREATE TABLE IF NOT EXISTS audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            action VARCHAR(100),
            table_name VARCHAR(50),
            record_id INT,
            old_data JSON,
            new_data JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        ) ENGINE=InnoDB"
    ];

    foreach ($tables_sql as $sql) {
        if (!$conn->query($sql)) {
            error_log("Table creation error: " . $conn->error);
        }
    }

    
    createIndexIfNotExists($conn, 'items', 'idx_items_status', '(status)');
    createIndexIfNotExists($conn, 'items', 'idx_items_category', '(category)');
    createIndexIfNotExists($conn, 'items', 'idx_items_created_at', '(created_at)');
    createIndexIfNotExists($conn, 'claims', 'idx_claims_status', '(status)');
    createIndexIfNotExists($conn, 'users', 'idx_users_email', '(email)');
    



    $result = $conn->query("SELECT id FROM users WHERE email = 'admin@college.edu'");
    if ($result->num_rows == 0) {
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("INSERT INTO users (name, email, password, role) VALUES ('Administrator', 'admin@college.edu', '$hashed_password', 'admin')");
    }
}


createTables($conn);

function fetchOne($conn, $sql, $params = []) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }
    
    if ($params) {
      
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_double($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
        }
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        return false;
    }
    
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
?>