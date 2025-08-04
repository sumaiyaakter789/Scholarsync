<?php
require 'db_connect.php';

if (isset($_POST['job_id'])) {
    $job_id = intval($_POST['job_id']);
    $stmt = $conn->prepare("UPDATE jobs SET status='approved' WHERE id=?");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: job_control.php");
exit();
?>
