<?php
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$item_id = intval($_GET['id'] ?? 0);
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = trim($_POST['item_name'] ?? '');
    $item_desc = trim($_POST['item_desc'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $category = $_POST['category'] ?? 'General';
    $status = $_POST['status'] ?? 'Lost';
    
    $errors = [];
    
    if (strlen($item_name) < 2) {
        $errors[] = "Item name must be at least 2 characters";
    }
    
    if (strlen($item_desc) < 10) {
        $errors[] = "Description must be at least 10 characters";
    }
    
    if (empty($location)) {
        $errors[] = "Location is required";
    }
    
    if (empty($errors)) {
        $old_data = [
            'item_name' => $item['item_name'],
            'item_desc' => $item['item_desc'],
            'location' => $item['location'],
            'category' => $item['category'],
            'status' => $item['status']
        ];
        
        $new_data = [
            'item_name' => $item_name,
            'item_desc' => $item_desc,
            'location' => $location,
            'category' => $category,
            'status' => $status
        ];
        
        $stmt = $conn->prepare("UPDATE items SET item_name = ?, item_desc = ?, location = ?, category = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $item_name, $item_desc, $location, $category, $status, $item_id);
        
        if ($stmt->execute()) {
          
            require_once 'log_action.php';
            log_action($conn, $_SESSION['user_id'], 'UPDATE', 'items', $item_id, $old_data, $new_data);
            
            $_SESSION['success'] = "Item updated successfully!";
            header('Location: manage_items.php');
            exit();
        } else {
            $error = "Failed to update item: " . $conn->error;
        }
    } else {
        $error = implode("<br>", $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item - Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <link rel="manifest" href="manifest.json">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1>College Lost & Found</h1>
            <div class="nav-links">
                <a href="index.php">Search</a>
                <a href="report_item.php">Report Item</a>
                <a href="manage_items.php">My Items</a>
                <a href="notification_center.php">Notifications</a>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a href="admin_management.php">Admin Management</a>
                    <a href="manage_users.php">Manage Users</a>
                    <a href="admin.php">Dashboard</a>
                <?php endif; ?>
                <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2>Edit Item</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" class="item-form">
            <input type="text" name="item_name" placeholder="Item Name" required value="<?php echo htmlspecialchars($item['item_name']); ?>">
            <textarea name="item_desc" placeholder="Detailed Description" required rows="4"><?php echo htmlspecialchars($item['item_desc']); ?></textarea>
            <input type="text" name="location" placeholder="Location where lost/found" required value="<?php echo htmlspecialchars($item['location']); ?>">
            
            <select name="category" required>
                <option value="General" <?php echo $item['category'] === 'General' ? 'selected' : ''; ?>>General</option>
                <option value="Electronics" <?php echo $item['category'] === 'Electronics' ? 'selected' : ''; ?>>Electronics</option>
                <option value="Clothing" <?php echo $item['category'] === 'Clothing' ? 'selected' : ''; ?>>Clothing</option>
            </select>
            
            <select name="status" required>
                <option value="Lost" <?php echo $item['status'] === 'Lost' ? 'selected' : ''; ?>>Lost</option>
                <option value="Found" <?php echo $item['status'] === 'Found' ? 'selected' : ''; ?>>Found</option>
            </select>
            
            <button type="submit">Update Item</button>
            <a href="manage_items.php" class="btn cancel">Cancel</a>
        </form>
    </div>

    <footer>
        <p>2025 College Lost & Found System</p>
    </footer>
</body>
</html>