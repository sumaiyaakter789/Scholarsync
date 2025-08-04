<?php
session_start();
header('Content-Type: application/json');
include "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['paper_id'], $_POST['rating'], $_POST['review'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }

    $paperId = intval($_POST['paper_id']);
    $rating = intval($_POST['rating']);
    $review = trim($_POST['review']);
    $userId = intval($_SESSION['user_id']);

    $stmt = $conn->prepare("INSERT INTO reviews (user_id, paper_id, rating, review, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiis", $userId, $paperId, $rating, $review);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>
