<?php
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Clean items older than 1 year
$one_year_ago = date('Y-m-d H:i:s', strtotime('-1 year'));

// Get items to be deleted for logging
$old_items = $conn->query("SELECT * FROM items WHERE created_at < '$one_year_ago'");
$deleted_count = 0;

while ($item = $old_items->fetch_assoc()) {
    // Log before deletion
    require_once 'log_action.php';
    log_action($conn, $_SESSION['user_id'], 'DELETE', 'items', $item['id'], [
        'item_name' => $item['item_name'],
        'user_id' => $item['user_id']
    ], []);
    
    // Delete associated image
    if ($item['image_path'] && file_exists($item['image_path'])) {
        unlink($item['image_path']);
    }
    
    $deleted_count++;
}

// Delete the items
$conn->query("DELETE FROM items WHERE created_at < '$one_year_ago'");

$_SESSION['success'] = "Cleaned $deleted_count items older than 1 year";
header('Location: admin_management.php');
exit();
?>