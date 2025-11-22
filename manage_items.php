<?php
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

if ($_SESSION['user_role'] === 'admin') {
    $count_sql = "SELECT COUNT(*) as total FROM items";
    $sql = "SELECT * FROM items ORDER BY created_at DESC LIMIT ? OFFSET ?";
} else {
    $count_sql = "SELECT COUNT(*) as total FROM items WHERE user_id = ?";
    $sql = "SELECT * FROM items WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
}

$count_stmt = $conn->prepare($count_sql);
if ($_SESSION['user_role'] === 'admin') {
    $count_stmt->execute();
} else {
    $count_stmt->bind_param("i", $_SESSION['user_id']);
    $count_stmt->execute();
}
$total_items = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_items / $limit);

$stmt = $conn->prepare($sql);
if ($_SESSION['user_role'] === 'admin') {
    $stmt->bind_param("ii", $limit, $offset);
} else {
    $stmt->bind_param("iii", $_SESSION['user_id'], $limit, $offset);
}
$stmt->execute();
$items = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Items - Lost & Found</title>
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
        <h2><?php echo $_SESSION['user_role'] === 'admin' ? 'All Items' : 'My Reported Items'; ?></h2>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if ($items->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Status</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $items->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
                            <?php if ($item['image_path']): ?>
                                <br><img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="" style="max-width: 50px; height: auto;">
                            <?php endif; ?>
                        </td>
                        <td><span class="status <?php echo strtolower($item['status']); ?>"><?php echo htmlspecialchars($item['status']); ?></span></td>
                        <td><?php echo htmlspecialchars($item['category']); ?></td>
                        <td><?php echo htmlspecialchars($item['location']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($item['created_at'])); ?></td>
                        <td>
                            <a href="edit_item.php?id=<?php echo $item['id']; ?>" class="btn">Edit</a>
                            <form action="delete_item.php" method="POST" style="display: inline;" onsubmit="return confirm('Delete this item?');">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn delete">Delete</button>
                            </form>
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
        <?php else: ?>
            <p>No items found.</p>
        <?php endif; ?>
    </div>

    <footer>
        <p>2025 College Lost & Found System</p>
    </footer>
</body>
</html>