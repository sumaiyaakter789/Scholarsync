<?php
session_start();
include 'db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check for POST data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_id'], $_POST['reply'])) {
    $comment_id = (int)$_POST['comment_id'];
    $reply_text = trim($_POST['reply']);
    $user_id = $_SESSION['user_id'];

    if ($reply_text !== '') {
        $stmt = $conn->prepare("INSERT INTO comment_replies (comment_id, user_id, reply, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $comment_id, $user_id, $reply_text);
        
        if ($stmt->execute()) {
            // Redirect back to the referring page (forum)
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Reply cannot be empty.";
    }
} else {
    echo "Invalid request.";
}
?>
