<?php
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_file'])) {
    $backup_file = 'backup/' . basename($_POST['backup_file']);
    
    if (file_exists($backup_file)) {

        $sql = file_get_contents($backup_file);
        $conn->multi_query($sql);
        
        $_SESSION['success'] = "Backup restored successfully!";
        header('Location: backup_management.php');
        exit();
    }
}
?>