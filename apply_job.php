<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['job_id'])) {
    echo "Invalid job.";
    exit();
}

$job_id = intval($_GET['job_id']);

// Fetch job details
$stmt = $conn->prepare("SELECT * FROM jobs WHERE id = ? AND status = 'approved'");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();
$job = $result->fetch_assoc();

if (!$job) {
    echo "Job not found or not available.";
    exit();
}

$username = $_SESSION['username'];
$user_query = $conn->prepare("SELECT id, email FROM users WHERE username = ?");
$user_query->bind_param("s", $username);
$user_query->execute();
$user_result = $user_query->get_result();
$user_data = $user_result->fetch_assoc();
$user_id = $user_data['id'];
$user_email = $user_data['email'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $cover_letter = trim($_POST['cover_letter']);
    $resume_path = '';

    if (empty($full_name) || empty($phone) || empty($cover_letter)) {
        $error_message = "All fields are required!";
    } else {
        // Handle resume upload
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['resume']['tmp_name'];
            $file_name = basename($_FILES['resume']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['pdf', 'doc', 'docx'];

            if (in_array($file_ext, $allowed_exts)) {
                $new_filename = uniqid("resume_") . '.' . $file_ext;
                $upload_dir = 'uploads/resumes/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $resume_path = $upload_dir . $new_filename;
                move_uploaded_file($file_tmp, $resume_path);
            } else {
                $error_message = "Only PDF, DOC, and DOCX files are allowed for resume.";
            }
        }

        if (!isset($error_message)) {
            $apply_stmt = $conn->prepare("INSERT INTO job_applications (job_id, user_id, full_name, email, phone, resume_path, cover_letter, applied_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $apply_stmt->bind_param("iisssss", $job_id, $user_id, $full_name, $user_email, $phone, $resume_path, $cover_letter);
            if ($apply_stmt->execute()) {
                $success_message = "Application submitted successfully!";

                // 🔔 Notification for applicant
                $notif_msg = "Thank you for applying to " . $job['title'] . " at " . $job['company'] . ".";
                $notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $notif_stmt->bind_param("is", $user_id, $notif_msg);
                $notif_stmt->execute();

                // 🔔 Notification for job poster
                $poster_user_id = $job['user_id'];
                $poster_message = "$username has applied for your job: \"" . $job['title'] . "\" at " . $job['company'] . ".";
                $poster_notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $poster_notif_stmt->bind_param("is", $poster_user_id, $poster_message);
                $poster_notif_stmt->execute();

            } else {
                $error_message = "Failed to submit application.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Apply for Job</title>
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
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="form-container">
    <h2>Apply for: <?php echo htmlspecialchars($job['title']); ?> at <?php echo htmlspecialchars($job['company']); ?></h2>

    <?php if (isset($error_message)) { echo "<p class='error-message'>$error_message</p>"; } ?>
    <?php if (isset($success_message)) { echo "<p class='success-message'>$success_message</p>"; } ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" required>
        </div>

        <div class="form-group">
            <label>Email (auto-filled)</label>
            <input type="email" value="<?php echo htmlspecialchars($user_email); ?>" readonly>
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" required>
        </div>

        <div class="form-group">
            <label for="resume">Upload Resume (PDF, DOC, DOCX)</label>
            <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx" required>
        </div>

        <div class="form-group">
            <label for="cover_letter">Cover Letter</label>
            <textarea name="cover_letter" id="cover_letter" rows="6" required></textarea>
        </div>

        <button type="submit">Submit Application</button>
    </form>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
