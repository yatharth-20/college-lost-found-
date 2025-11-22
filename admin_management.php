<?php
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$stats = [
    'total_users' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'total_items' => $conn->query("SELECT COUNT(*) as count FROM items")->fetch_assoc()['count'],
    'lost_items' => $conn->query("SELECT COUNT(*) as count FROM items WHERE status = 'Lost'")->fetch_assoc()['count'],
    'found_items' => $conn->query("SELECT COUNT(*) as count FROM items WHERE status = 'Found'")->fetch_assoc()['count'],
    'pending_claims' => $conn->query("SELECT COUNT(*) as count FROM claims WHERE status = 'Pending'")->fetch_assoc()['count'],
    'approved_claims' => $conn->query("SELECT COUNT(*) as count FROM claims WHERE status = 'Approved'")->fetch_assoc()['count'],
    'staff_count' => $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'staff'")->fetch_assoc()['count'],
    'student_count' => $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'")->fetch_assoc()['count']
];

$recent_activity = $conn->query("
    SELECT 'item' as type, item_name as title, user_name as actor, created_at 
    FROM items 
    UNION ALL 
    SELECT 'claim' as type, CONCAT('Claim for item #', item_id) as title, claimer_name as actor, created_at 
    FROM claims 
    UNION ALL
    SELECT 'user' as type, CONCAT('User registered: ', name) as title, name as actor, created_at
    FROM users
    ORDER BY created_at DESC 
    LIMIT 10
");

$categories = $conn->query("SELECT category, COUNT(*) as count FROM items GROUP BY category");

$statuses = $conn->query("SELECT status, COUNT(*) as count FROM items GROUP BY status");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Management - Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <link rel="manifest" href="manifest.json">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1>College Lost & Found - Admin Management</h1>
            <div class="nav-links">
                <a href="index.php">Search</a>
                <a href="admin_management.php" class="active">Admin Management</a>
                <a href="admin.php">Dashboard</a>
                <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2>📊 Admin Management Console</h2>

        <div class="dashboard-cards">
            <div class="card stat-card">
                <h3>👥 Users</h3>
                <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                <div class="stat-detail">
                    <span class="student-count">Students: <?php echo $stats['student_count']; ?></span>
                    <span class="staff-count">Staff: <?php echo $stats['staff_count']; ?></span>
                </div>
            </div>
            
            <div class="card stat-card">
                <h3>📦 Items</h3>
                <div class="stat-number"><?php echo $stats['total_items']; ?></div>
                <div class="stat-detail">
                    <span class="lost-count">Lost: <?php echo $stats['lost_items']; ?></span>
                    <span class="found-count">Found: <?php echo $stats['found_items']; ?></span>
                </div>
            </div>
            
            <div class="card stat-card">
                <h3>🏷️ Claims</h3>
                <div class="stat-number"><?php echo $stats['pending_claims'] + $stats['approved_claims']; ?></div>
                <div class="stat-detail">
                    <span class="pending-count">Pending: <?php echo $stats['pending_claims']; ?></span>
                    <span class="approved-count">Approved: <?php echo $stats['approved_claims']; ?></span>
                </div>
            </div>
        </div>

        <div class="quick-actions-section">
            <h3>⚡ Quick Actions</h3>
            <div class="quick-actions-grid">
                <a href="manage_users.php" class="action-card">
                    <div class="action-icon">👥</div>
                    <div class="action-text">
                        <h4>Manage Users</h4>
                        <p>View, edit, and manage all users</p>
                    </div>
                </a>
                
                <a href="manage_items.php" class="action-card">
                    <div class="action-icon">📦</div>
                    <div class="action-text">
                        <h4>Manage Items</h4>
                        <p>View and manage all lost/found items</p>
                    </div>
                </a>
                
                <a href="admin.php" class="action-card">
                    <div class="action-icon">🏷️</div>
                    <div class="action-text">
                        <h4>Manage Claims</h4>
                        <p>Approve or reject item claims</p>
                    </div>
                </a>
                
                <a href="notification_center.php" class="action-card">
                    <div class="action-icon">🔔</div>
                    <div class="action-text">
                        <h4>Notifications</h4>
                        <p>View system notifications</p>
                    </div>
                </a>
            </div>
        </div>

        <div class="charts-section">
            <div class="chart-card">
                <h3>Items by Category</h3>
                <canvas id="categoryChart" width="400" height="200"></canvas>
            </div>
            
            <div class="chart-card">
                <h3>Items by Status</h3>
                <canvas id="statusChart" width="400" height="200"></canvas>
            </div>
        </div>

        <div class="recent-activity">
            <h3>📈 Recent Activity</h3>
            <div class="activity-list">
                <?php while ($activity = $recent_activity->fetch_assoc()): ?>
                <div class="activity-item">
                    <div class="activity-icon">
                        <?php 
                        switch($activity['type']) {
                            case 'item': echo '📦'; break;
                            case 'claim': echo '🏷️'; break;
                            case 'user': echo '👤'; break;
                            default: echo '📝';
                        }
                        ?>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title"><?php echo htmlspecialchars($activity['title']); ?></div>
                        <div class="activity-meta">
                            <span class="activity-actor">By: <?php echo htmlspecialchars($activity['actor']); ?></span>
                            <span class="activity-time"><?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="system-tools">
            <h3>🔧 System Tools</h3>
            <div class="tools-grid">
                <div class="tool-card">
                    <h4>Database Backup</h4>
                    <p>Export database for backup</p>
                    <form action="backup_database.php" method="POST">
                        <button type="submit" class="btn">Backup Now</button>
                    </form>
                </div>
                
                <div class="tool-card">
                    <h4>Clean Old Data</h4>
                    <p>Remove items older than 1 year</p>
                    <form action="clean_old_data.php" method="POST" onsubmit="return confirm('Clean items older than 1 year?')">
                        <button type="submit" class="btn">Clean Now</button>
                    </form>
                </div>
                
                <div class="tool-card">
                    <h4>System Report</h4>
                    <p>Generate system usage report</p>
                    <form action="generate_report.php" method="POST">
                        <button type="submit" class="btn">Generate Report</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p> 2025 College Lost & Found System - Admin Console</p>
    </footer>

    <script>
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: [<?php 
                    $categories_data = [];
                    while ($cat = $categories->fetch_assoc()) {
                        $categories_data[] = "'" . $cat['category'] . "'";
                    }
                    echo implode(',', $categories_data);
                ?>],
                datasets: [{
                    data: [<?php 
                    $categories->data_seek(0);
                    $counts = [];
                    while ($cat = $categories->fetch_assoc()) {
                        $counts[] = $cat['count'];
                    }
                    echo implode(',', $counts);
                    ?>],
                    backgroundColor: ['#ff6b6b', '#4ecdc4', '#45b7d1', '#96ceb4', '#feca57']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'bar',
            data: {
                labels: [<?php 
                    $status_data = [];
                    while ($stat = $statuses->fetch_assoc()) {
                        $status_data[] = "'" . $stat['status'] . "'";
                    }
                    echo implode(',', $status_data);
                ?>],
                datasets: [{
                    label: 'Items',
                    data: [<?php 
                    $statuses->data_seek(0);
                    $status_counts = [];
                    while ($stat = $statuses->fetch_assoc()) {
                        $status_counts[] = $stat['count'];
                    }
                    echo implode(',', $status_counts);
                    ?>],
                    backgroundColor: ['#ff9ff3', '#54a0ff']
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>
