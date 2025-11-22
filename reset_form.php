<?php
require_once 'db_connect.php';

$token = $_GET['token'] ?? '';
$user = fetchOne($conn, "SELECT * FROM users WHERE reset_token = ? AND reset_expiry > NOW()", [$token]);

if (!$user) {
    $_SESSION['error'] = "Invalid or expired reset token";
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user['id']);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Password reset successfully! Please login.";
            header('Location: login.php');
            exit();
        } else {
            $error = "Failed to reset password";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password - Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <link rel="manifest" href="manifest.json">
</head>
<body>
    <div class="container">
        <div class="login-form">
            <h2>Set New Password</h2>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="password" name="password" placeholder="New Password (min 6 characters)" required>
                <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                <button type="submit">Reset Password</button>
            </form>
            <p><a href="login.php">Back to Login</a></p>
        </div>
    </div>
</body>
</html>