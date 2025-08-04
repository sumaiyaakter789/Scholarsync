<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['group_id']) && isset($_POST['reply_to']) && isset($_SESSION['user_id'])) {
        $group_id = (int)$_POST['group_id'];
        $reply_to = (int)$_POST['reply_to'];
        $user_id = (int)$_SESSION['user_id'];

        $attachment_path = null;
        $upload_dir = 'uploads/attachments/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
            $file_name = $_FILES['attachment']['name'];
            $file_tmp = $_FILES['attachment']['tmp_name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'docx'];
            if (in_array($file_ext, $allowed_types)) {
                $attachment_path = $upload_dir . uniqid() . '.' . $file_ext;
                if (!move_uploaded_file($file_tmp, $attachment_path)) {
                    echo "Failed to upload file.";
                    exit();
                }
            } else {
                echo "Invalid file type.";
                exit();
            }
        }

        $message = isset($_POST['message']) ? trim($_POST['message']) : null;

        if (empty($message) && !$attachment_path) {
            echo "Cannot send an empty message.";
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO group_messages (group_id, user_id, message, attachment_path, reply_to, sent_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("iissi", $group_id, $user_id, $message, $attachment_path, $reply_to);

        if ($stmt->execute()) {
            header("Location: group_view.php?group_id=" . $group_id);
            exit();
        } else {
            echo "Error sending reply: " . $stmt->error;
        }
    }
}
?>
