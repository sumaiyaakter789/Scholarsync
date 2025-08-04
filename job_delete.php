<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['job_id'])) {
    $job_id = (int)$_POST['job_id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT thumbnail FROM jobs WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $job_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "Job not found or access denied.";
        exit();
    }

    $job = $result->fetch_assoc();

    // Delete related job applications first
    $delete_apps_stmt = $conn->prepare("DELETE FROM job_applications WHERE job_id = ?");
    $delete_apps_stmt->bind_param("i", $job_id);
    $delete_apps_stmt->execute();

    // Then delete the job
    $delete_stmt = $conn->prepare("DELETE FROM jobs WHERE id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $job_id, $user_id);


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo "POST detected<br>";
        echo "POST delete: " . ($_POST['delete'] ?? 'Not set') . "<br>";
        echo "POST job_id: " . ($_POST['job_id'] ?? 'Not set') . "<br>";
    }
    

    if ($delete_stmt->execute()) {
        if (!empty($job['thumbnail']) && file_exists($job['thumbnail'])) {
            unlink($job['thumbnail']);
        }

        header("Location: user_profile.php");
        exit();
    } else {
        echo "Failed to delete the job post.";
    }
} else {
    echo "Invalid request.";
}
?>
