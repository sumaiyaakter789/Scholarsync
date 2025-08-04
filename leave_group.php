<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['group_id'])) {
    echo "Invalid request.";
    exit();
}

$user_id = $_SESSION['user_id'];
$group_id = $_GET['group_id'];

// Check if user is a member
$checkStmt = $conn->prepare("SELECT * FROM group_members WHERE group_id = ? AND user_id = ?");
$checkStmt->bind_param("ii", $group_id, $user_id);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows == 0) {
    echo "You are not a member of this group.";
    exit();
}

// Remove user from group
$deleteStmt = $conn->prepare("DELETE FROM group_members WHERE group_id = ? AND user_id = ?");
$deleteStmt->bind_param("ii", $group_id, $user_id);

if ($deleteStmt->execute()) {
    header("Location: user_profile.php?msg=left");
    exit();
} else {
    echo "Error leaving group.";
}
?>
