<?php
include "db_connect.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $type = $_POST['type'];

    if ($type === 'confirmed') {
        $stmt = $conn->prepare("UPDATE job_applications SET is_read = 1 WHERE id = ? AND user_id = ?");
    } else {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    }
    $stmt->bind_param("ii", $id, $_SESSION['user_id']);
    $stmt->execute();
}
?>
