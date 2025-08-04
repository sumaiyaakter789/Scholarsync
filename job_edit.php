<?php
session_start();
include "db_connect.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : (isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0);
if (!$job_id) {
    echo "Invalid Job ID!";
    exit();
}

// Fetch job post
$sql = "SELECT * FROM jobs WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $job_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Job not found!";
    exit();
}

$job = $result->fetch_assoc();

// Handle update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_update'])) {
    $title = trim($_POST['title']);
    $company = trim($_POST['company']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $requirement = trim($_POST['requirement']);

    $thumbnail = $job['thumbnail']; // Keep existing

    // Image update
    if (isset($_FILES['new_thumbnail']) && $_FILES['new_thumbnail']['error'] === UPLOAD_ERR_OK) {
        $thumbTmp = $_FILES['new_thumbnail']['tmp_name'];
        $thumbName = $_FILES['new_thumbnail']['name'];
        $thumbExt = strtolower(pathinfo($thumbName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($thumbExt, $allowedExts)) {
            $newThumbName = uniqid('job_') . '.' . $thumbExt;
            $uploadDir = 'job_thumbnails/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $thumbDest = $uploadDir . $newThumbName;

            if (move_uploaded_file($thumbTmp, $thumbDest)) {
                $thumbnail = $thumbDest;
            } else {
                echo "Thumbnail upload failed!";
                exit();
            }
        } else {
            echo "Invalid image format!";
            exit();
        }
    }

    if (empty($title) || empty($company) || empty($location) || empty($description)) {
        echo "All required fields must be filled!";
        exit();
    }

    $update = $conn->prepare("UPDATE jobs SET title = ?, company = ?, location = ?, description = ?, category = ?, requirement = ?, thumbnail = ?, status = 'pending' WHERE id = ? AND user_id = ?");
    $update->bind_param("sssssssii", $title, $company, $location, $description, $category, $requirement, $thumbnail, $job_id, $_SESSION['user_id']);

    if ($update->execute()) {
        header("Location: user_profile.php");
        exit();
    } else {
        echo "Update failed!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Job Post</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .form-container { max-width: 700px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); }
        h2 { text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; color: #555; }
        input[type="text"], textarea, input[type="file"] {
            width: 100%; padding: 10px; border: 1px solid #ccc;
            border-radius: 8px; font-size: 16px;
        }
        textarea { resize: vertical; }
        button {
            width: 100%; padding: 12px; background: #78909C;
            color: black; border: none; font-size: 16px;
            border-radius: 8px; cursor: pointer;
        }
        button:hover { background: #607d8b; color: white; }
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
    <h2>Edit Job Post</h2>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="job_id" value="<?= $job_id ?>">

        <div class="form-group">
            <label>Job Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($job['title']) ?>" required>
        </div>

        <div class="form-group">
            <label>Company</label>
            <input type="text" name="company" value="<?= htmlspecialchars($job['company']) ?>" required>
        </div>

        <div class="form-group">
            <label>Location</label>
            <input type="text" name="location" value="<?= htmlspecialchars($job['location']) ?>" required>
        </div>

        <div class="form-group">
            <label>Job Description</label>
            <textarea name="description" rows="5" required><?= htmlspecialchars($job['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Category</label>
            <input type="text" name="category" value="<?= htmlspecialchars($job['category']) ?>">
        </div>

        <div class="form-group">
            <label>Requirements</label>
            <textarea name="requirement" rows="4"><?= htmlspecialchars($job['requirement']) ?></textarea>
        </div>

        <div class="form-group">
            <label>Current Thumbnail</label>
            <?php if (!empty($job['thumbnail'])): ?>
                <img src="<?= htmlspecialchars($job['thumbnail']) ?>" alt="Thumbnail" class="preview">
            <?php else: ?>
                <p>No thumbnail uploaded.</p>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Upload New Thumbnail (optional)</label>
            <input type="file" name="new_thumbnail">
            <small>Allowed: jpg, jpeg, png, gif</small>
        </div>

        <button type="submit" name="save_update">Update Job</button>
    </form>
</div>

<?php include "footer.php"; ?>

</body>
</html>
