<?php
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['user_role'] === 'admin') {
    header('Location: admin.php');
    exit();
}

$item_id = intval($_GET['item_id'] ?? 0);
$item = fetchOne($conn, "SELECT * FROM items WHERE id = ?", [$item_id]);

if (!$item || $item['status'] !== 'Found') {
    $_SESSION['error'] = "Item not found or not available for claiming";
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $claimer_name = trim($_POST['claimer_name'] ?? '');
    $claimer_email = trim($_POST['claimer_email'] ?? '');
    $confirmation = $_POST['confirmation'] ?? '';
    
    if (empty($claimer_name) || empty($claimer_email)) {
        $error = "Please fill in all fields";
    } elseif (!filter_var($claimer_email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif ($confirmation !== 'yes') {
        $error = "Please confirm that this item belongs to you";
    } else {
        $stmt = $conn->prepare("INSERT INTO claims (item_id, claimer_user_id, claimer_name, claimer_email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $item_id, $_SESSION['user_id'], $claimer_name, $claimer_email);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Claim submitted successfully! Staff will review your claim.";
            header('Location: index.php');
            exit();
        } else {
            $error = "Failed to submit claim: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Item - Lost & Found</title>
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
        <h2>Claim Found Item</h2>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="item-details">
            <h3><?php echo htmlspecialchars($item['item_name']); ?></h3>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($item['item_desc']); ?></p>
            <p><strong>Location Found:</strong> <?php echo htmlspecialchars($item['location']); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($item['category']); ?></p>
            <p><strong>Date Reported:</strong> <?php echo date('M j, Y', strtotime($item['created_at'])); ?></p>
            <?php if ($item['image_path']): ?>
                <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>" style="max-width: 300px;">
            <?php endif; ?>
        </div>
        
        <form method="POST" class="claim-form">
            <h3>Claim Information</h3>
            <input type="text" name="claimer_name" placeholder="Your Full Name" required value="<?php echo htmlspecialchars($_POST['claimer_name'] ?? $_SESSION['user_name']); ?>">
            <input type="email" name="claimer_email" placeholder="Your Email" required value="<?php echo htmlspecialchars($_POST['claimer_email'] ?? $_SESSION['user_email']); ?>">
            
            <div class="confirmation">
                <label>
                    <input type="checkbox" name="confirmation" value="yes" required>
                    I confirm that this item belongs to me and the information provided is accurate
                </label>
            </div>
            
            <button type="submit">Submit Claim</button>
            <a href="index.php" class="btn cancel">Cancel</a>
        </form>
    </div>

    <footer>
        <p>2025 College Lost & Found System</p>
    </footer>
</body>
</html>