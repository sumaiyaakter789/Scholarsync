<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_id'], $_POST['reaction'])) {
    $user_id = $_SESSION['user_id'];
    $comment_id = (int)$_POST['comment_id'];
    $reaction_type = $_POST['reaction'] === 'dislike' ? 'dislike' : 'like'; // default to 'like' if invalid

    // Check if user already reacted to this comment
    $check = $conn->prepare("SELECT id FROM comment_reactions WHERE comment_id = ? AND user_id = ?");
    $check->bind_param("ii", $comment_id, $user_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Update existing reaction
        $update = $conn->prepare("UPDATE comment_reactions SET reaction_type = ? WHERE comment_id = ? AND user_id = ?");
        $update->bind_param("sii", $reaction_type, $comment_id, $user_id);
        $update->execute();
    } else {
        // Insert new reaction
        $insert = $conn->prepare("INSERT INTO comment_reactions (comment_id, user_id, reaction_type, created_at) VALUES (?, ?, ?, NOW())");
        $insert->bind_param("iis", $comment_id, $user_id, $reaction_type);
        $insert->execute();
    }

    // Redirect back to the forum page
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
} else {
    echo "Invalid request.";
}
?>
