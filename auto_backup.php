<?php

require_once 'db_connect.php';

function createBackup($conn) {
    $backup_dir = 'backup/';
    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }
    
    $backup_file = $backup_dir . 'auto_backup_' . date('Y-m-d_H-i-s') . '.sql';
    $tables = ['users', 'items', 'claims', 'audit_logs'];
    
    $backup_content = "-- Auto Backup - College Lost & Found\n";
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
    log_action($conn, 1, 'AUTO_BACKUP', 'system', 0, [], ['backup_file' => $backup_file]);
    
    return $backup_file;
}


$backup_file = createBackup($conn);
echo "Backup created: " . basename($backup_file);
?>