<?php
session_start();
include "db_connect.php";

// Make sure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'], $_POST['reaction'])) {
    $post_id = (int) $_POST['post_id'];
    $user_id = $_SESSION['user_id'];
    $reaction = $_POST['reaction'];

    // Only allow valid reaction types
    if (!in_array($reaction, ['like', 'dislike'])) {
        header("Location: forums.php");
        exit();
    }

    // Check if the user has already reacted to this post
    $check_sql = "SELECT * FROM forum_reactions WHERE post_id = ? AND user_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $existing = $result->fetch_assoc();
        if ($existing['reaction_type'] === $reaction) {
            // Same reaction → remove it (toggle off)
            $delete_sql = "DELETE FROM forum_reactions WHERE id = ?";
            $del_stmt = $conn->prepare($delete_sql);
            $del_stmt->bind_param("i", $existing['id']);
            $del_stmt->execute();
        } else {
            // Different reaction → update it
            $update_sql = "UPDATE forum_reactions SET reaction_type = ?, created_at = NOW() WHERE id = ?";
            $upd_stmt = $conn->prepare($update_sql);
            $upd_stmt->bind_param("si", $reaction, $existing['id']);
            $upd_stmt->execute();
        }
    } else {
        // No previous reaction → insert new one
        $insert_sql = "INSERT INTO forum_reactions (post_id, user_id, reaction_type) VALUES (?, ?, ?)";
        $ins_stmt = $conn->prepare($insert_sql);
        $ins_stmt->bind_param("iis", $post_id, $user_id, $reaction);
        $ins_stmt->execute();
    }

    // Redirect back to forum or previous page
    header("Location: forums.php");
    exit();
} else {
    header("Location: forums.php");
    exit();
}

?>