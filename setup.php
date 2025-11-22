<?php

session_start();

$host = 'localhost';
$username = 'root';
$password = '';


$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    die("MySQL Connection failed: " . $conn->connect_error);
}


$database = 'college_lost_found';
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}


$conn->select_db($database);


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
    )",
    
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
    )",
    
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
    )",
    
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
    )"
];

foreach ($tables_sql as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Table created successfully<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }
}


$hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
$admin_sql = "INSERT IGNORE INTO users (name, email, password, role) VALUES ('Administrator', 'admin@college.edu', '$hashed_password', 'admin')";
if ($conn->query($admin_sql) === TRUE) {
    echo "Admin user created successfully<br>";
} else {
    echo "Error creating admin user: " . $conn->error . "<br>";
}


if (!is_dir('uploads')) {
    mkdir('uploads', 0755, true);
    mkdir('uploads/items', 0755, true);
    echo "Uploads directory created<br>";
}

echo "<h2>Setup Complete!</h2>";
echo "<p>You can now <a href='login.php'>login</a> with:</p>";
echo "<ul>";
echo "<li>Email: admin@college.edu</li>";
echo "<li>Password: admin123</li>";
echo "</ul>";
echo "<p><strong>Enhanced features included:</strong></p>";
echo "<ul>";
echo "<li>Smart notification system</li>";
echo "<li>Potential match detection</li>";
echo "<li>Notification center</li>";
echo "<li>Email alerts for matches</li>";
echo "</ul>";
echo "<p><strong>College Lost & Found System 2025</strong></p>";
echo "<p><strong>Delete this file after use for security!</strong></p>";

$conn->close();
?>