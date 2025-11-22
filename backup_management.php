<?php
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}


$backup_files = [];
$backup_dir = 'backup/';
if (is_dir($backup_dir)) {
    $files = scandir($backup_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && (strpos($file, '.sql') !== false || strpos($file, '.zip') !== false)) {
            $file_path = $backup_dir . $file;
            $backup_files[] = [
                'name' => $file,
                'size' => filesize($file_path),
                'date' => date('Y-m-d H:i:s', filemtime($file_path)),
                'path' => $file_path
            ];
        }
    }

    usort($backup_files, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
}


if (isset($_GET['download']) && isset($_GET['file'])) {
    $file = $_GET['file'];
    $file_path = $backup_dir . basename($file);
    
    if (file_exists($file_path)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    }
}


if (isset($_POST['delete_backup'])) {
    $file = $_POST['backup_file'];
    $file_path = $backup_dir . basename($file);
    
    if (file_exists($file_path)) {
        unlink($file_path);
        $_SESSION['success'] = "Backup deleted successfully!";
        header('Location: backup_management.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup Management - Lost & Found</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1>College Lost & Found - Backup Management</h1>
            <div class="nav-links">
                <a href="index.php">Search</a>
                <a href="admin_management.php">Admin Management</a>
                <a href="backup_management.php">Backups</a>
                <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Database Backups</h2>
            <div>
                <a href="admin_management.php" class="btn">Back to Admin</a>
                <a href="backup_database.php" class="btn">Create New Backup</a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <div class="backup-list">
            <h3>Available Backups</h3>
            <?php if (empty($backup_files)): ?>
                <p>No backup files found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>File Name</th>
                            <th>Size</th>
                            <th>Date Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backup_files as $backup): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($backup['name']); ?></td>
                            <td><?php echo formatSize($backup['size']); ?></td>
                            <td><?php echo $backup['date']; ?></td>
                            <td>
                                <a href="?download=1&file=<?php echo urlencode($backup['name']); ?>" class="btn">Download</a>
                                <form action="" method="POST" style="display: inline;" onsubmit="return confirm('Delete this backup?');">
                                    <input type="hidden" name="backup_file" value="<?php echo htmlspecialchars($backup['name']); ?>">
                                    <button type="submit" name="delete_backup" class="btn delete">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="backup-info">
            <h3>Backup Information</h3>
            <div class="info-cards">
                <div class="card">
                    <h4>📦 What's Backed Up</h4>
                    <ul>
                        <li>Users table (accounts and profiles)</li>
                        <li>Items table (lost/found items)</li>
                        <li>Claims table (item claims)</li>
                        <li>Audit logs (system activity)</li>
                    </ul>
                </div>
                <div class="card">
                    <h4>💾 Storage Location</h4>
                    <p>Backups are stored in: <code>backup/</code> folder</p>
                    <p>Recommended: Download and store backups externally</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
function formatSize($size) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $unit = 0;
    while ($size >= 1024 && $unit < count($units) - 1) {
        $size /= 1024;
        $unit++;
    }
    return round($size, 2) . ' ' . $units[$unit];
}
?>