<?php
include 'db_connect.php';

$group_id = $_GET['group_id'] ?? 0;

if (!$group_id) {
    echo "<p>Invalid group ID.</p>";
    exit;
}

// Prepare and execute query to get group members info
$stmt = $conn->prepare("
    SELECT users.username, users.email 
    FROM group_members
    JOIN users ON group_members.user_id = users.id
    WHERE group_members.group_id = ?
");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$results = $stmt->get_result();

echo "<h3>Group Members</h3><hr>";

if ($results->num_rows > 0) {
    while ($member = $results->fetch_assoc()) {
        echo "<div style='margin-bottom: 12px;'>
                <strong>" . htmlspecialchars($member['username']) . "</strong><br>
                <small>" . htmlspecialchars($member['email']) . "</small>
              </div>";
    }
} else {
    echo "<p>No members found in this group.</p>";
}

$stmt->close();
$conn->close();
?>
