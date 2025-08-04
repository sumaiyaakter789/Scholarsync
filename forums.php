<?php
session_start();
include 'db_connect.php';

// Highlight ID from URL (like post10)
$highlight_id = $_GET['highlight_id'] ?? '';

// Handle filtering
$filter_username = $_GET['username'] ?? '';
$filter_date = $_GET['date'] ?? '';

$sql = "SELECT forum_posts.*, users.username 
        FROM forum_posts 
        JOIN users ON forum_posts.user_id = users.id 
        WHERE forum_posts.status = 'approved'";
$params = [];

if (!empty($filter_username)) {
    $sql .= " AND users.username LIKE ?";
    $params[] = "%$filter_username%";
}
if (!empty($filter_date)) {
    $sql .= " AND DATE(forum_posts.created_at) = ?";
    $params[] = $filter_date;
}

$sql .= " ORDER BY forum_posts.created_at DESC";
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param(str_repeat("s", count($params)), ...$params);
}
$stmt->execute();
$posts_result = $stmt->get_result();

// Separate highlighted post and others
$highlightedPost = null;
$otherPosts = [];

while ($row = $posts_result->fetch_assoc()) {
    if ($highlight_id === 'post' . $row['id']) {
        $highlightedPost = $row;
    } else {
        $otherPosts[] = $row;
    }
}

$posts = $highlightedPost ? array_merge([$highlightedPost], $otherPosts) : $otherPosts;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forum</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 1000px; margin: 30px auto; padding: 20px; }

        h2 { text-align: center; margin-bottom: 20px; }

        .filter-form {
            background: #fff;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
        }

        .filter-form input[type="text"],
        .filter-form input[type="date"] {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
            width: 30%;
            min-width: 180px;
        }

        .filter-form button {
            padding: 10px 20px;
            background-color: #78909C;
            color: black;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .filter-form button:hover {
            background-color: #607d8b;
            color: white;
        }

        .grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
            gap: 20px; 
        }

        .card {
            position: relative;
            background: #fff; 
            padding: 20px; 
            border-radius: 12px; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: auto;
        }

        /* Highlight style */
        .highlight {
            background-color: #e0f7fa;
            border: 2px solid #00796b;
            box-shadow: 0 4px 12px rgba(0, 121, 107, 0.4);
        }

        .three-dots {
            position: absolute;
            bottom: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 22px;
            user-select: none;
            color: black;
        }

        .hidden-menu {
            display: none;
            position: absolute;
            bottom: 35px;
            right: 10px;
            background: #f9f9f9;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
            z-index: 10;
        }

        .hidden-menu a {
            display: block;
            padding: 5px 10px;
            text-decoration: none;
            color: black;
            font-size: 14px;
        }

        .hidden-menu a:hover {
            background: #e0e0e0;
            border-radius: 5px;
        }

        .card-title { font-size: 20px; font-weight: bold; margin-bottom: 10px; }
        .card-category { font-size: 16px; color: #666; margin-bottom: 10px; }
        .card-description { font-size: 14px; margin-bottom: 15px; color: #333; }
        .card-author { font-size: 14px; color: #999; margin-bottom: 15px; }

        .card-links {
            margin-top: 10px;
        }
        .card-links a, .card-links form button {
            display: inline-block;
            margin: 5px 5px 0 0;
            padding: 8px 12px;
            font-size: 14px;
            border-radius: 6px;
            text-decoration: none;
            background: #78909C;
            color: black;
            border: none;
            cursor: pointer;
        }
        .card-links a:hover, .card-links form button:hover {
            background: #607d8b;
            color: white;
        }

        .card-thumbnail {
            width: 98%;
            height: 200px;
            overflow: hidden;
            margin-bottom: 15px;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card-thumbnail img {
            object-fit: cover;
            width: 100%;
            height: 100%;
        }

        .review-section { margin-top: 10px; }
        .star {
            font-size: 24px;
            color: gray;
            cursor: pointer;
        }

        .review-form {
            margin-top: 15px;
        }

        .review-form textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-bottom: 10px;
            resize: none;
        }

        .review-form button {
            padding: 8px 16px;
            background-color: #78909C;
            color: black;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }

        .review-form button:hover {
            background-color: #607d8b;
            color: white;
        }

        .comment-box {
            background: #f5f5f5;
            padding: 12px 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .comment-meta {
            color: #888;
            font-size: 13px;
            margin-bottom: 5px;
        }

        .comment-action-form {
            margin-top: 6px;
        }

        .comment-action-btn {
            display: inline-block;
            margin-right: 10px;
            padding: 6px 12px;
            font-size: 13px;
            border-radius: 6px;
            background-color: #78909C;
            color: black;
            border: none;
            cursor: pointer;
        }

        .comment-action-btn:hover {
            background-color: #607d8b;
            color: white;
        }

        .reply-section {
            margin-top: 12px;
            padding-left: 20px;
            border-left: 2px solid #ccc;
        }

        .reply-textarea {
            width: 100%;
            border-radius: 6px;
            padding: 6px;
            margin-top: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
            resize: none;
        }

        .reply-submit-btn {
            padding: 6px 14px;
            background-color: #78909C;
            color: black;
            border: none;
            border-radius: 6px;
            margin-top: 6px;
            cursor: pointer;
        }

        .reply-submit-btn:hover {
            background-color: #607d8b;
            color: white;
        }

    </style>
</head>
<body>
<?php include "header.php"; ?>
    <div class="container">
        <h2>Forum Posts</h2>

        <form class="filter-form" method="GET">
            <input type="text" name="username" placeholder="Filter by Username" value="<?= htmlspecialchars($filter_username) ?>">
            <input type="date" name="date" value="<?= htmlspecialchars($filter_date) ?>">
            <button type="submit">Filter</button>
        </form>

        <div class="grid">
        <?php foreach ($posts as $row) : ?>
            <?php
                $isHighlight = ($highlight_id === 'post' . $row['id']);
                $cardClass = 'card' . ($isHighlight ? ' highlight' : '');
            ?>
            <div class="<?= $cardClass ?>">
                <?php if ($row['image_path']) : ?>
                    <div class="card-thumbnail">
                        <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="Post Image">
                    </div>
                <?php endif; ?>

                <div class="card-description"><?= nl2br(htmlspecialchars($row['content'])) ?></div>
                <div class="card-author">Posted by <?= htmlspecialchars($row['username']) ?> on <?= $row['created_at'] ?></div>

                <div class="card-links">
                    <form method="POST" action="react_to_post.php" style="display:inline;">
                        <input type="hidden" name="post_id" value="<?= $row['id'] ?>">
                        <button type="submit" name="reaction" value="like">👍 Like</button>
                        <button type="submit" name="reaction" value="dislike">👎 Dislike</button>
                    </form>
                </div>

                <div class="review-section">
                    <form method="POST" action="submit_comment.php" class="review-form">
                        <input type="hidden" name="post_id" value="<?= $row['id'] ?>">
                        <textarea name="comment" placeholder="Write a comment..."></textarea>
                        <button type="submit">Submit</button>
                    </form>

                    <div style="margin-top: 10px;">
                        <?php
                        $cid = $row['id'];
                        $cstmt = $conn->prepare("SELECT forum_comments.id, forum_comments.comment, forum_comments.created_at, users.username 
                                                FROM forum_comments 
                                                JOIN users ON forum_comments.user_id = users.id 
                                                WHERE post_id = ? 
                                                ORDER BY forum_comments.created_at DESC");
                        $cstmt->bind_param("i", $cid);
                        $cstmt->execute();
                        $comments = $cstmt->get_result();

                        while ($comment = $comments->fetch_assoc()) {
                            $comment_id = $comment['id'];
                        
                            // Count likes/dislikes
                            $rstmt = $conn->prepare("SELECT reaction_type, COUNT(*) as count FROM comment_reactions WHERE comment_id = ? GROUP BY reaction_type");
                            $rstmt->bind_param("i", $comment_id);
                            $rstmt->execute();
                            $rres = $rstmt->get_result();
                        
                            $likes = 0;
                            $dislikes = 0;
                            while ($r = $rres->fetch_assoc()) {
                                if ($r['reaction_type'] === 'like') $likes = $r['count'];
                                if ($r['reaction_type'] === 'dislike') $dislikes = $r['count'];
                            }
                        
                            echo "
                            <div class='comment-box'>
                                <p><strong>" . htmlspecialchars($comment['username']) . ":</strong> " . nl2br(htmlspecialchars($comment['comment'])) . "</p>
                                <div class='comment-meta'>" . $comment['created_at'] . "</div>
                        
                                <form method='POST' action='react_to_comment.php' class='comment-action-form'>
                                    <input type='hidden' name='comment_id' value='$comment_id'>
                                    <button type='submit' name='reaction' value='like' class='comment-action-btn'>👍 $likes</button>
                                    <button type='submit' name='reaction' value='dislike' class='comment-action-btn'>👎 $dislikes</button>
                                </form>
                        
                                <div class='reply-section'>
                            ";
                        
                            // Show replies
                            $reply_stmt = $conn->prepare("SELECT cr.reply, cr.created_at, u.username 
                                                        FROM comment_replies cr 
                                                        JOIN users u ON cr.user_id = u.id 
                                                        WHERE cr.comment_id = ? ORDER BY cr.created_at ASC");
                            $reply_stmt->bind_param("i", $comment_id);
                            $reply_stmt->execute();
                            $reply_res = $reply_stmt->get_result();
                        
                            while ($reply = $reply_res->fetch_assoc()) {
                                echo "
                                    <p style='margin: 5px 0;'>
                                        <strong>" . htmlspecialchars($reply['username']) . ":</strong> " . nl2br(htmlspecialchars($reply['reply'])) . "<br>
                                        <small>" . $reply['created_at'] . "</small>
                                    </p>
                                ";
                            }
                        
                            // Reply form
                            echo "
                                    <form method='POST' action='submit_reply.php'>
                                        <input type='hidden' name='comment_id' value='$comment_id'>
                                        <textarea name='reply' class='reply-textarea' placeholder='Reply to this comment...' rows='1'></textarea>
                                        <button type='submit' class='reply-submit-btn'>Reply</button>
                                    </form>
                                </div> <!-- reply-section -->
                            </div> <!-- comment-box -->
                            ";
                        }                        
                        ?>
                    </div>
                </div>

            </div>
        <?php endforeach; ?>
        </div>
    </div>
<?php include "footer.php"; ?>
</body>
</html>
