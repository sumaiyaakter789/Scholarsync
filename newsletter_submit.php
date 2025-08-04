<?php
session_start();
include "db_connect.php";

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_email'])) {
    $new_email = trim($_POST['new_email']);

    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format!";
        exit();
    }

    // Always insert into newsletter_subscriber
    $insert_sub = $conn->prepare("INSERT INTO newsletter_subscribers (user_id, email, subscribed_at) VALUES (?, ?, NOW())");
    $insert_sub->bind_param("is", $user_id, $new_email);
    $insert_sub->execute(); // optional: check for success or duplicates

    // If changing email, update the main users table
    if (isset($_POST['change_email'])) {
        $update_user = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
        $update_user->bind_param("si", $new_email, $user_id);
        $update_user->execute();
        $_SESSION['email'] = $new_email;

        echo "<script>alert('Your newsletter email has been updated.'); window.location='index.php';</script>";
        exit();
    }

    echo "<script>alert('You have successfully subscribed to the newsletter.'); window.location='index.php';</script>";
    exit();
}
?>
