<?php
session_start();
include "db_connect.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$post_id = isset($_GET['post_id']) ? (int)$_GET['post_id'] : (isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0);

if (!$post_id) {
    echo "Invalid Post ID!";
    exit();
}

// Fetch post
$sql = "SELECT * FROM forum_posts WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $post_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Forum post not found!";
    exit();
}

$post = $result->fetch_assoc();

// Handle update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_update'])) {
    $content = trim($_POST['content']);
    $image_path = $post['image_path'];

    // Image update
    if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
        $imgTmp = $_FILES['new_image']['tmp_name'];
        $imgName = $_FILES['new_image']['name'];
        $imgExt = strtolower(pathinfo($imgName, PATHINFO_EXTENSION));
        $allowedImgTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imgExt, $allowedImgTypes)) {
            $newImgName = uniqid('forum_') . '.' . $imgExt;
            $uploadDir = 'forum_images/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $imgDest = $uploadDir . $newImgName;

            if (move_uploaded_file($imgTmp, $imgDest)) {
                $image_path = $imgDest;
            } else {
                echo "Failed to upload image.";
                exit();
            }
        } else {
            echo "Invalid image format. Allowed: jpg, jpeg, png, gif";
            exit();
        }
    }

    if (empty($content)) {
        echo "Content cannot be empty!";
        exit();
    }

    // Update query
    $update = $conn->prepare("UPDATE forum_posts SET content = ?, image_path = ?, status = 'pending' WHERE id = ? AND user_id = ?");
    $update->bind_param("ssii", $content, $image_path, $post_id, $_SESSION['user_id']);

    if ($update->execute()) {
        header("Location: user_profile.php");
        exit();
    } else {
        echo "Failed to update the post!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Forum Post</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .form-container { max-width: 600px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); }
        h2 { text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; color: #555; }
        textarea, input[type="file"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-size: 16px; }
        button { width: 100%; padding: 12px; background: #78909C; color: #fff; border: none; font-size: 16px; border-radius: 8px; cursor: pointer; }
        button:hover { background: #607d8b; }
        img.preview { max-width: 100%; border-radius: 8px; margin-top: 10px; }
        small { display: block; margin-top: 5px; color: #777; }
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

<?php include "header.php"; ?>

<a href="user_profile.php" class="back-link">← Back to Dashboard</a>
<div class="form-container">
    <h2>Edit Forum Post</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="post_id" value="<?= $post_id ?>">

        <div class="form-group">
            <label>Post Content</label>
            <textarea name="content" rows="5" required><?= htmlspecialchars($post['content']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Current Image</label>
            <?php if (!empty($post['image_path'])): ?>
                <img src="<?= htmlspecialchars($post['image_path']) ?>" alt="Current Image" class="preview">
            <?php else: ?>
                <p>No image uploaded.</p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Upload New Image (optional)</label>
            <input type="file" name="new_image">
            <small>Allowed: jpg, jpeg, png, gif</small>
        </div>

        <button type="submit" name="save_update">Update Post</button>
    </form>
</div>

<?php include "footer.php"; ?>

</body>
</html>
