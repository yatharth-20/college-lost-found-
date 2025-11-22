<?php
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = intval($_POST['id'] ?? 0);
    $item = fetchOne($conn, "SELECT * FROM items WHERE id = ?", [$item_id]);
    
    if (!$item) {
        $_SESSION['error'] = "Item not found";
        header('Location: manage_items.php');
        exit();
    }
    

    if ($_SESSION['user_role'] !== 'admin' && $item['user_id'] != $_SESSION['user_id']) {
        $_SESSION['error'] = "Access denied";
        header('Location: manage_items.php');
        exit();
    }
    

    require_once 'log_action.php';
    log_action($conn, $_SESSION['user_id'], 'DELETE', 'items', $item_id, [
        'item_name' => $item['item_name'],
        'user_id' => $item['user_id']
    ], []);
    
    $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    
    if ($stmt->execute()) {

        if ($item['image_path'] && file_exists($item['image_path'])) {
            unlink($item['image_path']);
        }
        
        $_SESSION['success'] = "Item deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete item: " . $conn->error;
    }
}

header('Location: manage_items.php');
exit();