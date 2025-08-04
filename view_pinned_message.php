<?php
include 'db_connect.php';

$group_id = $_GET['group_id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM group_messages WHERE group_id = ? AND is_pinned = 1 ORDER BY sent_at DESC");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$results = $stmt->get_result();

echo "<h3>Pinned Messages</h3><hr>";
while ($msg = $results->fetch_assoc()) {
    echo "<div style='margin-bottom: 10px;'><strong>{$msg['message']}</strong><br><small>At: {$msg['sent_at']}</small></div>";
}
?>
