<?php
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}


$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'];


$my_items = $conn->query("SELECT * FROM items WHERE user_id = $user_id ORDER BY created_at DESC");


$potential_matches = $conn->query("
    SELECT f.*, 'found_match' as match_type
    FROM items l
    JOIN items f ON (l.category = f.category OR l.location LIKE CONCAT('%', f.location, '%'))
    WHERE l.user_id = $user_id 
    AND l.status = 'Lost' 
    AND f.status = 'Found'
    AND f.created_at > l.created_at
    UNION
    SELECT l.*, 'lost_match' as match_type
    FROM items f
    JOIN items l ON (f.category = l.category OR f.location LIKE CONCAT('%', l.location, '%'))
    WHERE f.user_id = $user_id 
    AND f.status = 'Found' 
    AND l.status = 'Lost'
    AND l.created_at > f.created_at
    ORDER BY created_at DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <link rel="manifest" href="manifest.json">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1>College Lost & Found - Notifications</h1>
            <div class="nav-links">
                <a href="index.php">Search</a>
                <a href="report_item.php">Report Item</a>
                <a href="manage_items.php">My Items</a>
                <a href="notification_center.php">Notifications</a>
                <?php if ($_SESSION['user_role'] === 'staff'): ?>
                    <a href="staff_dashboard.php">Staff Dashboard</a>
                <?php endif; ?>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a href="admin_management.php">Admin Management</a>
                    <a href="admin.php">Dashboard</a>
                <?php endif; ?>
                <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2>Your Notifications & Matches</h2>
        
        <div class="notification-section">
            <h3>🔔 Potential Matches</h3>
            <?php if ($potential_matches->num_rows > 0): ?>
                <div class="items-grid">
                    <?php while ($match = $potential_matches->fetch_assoc()): ?>
                        <div class="item-card match-notification">
                            <div class="match-badge">
                                <?php echo $match['match_type'] == 'found_match' ? '✅ Possible Found Match' : '🔍 Possible Lost Match'; ?>
                            </div>
                            <h3><?php echo htmlspecialchars($match['item_name']); ?></h3>
                            <p class="item-desc"><?php echo htmlspecialchars($match['item_desc']); ?></p>
                            <div class="item-meta">
                                <span class="status <?php echo strtolower($match['status']); ?>">
                                    <?php echo htmlspecialchars($match['status']); ?>
                                </span>
                                <span class="category"><?php echo htmlspecialchars($match['category']); ?></span>
                                <span class="location">📍 <?php echo htmlspecialchars($match['location']); ?></span>
                                <span class="date"><?php echo date('M j, Y', strtotime($match['created_at'])); ?></span>
                            </div>
                            <?php if ($match['match_type'] == 'found_match' && $_SESSION['user_role'] !== 'admin'): ?>
                                <a href="claim_item.php?item_id=<?php echo $match['id']; ?>" class="btn">Claim This Item</a>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No potential matches found at this time. Check back later!</p>
            <?php endif; ?>
        </div>

        <div class="notification-section">
            <h3>📋 Your Reported Items</h3>
            <?php if ($my_items->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $my_items->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
                                <?php if ($item['image_path']): ?>
                                    <br><img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="" style="max-width: 50px; height: auto;">
                                <?php endif; ?>
                            </td>
                            <td><span class="status <?php echo strtolower($item['status']); ?>">
                                <?php echo htmlspecialchars($item['status']); ?>
                            </span></td>
                            <td><?php echo date('M j, Y', strtotime($item['created_at'])); ?></td>
                            <td>
                                <a href="edit_item.php?id=<?php echo $item['id']; ?>" class="btn">Edit</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>You haven't reported any items yet. <a href="report_item.php">Report your first item</a>!</p>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>2025 College Lost & Found System</p>
    </footer>
</body>
</html>