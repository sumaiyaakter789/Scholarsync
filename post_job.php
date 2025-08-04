<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];

// Get user ID
$user_query = "SELECT id FROM users WHERE username = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("s", $username);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

$user_id = $user['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $company = trim($_POST['company']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $requirement = trim($_POST['requirement']); // Added requirement field
    $thumbnail_path = "";

    // Validate fields
    if (empty($title) || empty($company) || empty($location) || empty($description) || empty($category) || empty($requirement)) {
        $error_message = "All fields are required!";
    } else {
        // Handle image upload
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['thumbnail']['tmp_name'];
            $file_name = basename($_FILES['thumbnail']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($file_ext, $allowed_exts)) {
                $new_filename = uniqid("job_") . '.' . $file_ext;
                $upload_dir = 'uploads/jobs/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $thumbnail_path = $upload_dir . $new_filename;
                move_uploaded_file($file_tmp, $thumbnail_path);
            } else {
                $error_message = "Only JPG, JPEG, PNG, and GIF files are allowed for thumbnail.";
            }
        }

        if (!isset($error_message)) {
            $insert_sql = "INSERT INTO jobs (user_id, title, company, location, description, thumbnail, category, requirement) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("isssssss", $user_id, $title, $company, $location, $description, $thumbnail_path, $category, $requirement);
            if ($stmt->execute()) {
                $success_message = "Job post submitted for admin approval!";
            } else {
                $error_message = "Failed to submit job post!";
            }
        }
    }
}

include "header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post a New Job</title>
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
            color: black;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
        }
        button:hover {
            background-color: #607d8b;
            color: white;
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

<a href="user_profile.php" class="back-link">← Back to Dashboard</a>

<div class="form-container">
    <h2>Post a New Job</h2>

    <?php if (isset($error_message)) { echo "<p class='error-message'>$error_message</p>"; } ?>
    <?php if (isset($success_message)) { echo "<p class='success-message'>$success_message</p>"; } ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Job Title</label>
            <input type="text" id="title" name="title" required>
        </div>

        <div class="form-group">
            <label for="company">Company Name</label>
            <input type="text" id="company" name="company" required>
        </div>

        <div class="form-group">
            <label for="location">Job Location</label>
            <input type="text" id="location" name="location" required>
        </div>

        <div class="form-group">
            <label for="description">Job Description</label>
            <textarea id="description" name="description" rows="5" required></textarea>
        </div>

        <div class="form-group">
            <label for="requirement">Job Requirements</label>
            <textarea id="requirement" name="requirement" rows="5" required></textarea>
        </div>

        <div class="form-group">
            <label for="thumbnail">Company Logo / Thumbnail (optional)</label>
            <input type="file" id="thumbnail" name="thumbnail" accept="image/*">
        </div>

        <div class="form-group">
            <label for="category">Job Category</label>
            <select id="category" name="category" required>
                <option value="">Select a category</option>
                <option value="Machine Learning">Machine Learning</option>
                <option value="Artificial Intelligence">Artificial Intelligence</option>
                <option value="Cybersecurity">Cybersecurity</option>
                <option value="Data Science">Data Science</option>
                <option value="Internet of Things">Internet of Things</option>
                <option value="Robotics">Robotics</option>
                <option value="Marketing">Marketing</option>
                <option value="Finance">Finance</option>
                <option value="Entrepreneurship">Entrepreneurship</option>
                <option value="Human Resources">Human Resources</option>
                <option value="Economics">Economics</option>
                <option value="History">History</option>
                <option value="Philosophy">Philosophy</option>
                <option value="Literature">Literature</option>
                <option value="Languages">Languages</option>
                <option value="Visual Arts">Visual Arts</option>
                <option value="Medicine">Medicine</option>
                <option value="Nursing">Nursing</option>
                <option value="Public Health">Public Health</option>
                <option value="Biotechnology">Biotechnology</option>
                <option value="Corporate Law">Corporate Law</option>
                <option value="Criminal Law">Criminal Law</option>
                <option value="Environmental Law">Environmental Law</option>
                <option value="Human Rights">Human Rights</option>
            </select>
        </div>

        <button type="submit">Submit Job</button>
    </form>
</div>

</body>
<?php include "footer.php"; ?>
</html>
