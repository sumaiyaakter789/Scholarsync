<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['group_id']) && isset($_SESSION['user_id'])) {
        $group_id = (int) $_POST['group_id'];
        $user_id = (int) $_SESSION['user_id'];

        // Validate reply_to
        $reply_to = null;
        if (isset($_POST['reply_to']) && is_numeric($_POST['reply_to']) && (int)$_POST['reply_to'] > 0) {
            $potential_reply_to = (int)$_POST['reply_to'];

            $check_sql = "SELECT id FROM group_messages WHERE id = ?";
            $check_stmt = $conn->prepare($check_sql);
            if ($check_stmt) {
                $check_stmt->bind_param("i", $potential_reply_to);
                $check_stmt->execute();
                $check_stmt->store_result();
                if ($check_stmt->num_rows > 0) {
                    $reply_to = $potential_reply_to;
                }
                $check_stmt->close();
            } else {
                echo "Database error: " . $conn->error;
                exit;
            }
        }

        $upload_dir = 'uploads/attachments/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $attachment_path = null;
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $file_name = $_FILES['attachment']['name'];
            $file_tmp = $_FILES['attachment']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'docx'];
            if (in_array($file_ext, $allowed_types)) {
                $attachment_path = $upload_dir . uniqid('att_', true) . '.' . $file_ext;
                if (!move_uploaded_file($file_tmp, $attachment_path)) {
                    echo "Failed to upload file.";
                    exit;
                }
            } else {
                echo "Invalid file type.";
                exit;
            }
        }

        $message = isset($_POST['message']) ? trim($_POST['message']) : '';
        $message_param = $message !== '' ? $message : null;

        $sql = "INSERT INTO group_messages (group_id, user_id, message, reply_to, attachment_path, sent_at) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            echo "Database error: " . $conn->error;
            exit;
        }

        $stmt->bind_param("iisis", $group_id, $user_id, $message_param, $reply_to, $attachment_path);

        if ($stmt->execute()) {
            // ✅ Also insert into group_media if file was uploaded
            if ($attachment_path !== null) {
                $media_sql = "INSERT INTO group_media (group_id, file_path, uploaded_by) VALUES (?, ?, ?)";
                $media_stmt = $conn->prepare($media_sql);
                if ($media_stmt) {
                    $media_stmt->bind_param("isi", $group_id, $attachment_path, $user_id);
                    $media_stmt->execute();
                    $media_stmt->close();
                }
            }

            header("Location: group_view.php?group_id=" . $group_id);
            exit;
        } else {
            echo "Error sending message: " . $stmt->error;
        }

    } else {
        echo "Missing group ID or user not logged in.";
    }
} else {
    echo "Invalid request method.";
}
?>
