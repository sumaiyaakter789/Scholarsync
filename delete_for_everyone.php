<?php
session_start();
include 'db_connect.php';

// Debugging session and GET variable
if (!isset($_SESSION['user_id'])) {
    echo "Error: You are not logged in.";
    exit();
}

if (!isset($_GET['message_id'])) {
    echo "Error: Message ID is missing.";
    exit();
}

$user_id = $_SESSION['user_id'];
$message_id = (int)$_GET['message_id'];

// Check if the message exists and belongs to the current user
$stmt = $conn->prepare("SELECT * FROM group_messages WHERE id = ? AND user_id = ?");
if (!$stmt) {
    echo "Prepare failed: " . $conn->error;
    exit();
}

$stmt->bind_param("ii", $message_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Message not found or you are not authorized to delete it.";
    exit();
}

$message = $result->fetch_assoc();

// Delete the attachment file if it exists
if (!empty($message['attachment_path'])) {
    $file_path = $message['attachment_path'];
    if (file_exists($file_path)) {
        unlink($file_path);  // delete the file
    }
}

// Delete the message from group_messages
$delStmt = $conn->prepare("DELETE FROM group_messages WHERE id = ?");
if (!$delStmt) {
    echo "Error preparing delete statement: " . $conn->error;
    exit();
}

$delStmt->bind_param("i", $message_id);
if ($delStmt->execute()) {
    echo "Message deleted successfully.";
} else {
    echo "Failed to delete the message. Error: " . $delStmt->error;
}
?>
