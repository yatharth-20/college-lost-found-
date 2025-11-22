<?php
require_once 'db_connect.php';


$name = "Administrator";
$email = "admin@college.edu";
$password = "admin123"; 
$role = "admin";

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

if ($stmt->execute()) {
    echo "Admin user created successfully! Email: $email, Password: $password";
    echo "<br><strong>Delete this file after use!</strong>";
} else {
    echo "Error creating admin: " . $conn->error;
}
?>