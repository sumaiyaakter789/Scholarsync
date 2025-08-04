<?php
session_start();
include "db_connect.php";

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Handle delete request
if (isset($_POST['delete']) && isset($_POST['paper_id'])) {
    $paper_id = $_POST['paper_id'];
    
    // Delete the paper
    $delete_sql = "DELETE FROM research_papers WHERE id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $paper_id, $_SESSION['user_id']);
    
    if ($delete_stmt->execute()) {
        header("Location: user_profile.php");
        exit();
    } else {
        echo "Failed to delete the paper!";
    }
}
?>
