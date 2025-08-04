<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['message_id'])) {
    echo "Invalid request.";
    exit();
}

$user_id = $_SESSION['user_id'];
$message_id = (int)$_GET['message_id'];

// Check if message exists
$stmt = $conn->prepare("SELECT id FROM group_messages WHERE id = ?");
$stmt->bind_param("i", $message_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Message does not exist.";
    exit();
}

// Check if already deleted by the user
$check = $conn->prepare("SELECT id FROM deleted_messages WHERE message_id = ? AND user_id = ?");
$check->bind_param("ii", $message_id, $user_id);
$check->execute();
$checkResult = $check->get_result();

if ($checkResult->num_rows > 0) {
    echo "Message already deleted for you.";
    exit();
}

// Insert into deleted_messages
$insert = $conn->prepare("INSERT INTO deleted_messages (message_id, user_id) VALUES (?, ?)");
$insert->bind_param("ii", $message_id, $user_id);

if ($insert->execute()) {
    echo "Message deleted for you.";
} else {
    echo "Error deleting message: " . $insert->error;
}
?>
