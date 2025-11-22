<?php

function send_item_notification($conn, $item_id, $type) {
    $item = fetchOne($conn, "SELECT * FROM items WHERE id = ?", [$item_id]);
    if (!$item) return false;
    

    $interested_users = [];
    

    if ($item['status'] == 'Lost') {
        $sql = "SELECT DISTINCT u.email, u.name 
                FROM users u 
                JOIN items i ON u.id = i.user_id 
                WHERE i.status = 'Found' 
                AND (i.category = ? OR i.location LIKE ?)
                AND u.email != ?";
        $stmt = $conn->prepare($sql);
        $location_pattern = "%" . $item['location'] . "%";
        $stmt->bind_param("sss", $item['category'], $location_pattern, $item['user_email']);
        $stmt->execute();
        $interested_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    

    if ($item['status'] == 'Found') {
        $sql = "SELECT DISTINCT u.email, u.name 
                FROM users u 
                JOIN items i ON u.id = i.user_id 
                WHERE i.status = 'Lost' 
                AND (i.category = ? OR i.item_name LIKE ? OR i.location LIKE ?)
                AND u.email != ?";
        $stmt = $conn->prepare($sql);
        $name_pattern = "%" . $item['item_name'] . "%";
        $location_pattern = "%" . $item['location'] . "%";
        $stmt->bind_param("ssss", $item['category'], $name_pattern, $location_pattern, $item['user_email']);
        $stmt->execute();
        $interested_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    

    foreach ($interested_users as $user) {
        $subject = "Potential Match: " . htmlspecialchars($item['item_name']);
        $message = "Hello " . htmlspecialchars($user['name']) . ",\n\n";
        $message .= "A " . $item['status'] . " item might match something you reported:\n\n";
        $message .= "Item: " . htmlspecialchars($item['item_name']) . "\n";
        $message .= "Description: " . htmlspecialchars($item['item_desc']) . "\n";
        $message .= "Location: " . htmlspecialchars($item['location']) . "\n";
        $message .= "Category: " . htmlspecialchars($item['category']) . "\n";
        $message .= "Reported: " . date('M j, Y', strtotime($item['created_at'])) . "\n\n";
        $message .= "View item: http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/index.php?q=" . urlencode($item['item_name']);
        
        @mail($user['email'], $subject, $message);
    }
    
    return count($interested_users);
}
?>