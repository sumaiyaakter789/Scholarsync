<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("You must be logged in to comment.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $post_id = $_POST['post_id'];
    $comment = trim($_POST['comment']);

    if (!empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO forum_comments (user_id, post_id, comment, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $user_id, $post_id, $comment);

        if ($stmt->execute()) {
            // ✅ Redirect to forums.php with highlight
            header("Location: forums.php?highlight_id=post$post_id");
            exit;
        } else {
            echo "Failed to save comment.";
        }
    } else {
        echo "Comment cannot be empty.";
    }
} else {
    echo "Invalid request.";
}
