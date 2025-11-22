<?php
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
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
    
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; 
        
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $errors[] = "Only JPG, PNG, and GIF images are allowed";
        } elseif ($_FILES['image']['size'] > $max_size) {
            $errors[] = "Image size must be less than 5MB";
        } else {
            $upload_dir = 'uploads/items/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $image_path = $upload_dir . $filename;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                $errors[] = "Failed to upload image";
                $image_path = null;
            }
        }
    }
    
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO items (user_id, user_name, user_email, item_name, item_desc, location, category, status, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssss", $_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $item_name, $item_desc, $location, $category, $status, $image_path);
        
        if ($stmt->execute()) {
            $new_item_id = $conn->insert_id;
            

            require_once 'notifications.php';
            $notified_users = send_item_notification($conn, $new_item_id, 'new_report');
            

            $admin_email = "admin@college.edu";
            $subject = "New Item Reported - " . htmlspecialchars($item_name);
            $message = "A new item has been reported:\n\n" .
                      "Item: " . htmlspecialchars($item_name) . "\n" .
                      "Description: " . htmlspecialchars($item_desc) . "\n" .
                      "Location: " . htmlspecialchars($location) . "\n" .
                      "Status: " . htmlspecialchars($status) . "\n" .
                      "Reported by: " . htmlspecialchars($_SESSION['user_name']) . " (" . htmlspecialchars($_SESSION['user_email']) . ")\n" .
                      "Potential matches notified: " . $notified_users . " users";
            
            @mail($admin_email, $subject, $message);
            
            $_SESSION['success'] = "Item reported successfully! " . $notified_users . " users were notified of potential matches.";
            header('Location: index.php');
            exit();
        } else {
            $error = "Failed to report item: " . $conn->error;
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
    <title>Report Item - Lost & Found</title>
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
                <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2>Report Lost or Found Item</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="item-form">
            <input type="text" name="item_name" placeholder="Item Name" required value="<?php echo htmlspecialchars($_POST['item_name'] ?? ''); ?>">
            <textarea name="item_desc" placeholder="Detailed Description" required rows="4"><?php echo htmlspecialchars($_POST['item_desc'] ?? ''); ?></textarea>
            <input type="text" name="location" placeholder="Location where lost/found" required value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>">
            
            <select name="category" required>
                <option value="General" <?php echo ($_POST['category'] ?? '') === 'General' ? 'selected' : ''; ?>>General</option>
                <option value="Electronics" <?php echo ($_POST['category'] ?? '') === 'Electronics' ? 'selected' : ''; ?>>Electronics</option>
                <option value="Clothing" <?php echo ($_POST['category'] ?? '') === 'Clothing' ? 'selected' : ''; ?>>Clothing</option>
            </select>
            
            <select name="status" required>
                <option value="Lost" <?php echo ($_POST['status'] ?? '') === 'Lost' ? 'selected' : ''; ?>>Lost</option>
                <option value="Found" <?php echo ($_POST['status'] ?? '') === 'Found' ? 'selected' : ''; ?>>Found</option>
            </select>
            
            <label for="image">Item Image (Optional):</label>
            <input type="file" name="image" accept="image/*">
            
            <button type="submit">Report Item</button>
        </form>
    </div>
</body>
</html>