<?php
session_start();
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['application_id'])) {
    $application_id = (int)$_POST['application_id'];

    $sql = "UPDATE job_applications SET status = 'confirmed' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $application_id);

    if ($stmt->execute()) {
        echo "<script>alert('Applicant confirmed successfully!'); window.history.back();</script>";
    } else {
        echo "<script>alert('Failed to confirm applicant.'); window.history.back();</script>";
    }
} else {
    echo "Invalid request.";
}
?>
