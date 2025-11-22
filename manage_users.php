<?php
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$count_sql = "SELECT COUNT(*) as total FROM users";
$sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?";

$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute();
$total_users = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_users / $limit);

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$users = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <link rel="manifest" href="manifest.json">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1>College Lost & Found - Manage Users</h1>
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

        <div class="page-header">
            <h2>Manage Users</h2>
            <a href="create_user.php" class="btn">Create New User</a>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><span class="role <?php echo strtolower($user['role']); ?>"><?php echo htmlspecialchars($user['role']); ?></span></td>
                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn">Edit</a>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <form action="delete_user.php" method="POST" style="display: inline;" onsubmit="return confirm('Delete this user?');">
                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="btn delete">Delete</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>2025 College Lost & Found System</p>
    </footer>
</body>
</html>