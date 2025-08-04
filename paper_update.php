<?php
session_start();
include "db_connect.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch paper_id safely from GET or POST
$paper_id = null;
if (isset($_GET['paper_id'])) {
    $paper_id = intval($_GET['paper_id']);
} elseif (isset($_POST['paper_id'])) {
    $paper_id = intval($_POST['paper_id']);
}

if (!$paper_id) {
    echo "Invalid Paper ID!";
    exit();
}

// Fetch paper details
$sql = "SELECT * FROM research_papers WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $paper_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Research paper not found!";
    exit();
}

$paper = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_update'])) {
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);

    // Default: keep old file_path and thumbnail_path
    $file_path = $paper['file_path'];
    $thumbnail_path = $paper['thumbnail'];

    // Check if user uploaded a new file
    if (isset($_FILES['new_file']) && $_FILES['new_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['new_file']['tmp_name'];
        $fileName = $_FILES['new_file']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Allowed file extensions
        $allowedfileExtensions = array('pdf', 'doc', 'docx');

        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = uniqid() . '.' . $fileExtension;
            $uploadFileDir = 'uploads/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $file_path = $dest_path;
            } else {
                echo 'There was an error uploading your file.';
                exit();
            }
        } else {
            echo 'Upload failed. Allowed file types: ' . implode(', ', $allowedfileExtensions);
            exit();
        }
    }

    // Check if user uploaded a new thumbnail
    if (isset($_FILES['new_thumbnail']) && $_FILES['new_thumbnail']['error'] === UPLOAD_ERR_OK) {
        $thumbTmpPath = $_FILES['new_thumbnail']['tmp_name'];
        $thumbName = $_FILES['new_thumbnail']['name'];
        $thumbNameCmps = explode(".", $thumbName);
        $thumbExtension = strtolower(end($thumbNameCmps));

        // Allowed thumbnail extensions
        $allowedThumbExtensions = array('jpg', 'jpeg', 'png', 'gif');

        if (in_array($thumbExtension, $allowedThumbExtensions)) {
            $newThumbName = uniqid('thumb_') . '.' . $thumbExtension;
            $thumbUploadDir = 'thumbnails/';
            if (!is_dir($thumbUploadDir)) {
                mkdir($thumbUploadDir, 0777, true);
            }
            $thumb_dest_path = $thumbUploadDir . $newThumbName;

            if (move_uploaded_file($thumbTmpPath, $thumb_dest_path)) {
                $thumbnail_path = $thumb_dest_path;
            } else {
                echo 'There was an error uploading the thumbnail.';
                exit();
            }
        } else {
            echo 'Thumbnail upload failed. Allowed types: ' . implode(', ', $allowedThumbExtensions);
            exit();
        }
    }

    // Validate required fields
    if (empty($title) || empty($category) || empty($description) || empty($file_path)) {
        echo "All fields are required!";
        exit();
    }

    // Update the research paper
    $update_sql = "UPDATE research_papers 
                   SET title = ?, category = ?, description = ?, file_path = ?, thumbnail = ?
                   WHERE id = ? AND user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssssi", $title, $category, $description, $file_path, $thumbnail_path, $paper_id, $_SESSION['user_id']);

    if ($update_stmt->execute()) {
        header("Location: user_profile.php"); // Redirect after update
        exit();
    } else {
        echo "Failed to update the paper! Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Research Paper</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .form-container { max-width: 600px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); }
        h2 { text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; color: #555; }
        input[type="text"], textarea, input[type="file"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-size: 16px; }
        button { width: 100%; padding: 12px; background: #78909C; color: #fff; border: none; font-size: 16px; border-radius: 8px; cursor: pointer; }
        button:hover { background: #607d8b; }
        .current-file, .current-thumbnail { margin-top: 5px; font-size: 14px; color: #777; }
        img.thumbnail-preview { max-width: 150px; margin-top: 10px; border-radius: 8px; }
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
    <h2>Update Research Paper</h2>

    <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="paper_id" value="<?php echo $paper_id; ?>">

        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($paper['title']); ?>" required>
        </div>

        <div class="form-group">
            <label>Category</label>
            <input type="text" name="category" value="<?php echo htmlspecialchars($paper['category']); ?>" required>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="5" required><?php echo htmlspecialchars($paper['description']); ?></textarea>
        </div>

        <div class="form-group">
            <label>Current File</label>
            <div class="current-file">
                <a href="<?php echo htmlspecialchars($paper['file_path']); ?>" target="_blank">View Current File</a>
            </div>
        </div>

        <div class="form-group">
            <label>Upload New File (optional)</label>
            <input type="file" name="new_file">
            <small>Allowed types: pdf, doc, docx</small>
        </div>

        <div class="form-group">
            <label>Current Thumbnail</label>
            <div class="current-thumbnail">
                <?php if (!empty($paper['thumbnail_path'])): ?>
                    <img src="<?php echo htmlspecialchars($paper['thumbnail_path']); ?>" alt="Thumbnail" class="thumbnail-preview">
                <?php else: ?>
                    No thumbnail uploaded.
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <label>Upload New Thumbnail (optional)</label>
            <input type="file" name="new_thumbnail">
            <small>Allowed types: jpg, jpeg, png, gif</small>
        </div>

        <button type="submit" name="save_update">Save Changes</button>
    </form>
</div>

<?php include "footer.php"; ?>

</body>
</html>
