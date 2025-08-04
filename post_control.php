<?php
// Include database connection
include 'db_connect.php';
session_start();

// Approve Forum Post
if (isset($_POST['approve'])) {
    $post_id = $_POST['post_id'];
    $update_sql = "UPDATE forum_posts SET status = 'approved' WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $post_id);
    if ($update_stmt->execute()) {
        echo "<script>alert('Forum post approved successfully!');</script>";
    } else {
        echo "<script>alert('Failed to approve post.');</script>";
    }
}

// Decline Forum Post (Delete)
if (isset($_POST['decline'])) {
    $post_id = $_POST['post_id'];
    $delete_sql = "DELETE FROM forum_posts WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $post_id);
    if ($delete_stmt->execute()) {
        echo "<script>alert('Forum post declined and deleted successfully.');</script>";
    } else {
        echo "<script>alert('Failed to delete post.');</script>";
    }
}

// Fetch posts with 'pending' status
$sql = "SELECT forum_posts.*, users.username FROM forum_posts 
        JOIN users ON forum_posts.user_id = users.id
        WHERE forum_posts.status = 'pending' ORDER BY forum_posts.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Forum Posts</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }

        .post-container {
            background-color: white;
            padding: 15px;
            margin: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-radius: 8px;
            width: calc(33.33% - 20px);
            box-sizing: border-box;
        }

        .post-container h3 {
            margin-top: 0;
            font-size: 18px;
        }

        .post-container p {
            font-size: 14px;
            margin: 5px 0;
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
            border-radius: 5px;
            cursor: pointer;
        }

        .approve-btn {
            background-color: #A4B9BB;
            color: black;
        }

        .decline-btn {
            background-color: #e53935;
            color: white;
        }

        .posts-wrapper {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
        }

        .thumbnail-img {
            max-width: 100%;
            height: auto;
            margin-bottom: 10px;
            border-radius: 6px;
        }

        @media (max-width: 768px) {
            .post-container {
                width: calc(50% - 20px);
            }
        }

        @media (max-width: 480px) {
            .post-container {
                width: 100%;
            }
        }

        .back-link {
            position: absolute;
            top: 8px;
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

<a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>

    <h1>Manage Forum Posts</h1>

    <div class="posts-wrapper">
        <?php
        if ($result->num_rows > 0) {
            while ($post = $result->fetch_assoc()) {
                echo '<div class="post-container">';
                echo '<h3>Posted by: ' . htmlspecialchars($post['username']) . '</h3>';
                echo '<p><strong>Content:</strong><br>' . nl2br(htmlspecialchars($post['content'])) . '</p>';
                
                if (!empty($post['image_path'])) {
                    echo '<img src="' . htmlspecialchars($post['image_path']) . '" class="thumbnail-img" alt="Forum Image">';
                }

                echo '<p><strong>Date:</strong> ' . $post['created_at'] . '</p>';

                echo '<div class="buttons">';
                echo '<form method="POST" action="">
                        <input type="hidden" name="post_id" value="' . $post['id'] . '">
                        <button type="submit" name="approve" class="approve-btn">Approve</button>
                      </form>';
                echo '<form method="POST" action="">
                        <input type="hidden" name="post_id" value="' . $post['id'] . '">
                        <button type="submit" name="decline" class="decline-btn">Decline</button>
                      </form>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p>No forum posts pending approval.</p>';
        }
        ?>
    </div>

</body>
</html>
