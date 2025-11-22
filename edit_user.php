<?php
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$user_id = intval($_GET['id'] ?? 0);
$user = fetchOne($conn, "SELECT * FROM users WHERE id = ?", [$user_id]);

if (!$user) {
    $_SESSION['error'] = "User not found";
    header('Location: manage_users.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'student';
    $new_password = $_POST['new_password'] ?? '';
    
    $errors = [];
    
    if (strlen($name) < 2) {
        $errors[] = "Name must be at least 2 characters";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (!empty($new_password) && strlen($new_password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    

    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check_stmt->bind_param("si", $email, $user_id);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        $errors[] = "Email already exists";
    }
    
    if (empty($errors)) {
        $old_data = [
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ];
        
        $new_data = [
            'name' => $name,
            'email' => $email,
            'role' => $role
        ];
        
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $email, $role, $hashed_password, $user_id);
            $new_data['password'] = '***';
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $email, $role, $user_id);
        }
        
        if ($stmt->execute()) {

            require_once 'log_action.php';
            log_action($conn, $_SESSION['user_id'], 'UPDATE', 'users', $user_id, $old_data, $new_data);
            
            $_SESSION['success'] = "User updated successfully!";
            header('Location: manage_users.php');
            exit();
        } else {
            $error = "Failed to update user: " . $conn->error;
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
    <title>Edit User - Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <link rel="manifest" href="manifest.json">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1>College Lost & Found - Edit User</h1>
            <div class="nav-links">
                <a href="index.php">Search</a>
                <a href="report_item.php">Report Item</a>
                <a href="manage_items.php">Manage Items</a>
                <a href="notification_center.php">Notifications</a>
                <a href="admin_management.php">Admin Management</a>
                <a href="manage_users.php">Manage Users</a>
                <a href="admin.php">Dashboard</a>
                <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2>Edit User</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" class="user-form">
            <input type="text" name="name" placeholder="Full Name" required value="<?php echo htmlspecialchars($user['name']); ?>">
            <input type="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($user['email']); ?>">
            
            <select name="role" required>
                <option value="student" <?php echo $user['role'] === 'student' ? 'selected' : ''; ?>>Student</option>
                <option value="staff" <?php echo $user['role'] === 'staff' ? 'selected' : ''; ?>>Staff</option>
                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
            </select>
            
            <input type="password" name="new_password" placeholder="New Password (leave blank to keep current)">
            
            <button type="submit">Update User</button>
            <a href="manage_users.php" class="btn cancel">Cancel</a>
        </form>
    </div>

    <footer>
        <p>2025 College Lost & Found System</p>
    </footer>
</body>
</html>