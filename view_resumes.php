<?php
session_start();
include "db_connect.php"; // database connection here

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $job_id = (int)$_GET['id'];

    // Fetch job applications with resumes for the specific job_id
    $sql = "SELECT * FROM job_applications WHERE job_id = ? ORDER BY applied_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    echo "Job ID is missing or invalid.";
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Resumes</title>
    <style>
        /* Add your custom styling here */
        body {
            display: flex;
            justify-content: flex-start; /* Align the content towards the left */
            align-items: flex-start;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            overflow-y: scroll; /* Enable scrolling */
            padding: 20px;
        }

        .resume-container {
            width: 70%;  /* Adjusted width */
            margin-left: auto;
            margin-right: auto;
        }

        .resume-item {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0px 2px 6px rgba(0,0,0,0.1);
        }

        .resume-item h3 {
            margin-top: 0;
        }

        .resume-item a {
            background-color: #78909C; /* Same color as "All Resumes" button */
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
        }

        .resume-item a:hover {
            background-color: #607d8b; /* Hover effect matching button hover */
        }

        h2 {
            text-align: center; /* Center the "All Resumes" text */
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            text-decoration: none;
            color: #3498db;
            font-size: 16px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .confirm-btn {
            background-color: #78909C; /* Same as resume button */
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-family: inherit;
        }

        .confirm-btn:hover {
            background-color: #607d8b; /* Hover effect */
        }

        .confirmed-label {
            margin-left: 10px;
            color: #466391;
            font-weight: bold;
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
<?php include "header.php" ?>
<a href="user_profile.php" class="back-link">← Back to Dashboard</a>

    <div class="resume-container">
        <h2>All Resumes for this Job</h2>
        <?php
        if ($result->num_rows > 0) {
            while ($application = $result->fetch_assoc()) {
                $applicant_name = htmlspecialchars($application['full_name']);
                $resume_path = htmlspecialchars($application['resume_path']);
                $status = $application['status'];
                $file_name = basename($resume_path);

                // Fetch job title associated with the job_id (optional)
                $job_sql = "SELECT title FROM jobs WHERE id = ?";
                $job_stmt = $conn->prepare($job_sql);
                $job_stmt->bind_param("i", $job_id);
                $job_stmt->execute();
                $job_result = $job_stmt->get_result();
                $job_title = ($job_result->num_rows > 0) ? $job_result->fetch_assoc()['title'] : 'Unknown Job';

                echo '<div class="resume-item">';
                echo '<h3>' . $applicant_name . '</h3>';
                echo '<p>Applied for: ' . htmlspecialchars($job_title) . '</p>';
                echo '<p>Resume: 
                        <a href="' . $resume_path . '" target="_blank" download="false">' . $applicant_name . '.pdf</a>';

                if ($status === 'confirmed') {
                    echo '<span style="margin-left: 10px; color: #466391; font-weight: bold;">Confirmed</span>';
                } else {
                    echo '<form method="POST" action="confirm_applicant.php?job_id=' . $job_id . '" style="display:inline; margin-left: 10px;">
                            <input type="hidden" name="application_id" value="' . $application['id'] . '">
                            <button type="submit" class="confirm-btn">Confirm</button>
                        </form>';
                }

                echo '</p>';
                echo '</div>';
            }
        } else {
            echo '<p>No resumes found for this job.</p>';
        }
        ?>

        <!-- Add this inside the <body> tag at the bottom of .resume-container -->

    </div>

</body>
</html>
