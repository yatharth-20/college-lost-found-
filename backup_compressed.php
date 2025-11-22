<?php
require_once 'db_connect.php';

function createCompressedBackup($conn) {
    $backup_dir = 'backup/';
    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }
    
    $backup_file = $backup_dir . 'backup_' . date('Y-m-d') . '.sql';
    $compressed_file = $backup_dir . 'backup_' . date('Y-m-d') . '.zip';
    

    $tables = ['users', 'items', 'claims', 'audit_logs'];
    $backup_content = "";
    
    foreach ($tables as $table) {

        $result = $conn->query("SHOW CREATE TABLE $table");
        $row = $result->fetch_assoc();
        $backup_content .= $row['Create Table'] . ";\n\n";
        

        $data_result = $conn->query("SELECT * FROM $table");
        while ($data_row = $data_result->fetch_assoc()) {
            $columns = implode(', ', array_keys($data_row));
            $values = implode("', '", array_map(function($value) use ($conn) {
                return $conn->real_escape_string($value);
            }, array_values($data_row)));
            
            $backup_content .= "INSERT INTO $table ($columns) VALUES ('$values');\n";
        }
        $backup_content .= "\n";
    }
    
    file_put_contents($backup_file, $backup_content);
    

    $zip = new ZipArchive();
    if ($zip->open($compressed_file, ZipArchive::CREATE) === TRUE) {
        $zip->addFile($backup_file, basename($backup_file));
        $zip->close();
        unlink($backup_file); 
        return $compressed_file;
    }
    
    return $backup_file;
}


if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
    $backup_file = createCompressedBackup($conn);
    $_SESSION['success'] = "Backup created: " . basename($backup_file);
    header('Location: admin_management.php');
}
?>