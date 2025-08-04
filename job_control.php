<?php
session_start();
include 'db_connect.php';

// Optional: Add your admin check
// if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit(); }

// Approve Job
if (isset($_POST['approve'])) {
    $job_id = $_POST['job_id'];
    $update_sql = "UPDATE jobs SET status = 'approved' WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $job_id);
    if ($update_stmt->execute()) {
        echo "<script>alert('Job approved successfully!');</script>";
    } else {
        echo "<script>alert('Failed to approve job.');</script>";
    }
}

// Decline Job (Delete it)
if (isset($_POST['decline'])) {
    $job_id = $_POST['job_id'];
    $delete_sql = "DELETE FROM jobs WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $job_id);
    if ($delete_stmt->execute()) {
        echo "<script>alert('Job declined successfully!');</script>";
    } else {
        echo "<script>alert('Failed to decline job.');</script>";
    }
}

// Fetch pending jobs
$sql = "SELECT jobs.*, users.username FROM jobs JOIN users ON jobs.user_id = users.id WHERE jobs.status = 'pending' ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Job Posts</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .job-container {
            background-color: white;
            padding: 15px;
            margin: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 5px;
            width: calc(33.33% - 10px);
            box-sizing: border-box;
        }

        .job-container h3 {
            margin-top: 0;
            font-size: 18px;
        }

        .job-container p {
            margin: 5px 0;
            font-size: 14px;
        }

        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .buttons button {
            padding: 8px 16px;
            font-size: 14px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .approve-btn {
            background-color: #A4B9BB;
            color: black;
        }

        .decline-btn {
            background-color: #f00722;
            color: white;
        }

        .jobs-wrapper {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
        }

        @media (max-width: 768px) {
            .job-container {
                width: calc(50% - 10px);
            }
        }

        @media (max-width: 480px) {
            .job-container {
                width: 100%;
            }
        }

        .back-link {
            position: absolute;
            top: 9px;
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

        .thumbnail-img {
            width: 100%;
            max-width: 200px;
            height: auto;
            border-radius: 8px;
            margin-bottom: 10px;
        }

    </style>
</head>
<body>

<a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>
    <h1>Manage Job Posts</h1>

    <div class="jobs-wrapper">
        <?php
        if ($result->num_rows > 0) {
            while ($job = $result->fetch_assoc()) {
                echo '<div class="job-container">';
                echo '<h3>' . htmlspecialchars($job['title']) . '</h3>';
                echo '<p><strong>Company:</strong> ' . htmlspecialchars($job['company']) . '</p>';
                echo '<p><strong>Location:</strong> ' . htmlspecialchars($job['location']) . '</p>';
                echo '<p><strong>Posted By:</strong> ' . htmlspecialchars($job['username']) . '</p>';
                echo '<p><strong>Description:</strong> ' . nl2br(htmlspecialchars($job['description'])) . '</p>';
                echo '<p><strong>Category:</strong> ' . htmlspecialchars($job['category']) . '</p>';

                // Display the thumbnail if available
                if (!empty($job['thumbnail'])) {
                    echo '<p><strong>Thumbnail:</strong></p>';
                    echo '<img src="' . htmlspecialchars($job['thumbnail']) . '" alt="Job Thumbnail" class="thumbnail-img">';
                }

                // Approve and Decline buttons
                echo '<div class="buttons">';
                echo '<form method="POST" action="" style="display:inline;">
                        <input type="hidden" name="job_id" value="' . $job['id'] . '">
                        <button type="submit" name="approve" class="approve-btn">Approve</button>
                    </form>';
                echo '<form method="POST" action="" style="display:inline;">
                        <input type="hidden" name="job_id" value="' . $job['id'] . '">
                        <button type="submit" name="decline" class="decline-btn">Decline</button>
                    </form>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p>No pending job posts for approval.</p>';
        }
        ?>
    </div>

</body>
</html>
