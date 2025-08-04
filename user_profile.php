<?php
session_start();
include "db_connect.php"; // database connection here

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Handle form submission FIRST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // === 1. Handle Job Application Confirmation ===
    $application_id = $_POST['application_id'] ?? null;

    if ($application_id) {
        $update_sql = "UPDATE job_applications SET status = 'confirmed' WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $application_id);
        if ($update_stmt->execute()) {
            $user_sql = "SELECT user_id FROM job_applications WHERE id = ?";
            $user_stmt = $conn->prepare($user_sql);
            $user_stmt->bind_param("i", $application_id);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();
            $user_row = $user_result->fetch_assoc();

            if ($user_row) {
                $user_id = $user_row['user_id'];
                $title = "Application Confirmed";
                $message = "Congratulations! Your job application has been confirmed.";
                $created_at = date("Y-m-d H:i:s");

                $insert_sql = "INSERT INTO notifications (user_id, title, message, created_at) VALUES (?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("isss", $user_id, $title, $message, $created_at);
                $insert_stmt->execute();
            }
        }
    }

    // === 2. Handle Profile Update ===
    $new_username = trim($_POST['nickname']);
    $occupation = trim($_POST['occupation']);
    $interests = trim($_POST['interests']);
    $institution = trim($_POST['institution']);
    $new_password = trim($_POST['password']);

    $username = $_SESSION['username'];
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET username=?, occupation=?, interests=?, institution=?, password=? WHERE id=?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sssssi", $new_username, $occupation, $interests, $institution, $hashed_password, $user['id']);
    } else {
        $update_sql = "UPDATE users SET username=?, occupation=?, interests=?, institution=? WHERE id=?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssssi", $new_username, $occupation, $interests, $institution, $user['id']);
    }

    if ($update_stmt->execute()) {
        $_SESSION['username'] = $new_username;
        header("Location: user_profile.php");
        exit();
    } else {
        echo "Failed to update!";
    }
}

// THEN include header
include "header.php";

// Now fetch user data to show in form
$username = $_SESSION['username'];
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$userId = $user['id']; // Get logged-in user ID
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>

    <style>
        .profile-container {
            margin: 50px auto;
            max-width: 1200px;
            background-color: #f5f5f5;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
        }

        .profile-section {
            margin-bottom: 30px;
        }

        .profile-section h2 {
            font-size: 24px;
            color: #333;
        }

        .profile-section p {
            font-size: 18px;
            color: #555;
            margin-top: 8px;
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
            transition: 0.3s;
            display: block;
            width: 100%;
            margin-top: 10px;
        }

        button:hover {
            background-color: #607d8b;
            color: white;
        }

        /* Tab styling */
        .tab-buttons {
            display: flex;
            margin-bottom: 20px;
        }

        .tab-button {
            flex: 1;
            padding: 10px;
            font-size: 18px;
            background-color: #e0e0e0;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .tab-button.active {
            background-color: #78909C;
            color: black;
        }

        .tab-button:not(.active):hover {
            background-color: #ccc;
        }

        .tab-button:not(.active){
            color: black;
        }

        /* Hide research section initially */
        #researchSection {
            display: none;
        }
        .add-research-btn{
            background-color: #78909C;
            width: 230px;
            margin-left: 160px;
        }
        .profile-section p {
            font-size: 18px;
            color: #555;
            margin-top: 8px;
            word-wrap: break-word;
            white-space: normal;  /* Ensure the text wraps to the next line */
        }
        form {
            display: block;  /* Ensure each form is on a new line */
            margin-top: 10px;
            width: 150px;
        }
        .form-group{
            width: 540px;
        }

        #notificationsSection {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        #notificationsSection h2 {
            font-size: 24px;
            color: #333;
        }

        #notificationsSection ul {
            list-style-type: none;
            padding: 0;
        }

        #notificationsSection li {
            background: #fff;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0px 2px 6px rgba(0, 0, 0, 0.1);
        }

        .notification-item {
            background: #fff;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            transition: box-shadow 0.3s ease;
            cursor: pointer;
        }

        .notification-item.unread {
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.2);
            font-weight: bold;
        }

        .notification-item.read {
            box-shadow: none;
            font-weight: normal;
        }


        #no-notifications-message {
            color: #777;
            font-style: italic;
        }

        .view-group-btn {
            background-color: #78909C;
            color: black;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-family: inherit;
            display: inline-block;
        }

        .view-group-btn:hover {
            background-color: #607d8b;
            color: white;
        }
        
    </style>
</head>
<body>

    <div class="profile-container">
        <!-- Tab buttons -->
        <div class="tab-buttons">
            <button id="overviewBtn" class="tab-button active">Overview</button>
            <button id="researchBtn" class="tab-button">Research</button>
            <button id="jobsBtn" class="tab-button">Jobs</button>
            <button id="notificationsBtn" class="tab-button">Notifications</button>
            <button id="teamsBtn" class="tab-button">Teams</button>
            <button id="achievementsBtn" class="tab-button">Achievements</button>
            <button id="forumsBtn" class="tab-button">Forums</button>
        </div>


        <!-- Overview Section -->
        <div id="overviewSection">
            <div class="profile-section">
                <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                <p>
                    <?php 
                        if (!empty($user['occupation'])) {
                            echo htmlspecialchars($user['occupation']);
                        } else {
                            echo "Add your occupation";
                        }
                    ?>
                </p>
            </div>

            <div class="profile-section">
                <h2>Interests</h2>
                <p>
                    <?php 
                        if (!empty($user['interests'])) {
                            echo htmlspecialchars($user['interests']);
                        } else {
                            echo "No interests added yet.";
                        }
                    ?>
                </p>
            </div>

            <div class="profile-section">
                <h2>Institution</h2>
                <p>
                    <?php 
                        if (!empty($user['institution'])) {
                            echo htmlspecialchars($user['institution']);
                        } else {
                            echo "Add your institution.";
                        }
                    ?>
                </p>
            </div>

            <!-- Update Form -->
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nickname">Nickname</label>
                    <input type="text" id="nickname" name="nickname" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="occupation">Occupation</label>
                    <input type="text" id="occupation" name="occupation" value="<?php echo htmlspecialchars($user['occupation']); ?>">
                </div>

                <div class="form-group">
                    <label for="interests">Interests</label>
                    <textarea id="interests" name="interests" rows="4"><?php echo htmlspecialchars($user['interests']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="institution">Institution</label>
                    <input type="text" id="institution" name="institution" value="<?php echo htmlspecialchars($user['institution']); ?>">
                </div>

                <div class="form-group">
                    <label for="password">New Password (leave blank if not changing)</label>
                    <input type="password" id="password" name="password">
                </div>

                <button type="submit">Save Changes</button>
            </form>
        </div>

        <div id="researchSection">
            <div class="profile-section">
                <h2>Your Approved Research Papers</h2>

                <?php
                // Fetch approved research papers for the current user
                $user_id = $user['id']; // fetched already at top
                $paper_sql = "SELECT * FROM research_papers WHERE user_id = ? AND status = 'approved' ORDER BY created_at DESC";
                $paper_stmt = $conn->prepare($paper_sql);
                $paper_stmt->bind_param("i", $user_id);
                $paper_stmt->execute();
                $paper_result = $paper_stmt->get_result();

                if ($paper_result->num_rows > 0) {
                    while ($paper = $paper_result->fetch_assoc()) {
                        echo '<div style="background: #fff; padding: 20px; margin-bottom: 20px; border-radius: 10px; box-shadow: 0px 2px 6px rgba(0,0,0,0.1);">';
                        echo '<h3 style="margin-top: 0;">' . htmlspecialchars($paper['title']) . '</h3>';
                        echo '<p><strong>Category:</strong> ' . htmlspecialchars($paper['category']) . '</p>';
                        echo '<p>' . nl2br(htmlspecialchars($paper['description'])) . '</p>';

                        // Download Paper link
                        echo '<a href="' . htmlspecialchars($paper['file_path']) . '" download style="color: #78909C; font-weight: bold; margin-right: 15px;">Download Paper</a>';

                        // Read Online link (assuming the file can be viewed in the browser)
                        echo '<a href="' . htmlspecialchars($paper['file_path']) . '" target="_blank" style="color: #78909C; font-weight: bold;">Read Online</a>';

                        // Add Update and Delete buttons in the same container
                        echo '<div style="display: flex; gap: 10px; margin-top: 10px;">';
                        echo '<form method="POST" action="paper_update.php" style="display:inline;">
                                <input type="hidden" name="paper_id" value="' . $paper['id'] . '">
                                <button type="submit" name="update" style="background-color: #78909C; color: white; padding: 10px 20px; border-radius: 7px; border: none;">Update</button>
                            </form>';

                        echo '<form method="POST" action="paper_delete.php" style="display:inline;">
                                <input type="hidden" name="paper_id" value="' . $paper['id'] . '">
                                <button type="submit" name="delete" style="background-color: #FF6347; color: white; padding: 10px 20px; border-radius: 7px; border: none;">Delete</button>
                            </form>';
                        echo '</div>';

                        echo '</div>';
                    }
                } else {
                    echo '<p>No approved research papers added yet.</p>';
                }
                ?>

                <!-- Button to add research paper -->
                <button id="addResearchPaperBtn" class="add-research-btn">Add Your Research Paper</button>
            </div>
        </div>

        <div id="jobsSection" style="display:none;">
            <div class="profile-section">
                <h2>Your Job Posts</h2>

                <?php
                // Fetch job posts for this user
                $job_sql = "SELECT * FROM jobs WHERE user_id = ? ORDER BY created_at DESC";
                $job_stmt = $conn->prepare($job_sql);
                $job_stmt->bind_param("i", $user['id']);
                $job_stmt->execute();
                $job_result = $job_stmt->get_result();

                if ($job_result->num_rows > 0) {
                    while ($job = $job_result->fetch_assoc()) {
                        echo '<div style="background: #fff; padding: 20px; margin-bottom: 20px; border-radius: 10px; box-shadow: 0px 2px 6px rgba(0,0,0,0.1);">';
                        echo '<h3>' . htmlspecialchars($job['title']) . '</h3>';
                        echo '<p><strong>Location:</strong> ' . htmlspecialchars($job['location']) . '</p>';
                        echo '<p>' . nl2br(htmlspecialchars($job['description'])) . '</p>';

                        // Update Button
                        echo '<form method="POST" action="job_edit.php" style="display:inline-block; margin-right: 10px;">
                                <input type="hidden" name="job_id" value="' . $job['id'] . '">
                                <button type="submit" class="add-research-btn">Update</button>
                            </form>';

                        // Delete Button
                        echo '<form method="POST" action="job_delete.php" style="display:inline-block; margin-left: 80px; margin-right: 10px;">
                                <input type="hidden" name="job_id" value="' . $job['id'] . '">
                                <button type="submit" class="add-research-btn" style="background-color: #FF6347;">Delete</button>
                            </form>';

                        // All Resumes Button
                        echo '<button class="add-research-btn" onclick="window.location.href=\'view_resumes.php?id=' . htmlspecialchars($job['id']) . '\'">All Resumes</button>';

                        echo '</div>';
                    }
                } else {
                    echo '<p>No jobs posted yet.</p>';
                }
                ?>

                <button id="addJobBtn" class="add-research-btn">Post a New Job</button>
            </div>
        </div>



            <!-- Notifications Section -->
        <div id="notificationsSection" style="display:none;">
            <div class="profile-section">
                <h2>Notifications</h2>
                <?php
                    echo '<ul style="list-style-type: none; padding-left: 0;">';

                    // 1. Show existing notifications
                    $notification_sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
                    $notification_stmt = $conn->prepare($notification_sql);
                    $notification_stmt->bind_param("i", $user['id']);
                    $notification_stmt->execute();
                    $notification_result = $notification_stmt->get_result();

                    $has_notifications = false;

                    if ($notification_result->num_rows > 0) {
                        while ($notification = $notification_result->fetch_assoc()) {
                            $has_notifications = true;
                    
                            $class = $notification['is_read'] ? 'notification-item read' : 'notification-item unread';
                            echo '<li class="' . $class . '" data-id="' . $notification['id'] . '">';
                            echo htmlspecialchars($notification['message']) . ' <small>(' . $notification['created_at'] . ')</small>';
                    
                            if (!empty($notification['resume_path'])) {
                                echo ' <a href="' . htmlspecialchars($notification['resume_path']) . '" target="_blank" style="color: #1976d2; text-decoration: underline;">View Resume</a>';
                            }
                    
                            echo '</li>';
                        }
                    }                    

                    // 2. Show confirmed applications with styled message
                    $confirmed_sql = "
                       SELECT job_applications.id, job_applications.is_read, jobs.title, jobs.company 
                       FROM job_applications 
                       JOIN jobs ON job_applications.job_id = jobs.id 
                       WHERE job_applications.user_id = ? AND job_applications.status = 'confirmed'

                    ";
                    $confirmed_stmt = $conn->prepare($confirmed_sql);
                    $confirmed_stmt->bind_param("i", $user['id']);
                    $confirmed_stmt->execute();
                    $confirmed_result = $confirmed_stmt->get_result();

                    if ($confirmed_result->num_rows > 0) {
                        $has_notifications = true;
                        while ($row = $confirmed_result->fetch_assoc()) {
                            $class = $row['is_read'] ? 'notification-item read' : 'notification-item unread';
                            echo '<li class="' . $class . '" data-id="' . $row['id'] . '" data-type="confirmed">';
                            echo 'Your application for <strong>' . htmlspecialchars($row['title']) . '</strong> at <strong>' . htmlspecialchars($row['company']) . '</strong> has been <strong>confirmed</strong>! 🎉';
                            echo '</li>';
                        }
                    }                    

                    if (!$has_notifications) {
                        echo '<p id="no-notifications-message" style="font-size: 16px; color: #666;">No notifications yet.</p>';
                    }

                    echo '</ul>';
                ?>
            </div>
        </div>

        <!-- Teams Section -->
        <div id="teamsSection" style="display:none;">
            <h2 class="text-2xl font-bold mb-4">Your Teams</h2>

            <?php
            $group_sql = "SELECT g.id, g.name, g.thumbnail_path 
                        FROM groups g
                        INNER JOIN group_members gm ON g.id = gm.group_id
                        WHERE gm.user_id = ?";
            $group_stmt = $conn->prepare($group_sql);
            $group_stmt->bind_param("i", $userId);
            $group_stmt->execute();
            $group_result = $group_stmt->get_result();

            if ($group_result->num_rows > 0): ?>
                <div class="flex flex-wrap gap-4">
                    <?php while ($group = $group_result->fetch_assoc()): ?>
                        <div class="bg-white rounded-xl shadow-md p-4 w-64">
                            <img src="<?= htmlspecialchars($group['thumbnail_path']) ?>" alt="Group Thumbnail" class="w-full h-40 object-cover rounded-lg mb-3">
                            <h3 class="text-lg font-semibold mb-2"><?= htmlspecialchars($group['name']) ?></h3>
                            <a href="group_view.php?group_id=<?= $group['id'] ?>" class="view-group-btn">View Group</a>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-600">You are not a member of any groups yet.</p>
            <?php endif; ?>
        </div>

        
      <!-- Achievements Section -->
        <div id="achievementsSection" style="display:none; padding: 30px; background: #f9f9f9;">
            <h2 style="font-size: 32px; margin-bottom: 40px; text-align: center; color: #333;">📈 Your Growth Over Time</h2>

            <div style="width: 100%; max-width: 1200px; height: 600px; margin: 0 auto; box-shadow: 0 4px 20px rgba(0,0,0,0.2); background: #fff; border-radius: 10px; padding: 20px;">
                <canvas id="ratingChart"></canvas>
            </div>

            <?php
                $userId = $user['id'];
                $sql = "
                    SELECT 
                    DATE(r.created_at) AS review_day,
                    COUNT(*) AS review_count,
                    AVG(r.rating) AS avg_rating
                    FROM reviews r
                    JOIN research_papers p ON r.paper_id = p.id
                    WHERE p.user_id = ?
                    GROUP BY review_day
                    ORDER BY review_day ASC
                ";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();

                $rawData = [];
                while ($row = $result->fetch_assoc()) {
                    $rawData[$row['review_day']] = [
                        'avg' => round($row['avg_rating'], 2),
                        'count' => $row['review_count']
                    ];
                }

                // Fill in missing dates
                $labels = [];
                $avgRatings = [];
                $reviewCounts = [];

                if (!empty($rawData)) {
                    $start = new DateTime(array_key_first($rawData));
                    $end = new DateTime(array_key_last($rawData));
                    $interval = new DateInterval('P1D');
                    $period = new DatePeriod($start, $interval, $end->modify('+1 day'));

                    foreach ($period as $date) {
                        $d = $date->format('Y-m-d');
                        $labels[] = $d;
                        if (isset($rawData[$d])) {
                            $avgRatings[] = $rawData[$d]['avg'];
                            $reviewCounts[] = $rawData[$d]['count'];
                        } else {
                            $avgRatings[] = 0;
                            $reviewCounts[] = 0;
                        }
                    }
                }
            ?>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

            <script>
                const labels = <?php echo json_encode($labels); ?>;
                const avgRatings = <?php echo json_encode($avgRatings); ?>;
                const reviewCounts = <?php echo json_encode($reviewCounts); ?>;

                const ctx = document.getElementById('ratingChart').getContext('2d');
                const ratingChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: '📊 Average Rating',
                                data: avgRatings,
                                borderColor: '#bf2517',
                                backgroundColor: 'rgba(0, 123, 255, 0.15)',
                                fill: true,
                                tension: 0.5,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                borderWidth: 3
                            },
                            {
                                label: '📝 Review Count',
                                data: reviewCounts,
                                borderColor: '#0f0be6',
                                backgroundColor: 'rgba(40, 167, 69, 0.15)',
                                fill: true,
                                tension: 0.5,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                borderWidth: 3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        scales: {
                            x: {
                                ticks: {
                                    autoSkip: true,
                                    maxTicksLimit: 20,
                                    color: '#222',
                                    font: { size: 14 }
                                },
                                grid: {
                                    color: 'rgba(0,0,0,0.1)'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Rating & Count Scale',
                                    color: '#222',
                                    font: { size: 16 }
                                },
                                ticks: {
                                    color: '#222',
                                    font: { size: 14 }
                                },
                                grid: {
                                    color: 'rgba(0,0,0,0.1)'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    font: { size: 14 },
                                    color: '#222'
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                backgroundColor: '#333',
                                titleFont: { size: 14 },
                                bodyFont: { size: 14 }
                            }
                        }
                    }
                });
            </script>
        </div>


        <div id="forumsSection" style="display:none;">
            <div class="profile-section">
                <h2>Your Forum Posts</h2>

                <?php
                // Fetch forum posts for the current user
                $forum_sql = "SELECT * FROM forum_posts WHERE user_id = ? ORDER BY created_at DESC";
                $forum_stmt = $conn->prepare($forum_sql);
                $forum_stmt->bind_param("i", $user['id']);
                $forum_stmt->execute();
                $forum_result = $forum_stmt->get_result();

                if ($forum_result->num_rows > 0) {
                    while ($post = $forum_result->fetch_assoc()) {
                        $post_id = $post['id'];

                        echo '<div style="background: #fff; padding: 20px; margin-bottom: 20px; border-radius: 10px; box-shadow: 0px 2px 6px rgba(0,0,0,0.1);">';

                        // Post content
                        echo '<p>' . nl2br(htmlspecialchars($post['content'])) . '</p>';
                        echo '<p style="color: #888; font-size: 14px;">Posted on: ' . $post['created_at'] . '</p>';

                        // Post status
                        echo '<p style="font-weight:bold;">Status: <span style="color: ' .
                            ($post['status'] === 'approved' ? 'green' : ($post['status'] === 'pending' ? '#ff9900' : 'red')) . ';">' .
                            ucfirst($post['status']) . '</span></p>';

                        // ✅ Get reaction counts
                        $reaction_stmt = $conn->prepare("SELECT reaction_type, COUNT(*) as count FROM forum_reactions WHERE post_id = ? GROUP BY reaction_type");
                        $reaction_stmt->bind_param("i", $post_id);
                        $reaction_stmt->execute();
                        $reaction_result = $reaction_stmt->get_result();

                        $likes = 0;
                        $dislikes = 0;
                        while ($reaction = $reaction_result->fetch_assoc()) {
                            if ($reaction['reaction_type'] === 'like') $likes = $reaction['count'];
                            if ($reaction['reaction_type'] === 'dislike') $dislikes = $reaction['count'];
                        }

                        echo '<div style="margin-top: 10px;">';
                        echo '<strong>Reactions:</strong> 👍 ' . $likes . ' | 👎 ' . $dislikes;
                        echo '</div>';

                        // ✅ Show comments
                        $comment_stmt = $conn->prepare("SELECT fc.id, fc.comment, fc.created_at, u.username FROM forum_comments fc JOIN users u ON fc.user_id = u.id WHERE fc.post_id = ? ORDER BY fc.created_at ASC");
                        $comment_stmt->bind_param("i", $post_id);
                        $comment_stmt->execute();
                        $comment_result = $comment_stmt->get_result();
                        
                        echo '<div style="margin-top: 15px;">';
                        echo '<strong>Comments:</strong>';
                        if ($comment_result->num_rows > 0) {
                            while ($comment = $comment_result->fetch_assoc()) {
                                $comment_id = $comment['id'];
                                echo '<div style="background: #f9f9f9; padding: 8px 12px; margin-top: 5px; border-radius: 6px;">';
                                echo '<p style="margin: 0;"><strong>' . htmlspecialchars($comment['username']) . ':</strong> ' . nl2br(htmlspecialchars($comment['comment'])) . '</p>';
                                echo '<small style="color: #999;">' . $comment['created_at'] . '</small>';
                        
                                // Fetch replies for this comment
                                $reply_stmt = $conn->prepare("SELECT cr.reply, cr.created_at, u.username FROM comment_replies cr JOIN users u ON cr.user_id = u.id WHERE cr.comment_id = ? ORDER BY cr.created_at ASC");
                                $reply_stmt->bind_param("i", $comment_id);
                                $reply_stmt->execute();
                                $reply_result = $reply_stmt->get_result();
                        
                                if ($reply_result->num_rows > 0) {
                                    echo '<div style="margin-top: 8px; padding-left: 20px; border-left: 2px solid #ccc;">';
                                    while ($reply = $reply_result->fetch_assoc()) {
                                        echo '<p style="margin: 5px 0;"><strong>' . htmlspecialchars($reply['username']) . ':</strong> ' . nl2br(htmlspecialchars($reply['reply'])) . '<br><small style="color: #999;">' . $reply['created_at'] . '</small></p>';
                                    }
                                    echo '</div>';
                                }
                        
                                echo '</div>'; // end comment box
                            }
                        } else {
                            echo '<p>No comments yet.</p>';
                        }
                        echo '</div>';                        

                        // ✅ Edit/Delete buttons
                        echo '<div style="display: flex; gap: 10px; margin-top: 15px;">';
                        echo '<form method="POST" action="post_edit.php" style="display:inline;">
                                <input type="hidden" name="post_id" value="' . $post_id . '">
                                <button type="submit" name="update" style="background-color: #78909C; color: white; padding: 10px 20px; border-radius: 7px; border: none;">Edit</button>
                            </form>';
                        echo '<form method="POST" action="post_delete.php" onsubmit="return confirm(\'Are you sure you want to delete this post?\')" style="display:inline;">
                                <input type="hidden" name="post_id" value="' . $post_id . '">
                                <button type="submit" name="delete" style="background-color: #FF6347; color: white; padding: 10px 20px; border-radius: 7px; border: none;">Delete</button>
                            </form>';
                        echo '</div>';

                        echo '</div>';
                    }
                } else {
                    echo '<p>No forum posts submitted yet.</p>';
                }
                ?>

                <!-- Button to add forum post -->
                <button onclick="window.location.href='post_forum.php'" class="add-research-btn">Create Forum Post</button>
            </div>
        </div>


    </div>

    <script>
    const overviewBtn = document.getElementById('overviewBtn');
    const researchBtn = document.getElementById('researchBtn');
    const jobsBtn = document.getElementById('jobsBtn');
    const notificationsBtn = document.getElementById('notificationsBtn');
    const teamsBtn = document.getElementById('teamsBtn');
    const achievementsBtn = document.getElementById('achievementsBtn');
    const forumsBtn = document.getElementById('forumsBtn');

    const overviewSection = document.getElementById('overviewSection');
    const researchSection = document.getElementById('researchSection');
    const jobsSection = document.getElementById('jobsSection');
    const notificationsSection = document.getElementById('notificationsSection');
    const teamsSection = document.getElementById('teamsSection');
    const achievementsSection = document.getElementById('achievementsSection');
    const forumsSection = document.getElementById('forumsSection');


    overviewBtn.addEventListener('click', function() {
    overviewSection.style.display = 'block';
    researchSection.style.display = 'none';
    jobsSection.style.display = 'none'; 
    notificationsSection.style.display = 'none';
    teamsSection.style.display = 'none';
    achievementsSection.style.display = 'none';
    forumsSection.style.display = 'none';// hide jobs section too

    overviewBtn.classList.add('active');
    researchBtn.classList.remove('active');
    jobsBtn.classList.remove('active');
    notificationsBtn.classList.remove('active'); 
    teamsBtn.classList.remove('active');
    achievementsBtn.classList.remove('active');
    forumsBtn.classList.remove('active');  // <-- missing line
    });

    researchBtn.addEventListener('click', function() {
        overviewSection.style.display = 'none';
        researchSection.style.display = 'block';
        jobsSection.style.display = 'none';
        notificationsSection.style.display = 'none';
        teamsSection.style.display = 'none';
        achievementsSection.style.display = 'none';
        forumsSection.style.display = 'none'; // hide jobs section too

        researchBtn.classList.add('active');
        overviewBtn.classList.remove('active');
        jobsBtn.classList.remove('active'); 
        notificationsBtn.classList.remove('active');
        teamsBtn.classList.remove('active');
        achievementsBtn.classList.remove('active');  
        forumsBtn.classList.remove('active');   // <-- missing line
    });


    jobsBtn.addEventListener('click', function() {
    overviewSection.style.display = 'none';
    researchSection.style.display = 'none';
    notificationsSection.style.display = 'none';
    jobsSection.style.display = 'block';
    teamsSection.style.display = 'none';
    achievementsSection.style.display = 'none';
    forumsSection.style.display = 'none';

    jobsBtn.classList.add('active');
    overviewBtn.classList.remove('active');
    researchBtn.classList.remove('active');
    notificationsBtn.classList.remove('active');
    teamsBtn.classList.remove('active');
    achievementsBtn.classList.remove('active'); 
    forumsBtn.classList.remove('active');    
    });

    notificationsBtn.addEventListener('click', function() {
    overviewSection.style.display = 'none';
    researchSection.style.display = 'none';
    jobsSection.style.display = 'none';
    notificationsSection.style.display = 'block';
    teamsSection.style.display = 'none';
    achievementsSection.style.display = 'none';
    forumsSection.style.display = 'none'; // Show notifications section

    notificationsBtn.classList.add('active');
    overviewBtn.classList.remove('active');
    researchBtn.classList.remove('active');
    jobsBtn.classList.remove('active');
    teamsBtn.classList.remove('active');
    achievementsBtn.classList.remove('active'); 
    forumsBtn.classList.remove('active');    
    });

    teamsBtn.addEventListener('click', function() {
    overviewSection.style.display = 'none';
    researchSection.style.display = 'none';
    jobsSection.style.display = 'none';
    notificationsSection.style.display = 'none';
    teamsSection.style.display = 'block';
    achievementsSection.style.display = 'none'; 
    forumsSection.style.display = 'none';// Show notifications section

    notificationsBtn.classList.remove('active');
    overviewBtn.classList.remove('active');
    researchBtn.classList.remove('active');
    jobsBtn.classList.remove('active');
    teamsBtn.classList.add('active'); 
    achievementsBtn.classList.remove('active');
    forumsBtn.classList.remove('active');  
    });

    achievementsBtn.addEventListener('click', function() {
    overviewSection.style.display = 'none';
    researchSection.style.display = 'none';
    jobsSection.style.display = 'none';
    notificationsSection.style.display = 'none';
    teamsSection.style.display = 'none';
    achievementsSection.style.display = 'block'; 
    forumsSection.style.display = 'none';// Show notifications section

    notificationsBtn.classList.remove('active');
    overviewBtn.classList.remove('active');
    researchBtn.classList.remove('active');
    jobsBtn.classList.remove('active');
    teamsBtn.classList.remove('active'); 
    achievementsBtn.classList.add('active');
    forumsBtn.classList.remove('active');  
    });

    forumsBtn.addEventListener('click', function() {
    overviewSection.style.display = 'none';
    researchSection.style.display = 'none';
    jobsSection.style.display = 'none';
    notificationsSection.style.display = 'none';
    teamsSection.style.display = 'none';
    achievementsSection.style.display = 'none'; 
    forumsSection.style.display = 'block';// Show notifications section

    notificationsBtn.classList.remove('active');
    overviewBtn.classList.remove('active');
    researchBtn.classList.remove('active');
    jobsBtn.classList.remove('active');
    teamsBtn.classList.remove('active'); 
    achievementsBtn.classList.remove('active');
    forumsBtn.classList.add('active');  
    });


    // Corrected redirection code
    const addResearchPaperBtn = document.getElementById('addResearchPaperBtn');
    addResearchPaperBtn.addEventListener('click', function() {
        window.location.href = "add_paper.php"; 
    });

    document.getElementById('addJobBtn').addEventListener('click', function() {
    window.location.href = "post_job.php";
    });

    document.querySelectorAll('.notification-item.unread').forEach(item => {
        item.addEventListener('click', function () {
            this.classList.remove('unread');
            this.classList.add('read');

            const notificationId = this.dataset.id;
            const notificationType = this.dataset.type || 'normal'; // default normal

            fetch('mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + encodeURIComponent(notificationId) + '&type=' + encodeURIComponent(notificationType),
            });
        });
    });


</script>

</body>
<?php include "footer.php"; ?>
</html>
