<?php
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$search = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
$location = trim($_GET['location'] ?? '');
$category = $_GET['category'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where[] = "(item_name LIKE ? OR item_desc LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%"]);
    $types .= 'ss';
}

if (!empty($status)) {
    $where[] = "status = ?";
    $params[] = $status;
    $types .= 's';
}

if (!empty($location)) {
    $where[] = "location LIKE ?";
    $params[] = "%$location%";
    $types .= 's';
}

if (!empty($category)) {
    $where[] = "category = ?";
    $params[] = $category;
    $types .= 's';
}

if (!empty($date_from)) {
    $where[] = "created_at >= ?";
    $params[] = $date_from;
    $types .= 's';
}

$where_clause = $where ? "WHERE " . implode(" AND ", $where) : "";


$count_sql = "SELECT COUNT(*) as total FROM items $where_clause";
$count_stmt = $conn->prepare($count_sql);
if ($params) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_result = $count_stmt->get_result();
$total_items = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_items / $limit);


$sql = "SELECT * FROM items $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$items = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost & Found - College</title>
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
                <?php if ($_SESSION['user_role'] === 'staff'): ?>
                    <a href="staff_dashboard.php">Staff Dashboard</a>
                <?php endif; ?>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a href="admin.php">Admin</a>
                <?php endif; ?>
                <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2>Search Lost & Found Items</h2>
        
        <form method="GET" class="search-form">
            <input type="text" name="q" placeholder="Search items..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="status">
                <option value="">All Status</option>
                <option value="Lost" <?php echo $status === 'Lost' ? 'selected' : ''; ?>>Lost</option>
                <option value="Found" <?php echo $status === 'Found' ? 'selected' : ''; ?>>Found</option>
            </select>
            <input type="text" name="location" placeholder="Location" value="<?php echo htmlspecialchars($location); ?>">
            <select name="category">
                <option value="">All Categories</option>
                <option value="General" <?php echo $category === 'General' ? 'selected' : ''; ?>>General</option>
                <option value="Electronics" <?php echo $category === 'Electronics' ? 'selected' : ''; ?>>Electronics</option>
                <option value="Clothing" <?php echo $category === 'Clothing' ? 'selected' : ''; ?>>Clothing</option>
            </select>
            <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
            <button type="submit">Search</button>
        </form>

        <div class="items-grid">
            <?php if ($items->num_rows > 0): ?>
                <?php while ($item = $items->fetch_assoc()): ?>
                    <div class="item-card">
                        <?php if ($item['image_path'] && file_exists($item['image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>">
                        <?php endif; ?>
                        <h3><?php echo htmlspecialchars($item['item_name']); ?></h3>
                        <p class="item-desc"><?php echo htmlspecialchars($item['item_desc']); ?></p>
                        <div class="item-meta">
                            <span class="status <?php echo strtolower($item['status']); ?>"><?php echo htmlspecialchars($item['status']); ?></span>
                            <span class="category"><?php echo htmlspecialchars($item['category']); ?></span>
                            <span class="location">📍 <?php echo htmlspecialchars($item['location']); ?></span>
                            <span class="date"><?php echo date('M j, Y', strtotime($item['created_at'])); ?></span>
                        </div>
                        <?php if ($_SESSION['user_role'] !== 'admin' && $item['status'] === 'Found'): ?>
                            <a href="claim_item.php?item_id=<?php echo $item['id']; ?>" class="btn">Claim This Item</a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No items found.</p>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p> 2025 College Lost & Found System</p>
    </footer>
    
    <script src="sw.js"></script>
</body>
</html>