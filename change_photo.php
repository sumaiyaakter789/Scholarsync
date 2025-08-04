<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to change the group photo.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Get group_id from GET or POST (better to get from GET here)
if (!isset($_GET['group_id']) || !is_numeric($_GET['group_id'])) {
    echo "Invalid group ID.";
    exit();
}

$group_id = (int)$_GET['group_id'];

// Optional: Check if the user is allowed to change this group's photo (e.g., is creator or member)
// Check if user is creator
$stmt = $conn->prepare("SELECT created_by FROM groups WHERE id = ?");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$stmt->bind_result($created_by);
if (!$stmt->fetch()) {
    echo "Group not found.";
    exit();
}
$stmt->close();

if ($created_by != $user_id) {
    // Optional: you can also check if user is a member here if you want more permissive access
    echo "You do not have permission to change this group's photo.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['group_photo']) && $_FILES['group_photo']['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['group_photo']['type'];
        $fileSize = $_FILES['group_photo']['size'];

        if (!in_array($fileType, $allowedTypes)) {
            echo "Only JPG, PNG, and GIF files are allowed.";
            exit();
        }

        if ($fileSize > 2 * 1024 * 1024) { // 2MB limit
            echo "File size must be less than 2MB.";
            exit();
        }

        $ext = pathinfo($_FILES['group_photo']['name'], PATHINFO_EXTENSION);
        $newFileName = 'uploads/group_' . $group_id . '_' . time() . '.' . $ext;

        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        if (move_uploaded_file($_FILES['group_photo']['tmp_name'], $newFileName)) {
            $stmt = $conn->prepare("UPDATE groups SET thumbnail_path = ? WHERE id = ?");
            $stmt->bind_param("si", $newFileName, $group_id);
            if ($stmt->execute()) {
                header("Location: group_view.php?group_id=$group_id&msg=photo_updated");
                exit();
            } else {
                echo "Failed to update group photo in database.";
            }
        } else {
            echo "Failed to upload file.";
        }
    } else {
        echo "No file uploaded or there was an upload error.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Group Photo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7f9;
            padding: 40px;
        }

        .container {
            max-width: 500px;
            background: #fff;
            padding: 30px;
            margin: 0 auto;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h2 {
            margin-bottom: 20px;
        }

        input[type="file"] {
            display: block;
            margin-bottom: 20px;
        }

        button {
            background: #78909C;
            color: black;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #607D8B;
            color: white;
        }

        .back-link {
            position: absolute;
            text-decoration: none;
            font-size: 16px;
            color: #003366;
            font-weight: bold;
            background-color: rgba(164, 185, 187, 0.5);
            padding: 8px 14px;
            border-radius: 12px;
            transition: background-color 0.3s ease;
        }

        .back-link:hover {
            background-color: rgba(120, 144, 156, 0.7);
        }
    </style>
</head>
<body>

<a href="group_view.php?group_id=<?= $group_id ?>" class="back-link">← Back to Group</a>
<div class="container">
    <h2>Change Group Photo</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="file" name="group_photo" accept="image/*" required>
        <button type="submit">Upload</button>
    </form>
</div>
</body>
</html>
