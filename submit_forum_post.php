<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];
$content = trim($_POST['content']);
$image_path = '';

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $uploadDir = 'uploads/forum/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $image_path = $uploadDir . basename($_FILES['image']['name']);
    move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
}

$stmt = $conn->prepare("INSERT INTO forum_posts (user_id, content, image_path) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $content, $image_path);

if ($stmt->execute()) {
    echo "Your post has been submitted for admin approval.";
} else {
    echo "Failed to submit post.";
}
?>
