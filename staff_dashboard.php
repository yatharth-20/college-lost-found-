<?php
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'staff') {
    header('Location: login.php');
    exit();
}

$recent_items = $conn->query("SELECT * FROM items ORDER BY created_at DESC LIMIT 5");
$pending_claims = $conn->query("SELECT COUNT(*) as count FROM claims WHERE status = 'Pending'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <link rel="manifest" href="manifest.json">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1>College Lost & Found - Staff Dashboard</h1>
            <div class="nav-links">
                <a href="index.php">Search</a>
                <a href="report_item.php">Report Item</a>
                <a href="manage_items.php">My Items</a>
                <a href="notification_center.php">Notifications</a>
                <a href="staff_dashboard.php">Staff Dashboard</a>
                <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
        
        <div class="dashboard-cards">
            <div class="card">
                <h3>Quick Actions</h3>
                <div class="quick-links">
                    <a href="report_item.php" class="btn">Report New Item</a>
                    <a href="index.php" class="btn">Search Items</a>
                    <a href="manage_items.php" class="btn">Manage My Items</a>
                    <a href="notification_center.php" class="btn">View Notifications</a>
                </div>
            </div>
            
            <div class="card">
                <h3>Pending Claims</h3>
                <p class="stat"><?php echo $pending_claims; ?></p>
                <a href="admin.php" class="btn">Review Claims</a>
            </div>
        </div>

        <div class="recent-items">
            <h3>Recently Reported Items</h3>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Status</th>
                        <th>Location</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $recent_items->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                        <td><span class="status <?php echo strtolower($item['status']); ?>"><?php echo htmlspecialchars($item['status']); ?></span></td>
                        <td><?php echo htmlspecialchars($item['location']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($item['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer>
        <p> 2025 College Lost & Found System</p>
    </footer>
</body>
</html>