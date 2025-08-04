<?php
session_start();
include "db_connect.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle delete request
if (isset($_POST['delete']) && isset($_POST['post_id'])) {
    $post_id = (int)$_POST['post_id'];

    // Optional: delete the image file if it exists
    $img_stmt = $conn->prepare("SELECT image_path FROM forum_posts WHERE id = ? AND user_id = ?");
    $img_stmt->bind_param("ii", $post_id, $_SESSION['user_id']);
    $img_stmt->execute();
    $img_result = $img_stmt->get_result();

    if ($img_result->num_rows > 0) {
        $row = $img_result->fetch_assoc();
        if (!empty($row['image_path']) && file_exists($row['image_path'])) {
            unlink($row['image_path']);
        }
    }

    // Delete the forum post
    $delete_stmt = $conn->prepare("DELETE FROM forum_posts WHERE id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $post_id, $_SESSION['user_id']);

    if ($delete_stmt->execute()) {
        header("Location: user_profile.php");
        exit();
    } else {
        echo "Failed to delete the post!";
    }
} else {
    echo "Invalid request.";
}
?>
