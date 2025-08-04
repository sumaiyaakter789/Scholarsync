<?php
session_start();
include "db_connect.php"; // database connection

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];

// Fetch user ID from username
$user_query = "SELECT id FROM users WHERE username = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("s", $username);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

$user_id = $user['id']; // now we have user_id

// Handle form submission for adding a research paper
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $file = $_FILES['paper'];
    $category = isset($_POST['category']) ? $_POST['category'] : null;
    $thumbnail = isset($_FILES['thumbnail']) ? $_FILES['thumbnail'] : null;

    if (empty($title) || empty($description)) {
        $error_message = "Title and Description are required!";
    } elseif (empty($category)) {
        $error_message = "Please select a category for your paper!";
    } else {
        if ($file['error'] == UPLOAD_ERR_OK) {
            $fileName = $file['name'];
            $fileTmpName = $file['tmp_name'];
            $fileType = $file['type'];

            $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (!in_array($fileType, $allowedTypes)) {
                $error_message = "Only PDF or Word documents are allowed!";
            } else {
                $uploadDir = 'uploads/research_papers/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $filePath = $uploadDir . uniqid() . "_" . basename($fileName);

                if (move_uploaded_file($fileTmpName, $filePath)) {
                    
                    // Handle thumbnail upload if exists
                    $thumbnailPath = null;
                    if ($thumbnail && $thumbnail['error'] == UPLOAD_ERR_OK) {
                        $thumbTmpName = $thumbnail['tmp_name'];
                        $thumbName = $thumbnail['name'];
                        $thumbType = $thumbnail['type'];
                        $allowedThumbTypes = ['image/jpeg', 'image/png', 'image/gif'];

                        if (in_array($thumbType, $allowedThumbTypes)) {
                            $thumbDir = 'uploads/thumbnails/';
                            if (!is_dir($thumbDir)) {
                                mkdir($thumbDir, 0777, true);
                            }
                            $thumbnailPath = $thumbDir . uniqid() . "_" . basename($thumbName);

                            if (!move_uploaded_file($thumbTmpName, $thumbnailPath)) {
                                $error_message = "Failed to upload the thumbnail!";
                            }
                        } else {
                            $error_message = "Only JPG, PNG, or GIF thumbnails are allowed!";
                        }
                    }

                    if (!isset($error_message)) {
                        // Insert into database with optional thumbnail
                        $insert_sql = "INSERT INTO research_papers (user_id, title, description, category, file_path, thumbnail) VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($insert_sql);
                        $stmt->bind_param("isssss", $user_id, $title, $description, $category, $filePath, $thumbnailPath);
                        if ($stmt->execute()) {
                            $success_message = "Research paper added successfully!";
                        } else {
                            $error_message = "Failed to save the paper in the database!";
                        }
                    }
                } else {
                    $error_message = "Failed to upload the file!";
                }
            }
        } else {
            $error_message = "No file uploaded or there was an upload error!";
        }
    }
}

include "header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Research Paper</title>
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

        input, textarea, select {
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
            transition: 0.3s;
            display: block;
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
    <h2>Add Your Research Paper</h2>
    
    <?php if (isset($error_message)) { echo "<p class='error-message'>$error_message</p>"; } ?>
    <?php if (isset($success_message)) { echo "<p class='success-message'>$success_message</p>"; } ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Research Paper Title</label>
            <input type="text" id="title" name="title" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" required></textarea>
        </div>

        <div class="form-group">
            <label for="category">Select Research Category</label>
            <select name="category" id="category" required>
                <option value="">--Select Category--</option>
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

        <div class="form-group">
            <label for="paper">Upload Paper (PDF/Word)</label>
            <input type="file" id="paper" name="paper" accept=".pdf,.doc,.docx" required>
        </div>

        <div class="form-group">
            <label for="thumbnail">Upload Thumbnail (optional)</label>
            <input type="file" id="thumbnail" name="thumbnail" accept=".jpg,.jpeg,.png,.gif">
            <small>Allowed types: JPG, PNG, GIF</small>
        </div>

        <button type="submit">Submit Paper</button>
    </form>
</div>

</body>
<?php include "footer.php"; ?>
</html>
