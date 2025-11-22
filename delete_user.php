<?php
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['id'] ?? 0);
    
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error'] = "Cannot delete your own account";
        header('Location: manage_users.php');
        exit();
    }
    
    $user = fetchOne($conn, "SELECT * FROM users WHERE id = ?", [$user_id]);
    
    if (!$user) {
        $_SESSION['error'] = "User not found";
        header('Location: manage_users.php');
        exit();
    }
    

    require_once 'log_action.php';
    log_action($conn, $_SESSION['user_id'], 'DELETE', 'users', $user_id, [
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role']
    ], []);
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "User deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete user: " . $conn->error;
    }
}

header('Location: manage_users.php');
exit();