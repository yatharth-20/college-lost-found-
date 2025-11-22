<?php
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['staff', 'admin'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $claim_id = intval($_POST['claim_id'] ?? 0);
    
    $claim = fetchOne($conn, "
        SELECT c.*, i.item_name 
        FROM claims c 
        JOIN items i ON c.item_id = i.id 
        WHERE c.id = ?
    ", [$claim_id]);
    
    if (!$claim) {
        $_SESSION['error'] = "Claim not found";
        header('Location: admin.php');
        exit();
    }
    

    $stmt = $conn->prepare("UPDATE claims SET status = 'Rejected' WHERE id = ?");
    $stmt->bind_param("i", $claim_id);
    
    if ($stmt->execute()) {

        require_once 'log_action.php';
        log_action($conn, $_SESSION['user_id'], 'REJECT', 'claims', $claim_id, ['status' => 'Pending'], ['status' => 'Rejected']);
        

        require_once 'send_notification.php';
        send_notification(
            $claim['claimer_email'],
            "Claim Rejected - " . htmlspecialchars($claim['item_name']),
            "Your claim for '" . htmlspecialchars($claim['item_name']) . "' has been rejected. Please contact the lost & found office for more information."
        );
        
        $_SESSION['success'] = "Claim rejected successfully!";
    } else {
        $_SESSION['error'] = "Failed to reject claim: " . $conn->error;
    }
}

header('Location: admin.php');
exit();