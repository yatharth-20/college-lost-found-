<?php
function log_action($conn, $user_id, $action, $table_name, $record_id, $old_data, $new_data) {
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, old_data, new_data) VALUES (?, ?, ?, ?, ?, ?)");
    $old_json = json_encode($old_data);
    $new_json = json_encode($new_data);
    $stmt->bind_param("ississ", $user_id, $action, $table_name, $record_id, $old_json, $new_json);
    $stmt->execute();
}
?>