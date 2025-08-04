<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include 'db_connect.php';

    $user_id = $_SESSION['user_id'];
    $content = trim($_POST['content']);
    $image_path = null;

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = 'forum_uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir);

        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $imageName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $image_path = $targetPath;
        } else {
            $error = "Image upload failed.";
        }
    }

    if (empty($content) && !$image_path) {
        $error = "Please enter some content or upload an image.";
    }

    if (!$error) {
        $stmt = $conn->prepare("INSERT INTO forum_posts (user_id, content, image_path) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $content, $image_path);
        if ($stmt->execute()) {
            $success = "Post submitted successfully!";
        } else {
            $error = "Database error.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post to Forum</title>
    <style>
        .form-container {
            margin: 50px auto;
            max-width: 700px;
            background-color: #f5f5f5;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-size: 16px;
            color: #555;
            display: block;
            margin-bottom: 6px;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        textarea {
            resize: vertical;
        }

        button {
            background-color: #78909C;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
            display: block;
            width: 100%;
            margin-top: 10px;
        }

        button:hover {
            background-color: #607d8b;
        }

        .error-message {
            color: red;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .success-message {
            color: green;
            font-size: 16px;
            margin-bottom: 20px;
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
<?php include "header.php " ?>
<a href="user_profile.php" class="back-link">← Back to Dashboard</a>

<div class="form-container">
    <form action="submit_forum_post.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="content">What's on your mind?</label>
            <textarea id="content" name="content" rows="5" required></textarea>
        </div>

        <div class="form-group">
            <label for="image">Upload an image (optional)</label>
            <input type="file" id="image" name="image" accept="image/*">
        </div>

        <button type="submit">Post to Forum</button>
    </form>
</div>

</body>
</html>
