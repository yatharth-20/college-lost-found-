<?php
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$status_stats = $conn->query("SELECT status, COUNT(*) as count FROM items GROUP BY status")->fetch_all(MYSQLI_ASSOC);
$category_stats = $conn->query("SELECT category, COUNT(*) as count FROM items GROUP BY category")->fetch_all(MYSQLI_ASSOC);

$claims = $conn->query("
    SELECT c.*, i.item_name, i.item_desc, i.image_path 
    FROM claims c 
    JOIN items i ON c.item_id = i.id 
    WHERE c.status = 'Pending'
    ORDER BY c.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <link rel="manifest" href="manifest.json">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1>College Lost & Found - Admin Dashboard</h1>
            <div class="nav-links">
                <a href="index.php">Search</a>
                <a href="report_item.php">Report Item</a>
                <a href="manage_items.php">My Items</a>
                <a href="notification_center.php">Notifications</a>
                <a href="admin_management.php">Admin Management</a>
                <a href="manage_users.php">Manage Users</a>
                <a href="admin.php">Dashboard</a>
                <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2>Admin Dashboard</h2>
        
        <div class="dashboard-cards">
            <div class="card">
                <h3>Items by Status</h3>
                <canvas id="statusChart" width="200" height="200"></canvas>
            </div>
            
            <div class="card">
                <h3>Items by Category</h3>
                <canvas id="categoryChart" width="200" height="200"></canvas>
            </div>
        </div>

        <div class="pending-claims">
            <h3>Pending Claims</h3>
            <?php if ($claims->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Claimer</th>
                            <th>Email</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($claim = $claims->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($claim['item_name']); ?></strong><br>
                                <small><?php echo htmlspecialchars($claim['item_desc']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($claim['claimer_name']); ?></td>
                            <td><?php echo htmlspecialchars($claim['claimer_email']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($claim['created_at'])); ?></td>
                            <td>
                                <form action="approve_claim.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="claim_id" value="<?php echo $claim['id']; ?>">
                                    <button type="submit" class="btn approve">Approve</button>
                                </form>
                                <form action="reject_claim.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="claim_id" value="<?php echo $claim['id']; ?>">
                                    <button type="submit" class="btn reject">Reject</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No pending claims.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>2025 College Lost & Found System</p>
    </footer>

    <script>
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'bar',
            data: {
                labels: [<?php echo implode(',', array_map(function($stat) { return "'" . $stat['status'] . "'"; }, $status_stats)); ?>],
                datasets: [{
                    label: 'Items by Status',
                    data: [<?php echo implode(',', array_map(function($stat) { return $stat['count']; }, $status_stats)); ?>],
                    backgroundColor: ['#ff6b6b', '#51cf66']
                }]
            }
        });

        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryChart = new Chart(categoryCtx, {
            type: 'pie',
            data: {
                labels: [<?php echo implode(',', array_map(function($stat) { return "'" . $stat['category'] . "'"; }, $category_stats)); ?>],
                datasets: [{
                    data: [<?php echo implode(',', array_map(function($stat) { return $stat['count']; }, $category_stats)); ?>],
                    backgroundColor: ['#ffd43b', '#339af0', '#cc5de8']
                }]
            }
        });
    </script>
</body>
</html>
