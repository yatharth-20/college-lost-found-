<?php
require_once 'db_connect.php';


if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } else {

        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = "Email already exists";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
            $stmt->bind_param("sss", $name, $email, $hashed_password);
            
            if ($stmt->execute()) {
                $success = "Admin user created successfully! You can now login.";
            } else {
                $error = "Failed to create admin user: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin - Lost & Found</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="login-form">
            <h2>Create Admin Account</h2>
            <p style="text-align: center; color: #666; margin-bottom: 1rem;">
                One-time admin creation
            </p>
            
            <?php if (isset($success)): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
                <p style="text-align: center; margin-top: 1rem;">
                    <a href="login.php" class="btn">Go to Login</a>
                </p>
            <?php else: ?>
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="text" name="name" placeholder="Admin Name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    <input type="email" name="email" placeholder="Admin Email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    <input type="password" name="password" placeholder="Admin Password (min 6 characters)" required>
                    <button type="submit">Create Admin Account</button>
                </form>
                <p style="text-align: center;"><a href="login.php">Back to Login</a></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>