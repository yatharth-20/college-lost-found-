<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } else {
        $user = fetchOne($conn, "SELECT * FROM users WHERE email = ?", [$email]);
        
        if ($user) {

            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE email = ?");
            $stmt->bind_param("sss", $token, $expiry, $email);
            
            if ($stmt->execute()) {
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_form.php?token=$token";
                
                $subject = "Password Reset - College Lost & Found";
                $message = "Hello " . htmlspecialchars($user['name']) . ",\n\n" .
                          "You requested a password reset. Click the link below to reset your password:\n" .
                          $reset_link . "\n\n" .
                          "This link will expire in 1 hour.\n\n" .
                          "If you didn't request this, please ignore this email.";
                
                @mail($email, $subject, $message);
                
                $_SESSION['success'] = "Password reset link sent to your email!";
                header('Location: login.php');
                exit();
            } else {
                $error = "Failed to process reset request";
            }
        } else {
            $error = "No account found with that email";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <link rel="manifest" href="manifest.json">
</head>
<body>
    <div class="container">
        <div class="login-form">
            <h2>Reset Password</h2>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="email" name="email" placeholder="Enter your email" required>
                <button type="submit">Send Reset Link</button>
            </form>
            <p><a href="login.php">Back to Login</a></p>
        </div>
    </div>
</body>
</html>