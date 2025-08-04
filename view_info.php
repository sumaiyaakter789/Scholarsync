<?php
include 'db_connect.php';

$group_id = $_GET['group_id'] ?? 0;

if (!$group_id) {
    echo "<p>Invalid group ID.</p>";
    exit;
}

// Prepare and execute query to get group info
$stmt = $conn->prepare("
    SELECT groups.name, groups.thumbnail_path, groups.created_at, users.username AS creator
    FROM groups
    LEFT JOIN users ON groups.created_by = users.id
    WHERE groups.id = ?
");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$result = $stmt->get_result();

if ($group = $result->fetch_assoc()) {
    echo "<h3>Group Information</h3><hr>";

    // Group name
    echo "<p><strong>Name:</strong> " . htmlspecialchars($group['name']) . "</p>";

    // Group thumbnail (if exists)
    if (!empty($group['thumbnail_path'])) {
        echo "<p><img src='" . htmlspecialchars($group['thumbnail_path']) . "' alt='Group Thumbnail' style='max-width: 100%; height: auto;'></p>";
    }

    // Creator username
    echo "<p><strong>Created by:</strong> " . htmlspecialchars($group['creator'] ?? 'Unknown') . "</p>";

    // Created at date
    echo "<p><strong>Created on:</strong> " . date("F j, Y, g:i a", strtotime($group['created_at'])) . "</p>";

} else {
    echo "<p>Group not found.</p>";
}

$stmt->close();
$conn->close();
?>
