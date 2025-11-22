<?php
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}


$backup_file = 'backup/database_backup_' . date('Y-m-d_H-i-s') . '.sql';


if (!is_dir('backup')) {
    mkdir('backup', 0755, true);
}


$tables = ['users', 'items', 'claims', 'audit_logs'];
$backup_content = "-- College Lost & Found Database Backup\n";
$backup_content .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";

foreach ($tables as $table) {
    $result = $conn->query("SELECT * FROM $table");
    $backup_content .= "-- Table: $table\n";
    
    while ($row = $result->fetch_assoc()) {
        $columns = implode(', ', array_keys($row));
        $values = implode("', '", array_map(function($value) use ($conn) {
            return $conn->real_escape_string($value);
        }, array_values($row)));
        
        $backup_content .= "INSERT INTO $table ($columns) VALUES ('$values');\n";
    }
    $backup_content .= "\n";
}

file_put_contents($backup_file, $backup_content);


require_once 'log_action.php';
log_action($conn, $_SESSION['user_id'], 'BACKUP', 'system', 0, [], ['backup_file' => $backup_file]);

$_SESSION['success'] = "Database backup created successfully: " . basename($backup_file);
header('Location: admin_management.php');
exit();
?>