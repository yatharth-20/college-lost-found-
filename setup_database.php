<?php


$host = 'localhost';
$username = 'root';
$password = '';


$conn = new mysqli($host, $username, $password);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sql = "CREATE DATABASE IF NOT EXISTS college_lost_found";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}


$conn->select_db("college_lost_found");


$tables_sql = [
    "DROP TABLE IF EXISTS audit_logs, claims, items, users",
    
    "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('student','staff','admin') DEFAULT 'student',
        reset_token VARCHAR(255) DEFAULT NULL,
        reset_expiry DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE items (
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
    )",
    
    "CREATE TABLE claims (
        id INT AUTO_INCREMENT PRIMARY KEY,
        item_id INT NOT NULL,
        claimer_user_id INT NULL,
        claimer_name VARCHAR(100),
        claimer_email VARCHAR(100),
        status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
        FOREIGN KEY (claimer_user_id) REFERENCES users(id) ON DELETE SET NULL
    )",
    
    "CREATE TABLE audit_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        action VARCHAR(100),
        table_name VARCHAR(50),
        record_id INT,
        old_data JSON,
        new_data JSON,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )",
    
    "CREATE INDEX idx_items_status ON items(status)",
    "CREATE INDEX idx_items_category ON items(category)",
    "CREATE INDEX idx_items_created_at ON items(created_at)",
    "CREATE INDEX idx_claims_status ON claims(status)",
    "CREATE INDEX idx_users_email ON users(email)",
    
    "INSERT INTO users (name, email, password, role) VALUES 
    ('Administrator', 'admin@college.edu', '\$2y\$10\$r3uWpVcBjT1pYpLkQ8qZJeZ8Q9aN2mK1bF5gT7nH4vS1wX3yR6tG', 'admin')"
];


foreach ($tables_sql as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Query executed successfully: " . substr($sql, 0, 50) . "...<br>";
    } else {
        echo "Error: " . $conn->error . "<br>";
    }
}

echo "<h2>Database setup complete!</h2>";
echo "<p>You can now <a href='login.php'>login</a> with:</p>";
echo "<ul>";
echo "<li>Email: admin@college.edu</li>";
echo "<li>Password: admin123</li>";
echo "</ul>";
echo "<p><strong>Delete this file after use for security!</strong></p>";

$conn->close();
?>