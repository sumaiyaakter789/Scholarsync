<?php
// Include database connection
include 'db_connect.php';
session_start();

// Approve Paper
if (isset($_POST['approve'])) {
    $paper_id = $_POST['paper_id'];
    $update_sql = "UPDATE research_papers SET status = 'approved' WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $paper_id);
    if ($update_stmt->execute()) {
        echo "<script>alert('Paper approved successfully!');</script>";
    } else {
        echo "<script>alert('Failed to approve paper.');</script>";
    }
}

// Decline Paper (Delete it)
if (isset($_POST['decline'])) {
    $paper_id = $_POST['paper_id'];
    $delete_sql = "DELETE FROM research_papers WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $paper_id);
    if ($delete_stmt->execute()) {
        echo "<script>alert('Paper declined successfully!');</script>";
    } else {
        echo "<script>alert('Failed to decline paper.');</script>";
    }
}

// Fetch papers with 'pending' status
$sql = "SELECT * FROM research_papers WHERE status = 'pending' ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Research Papers</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .paper-container {
            background-color: white;
            padding: 15px;
            margin: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 5px;
            width: calc(33.33% - 10px);
            box-sizing: border-box;
        }

        .paper-container h3 {
            margin-top: 0;
            font-size: 18px;
        }

        .paper-container p {
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

        .papers-wrapper {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
        }

        @media (max-width: 768px) {
            .paper-container {
                width: calc(50% - 10px);
            }
        }

        @media (max-width: 480px) {
            .paper-container {
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

    <h1>Manage Research Papers</h1>

    <div class="papers-wrapper">
        <?php
        if ($result->num_rows > 0) {
            while ($paper = $result->fetch_assoc()) {
                echo '<div class="paper-container">';
                echo '<h3>' . htmlspecialchars($paper['title']) . '</h3>';
                echo '<p><strong>Category:</strong> ' . htmlspecialchars($paper['category']) . '</p>';

                // Display the thumbnail if available
                if (!empty($paper['thumbnail'])) {
                    echo '<p><strong>Thumbnail:</strong></p>';
                    echo '<img src="' . htmlspecialchars($paper['thumbnail']) . '" alt="Paper Thumbnail" class="thumbnail-img">';
                }

                echo '<p><strong>Description:</strong> ' . nl2br(htmlspecialchars($paper['description'])) . '</p>';
                echo '<p><strong>Uploaded on:</strong> ' . $paper['created_at'] . '</p>';

                // Approve and Decline buttons
                echo '<div class="buttons">';
                echo '<form method="POST" action="" style="display:inline;">
                        <input type="hidden" name="paper_id" value="' . $paper['id'] . '">
                        <button type="submit" name="approve" class="approve-btn">Approve</button>
                    </form>';
                echo '<form method="POST" action="" style="display:inline;">
                        <input type="hidden" name="paper_id" value="' . $paper['id'] . '">
                        <button type="submit" name="decline" class="decline-btn">Decline</button>
                    </form>';
                echo '</div>';

                echo '</div>';
            }
        } else {
            echo '<p>No pending research papers for approval.</p>';
        }
        ?>
    </div>

</body>
</html>
