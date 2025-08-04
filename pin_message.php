<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'], $_GET['message_id'], $_GET['action'])) {
    echo "Invalid request.";
    exit();
}

$user_id = $_SESSION['user_id'];
$message_id = (int)$_GET['message_id'];
$action = $_GET['action'];

if (!in_array($action, ['pin', 'unpin'])) {
    echo "Invalid action.";
    exit();
}

// First, check if the message exists and belongs to a group the user is part of (optional for stricter control)
$stmt = $conn->prepare("SELECT gm.id, gm.group_id FROM group_messages gm 
    JOIN group_members gmbr ON gm.group_id = gmbr.group_id 
    WHERE gm.id = ? AND gmbr.user_id = ?");
$stmt->bind_param("ii", $message_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Message not found or you're not authorized.";
    exit();
}

// Update pin status
$is_pinned = ($action === 'pin') ? 1 : 0;

$update = $conn->prepare("UPDATE group_messages SET is_pinned = ? WHERE id = ?");
$update->bind_param("ii", $is_pinned, $message_id);

if ($update->execute()) {
    echo $is_pinned ? "Message pinned successfully." : "Message unpinned successfully.";
} else {
    echo "Failed to update pin status: " . $update->error;
}
?>
