<?php
session_start();
include "db_connect.php";

$keyword = trim($_GET['q'] ?? '');
$highlight_id = $_GET['highlight_id'] ?? ''; // The highlight_id passed in the URL

function safeEcho($str) {
    return htmlspecialchars($str);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Search Results</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        h2 { color: #78909C; }
        .section { margin-bottom: 40px; background: #fff; padding: 15px 20px; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        .item {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }
        .item:last-child { border: none; }
        .title { font-size: 18px; font-weight: bold; color: #333; }
        .desc { margin-top: 6px; color: #555; }
        .meta { font-size: 13px; color: #999; margin-top: 3px; }
        a { text-decoration: none; color: #78909C; }
        a:hover { text-decoration: underline; }
        .no-results { color: #888; font-style: italic; }
        .highlight {
            font-weight: bold;
            background-color: #e0f7fa;
            padding: 3px 6px;
            border-radius: 5px;
        }

        .back-link {
            position: absolute;
            top: 20px;
            left: 30px;
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
<a href="group_view.php?group_id=<?= $group_id ?>" class="back-link">← Back to Group</a>
<h1>Search Results for "<?= safeEcho($keyword) ?>"</h1>

<?php if ($keyword === ''): ?>
    <p>Please enter a search keyword.</p>
<?php else: ?>

<?php
$like_keyword = "%$keyword%";

// --- Research Papers ---
$stmt_papers = $conn->prepare("
    SELECT p.id, p.title, p.description, p.category, p.created_at, u.username 
    FROM research_papers p 
    JOIN users u ON p.user_id = u.id 
    WHERE p.status = 'approved' 
      AND (p.title LIKE ? OR p.description LIKE ? OR p.category LIKE ? OR u.username LIKE ?)
    ORDER BY p.created_at DESC
");
$stmt_papers->bind_param("ssss", $like_keyword, $like_keyword, $like_keyword, $like_keyword);
$stmt_papers->execute();
$result_papers = $stmt_papers->get_result();
?>

<div class="section">
    <h2>Research Papers</h2>
    <?php if ($result_papers->num_rows === 0): ?>
        <p class="no-results">No research papers found.</p>
    <?php else: ?>
        <?php while($paper = $result_papers->fetch_assoc()): ?>
            <div class="item">
                <a href="papers.php?file=<?= urlencode(basename($paper['title'])) ?>&highlight_id=paper<?= $paper['id'] ?>"
                   class="title <?= $highlight_id === 'paper' . $paper['id'] ? 'highlight' : '' ?>">
                   <?= safeEcho($paper['title']) ?>
                </a>
                <div class="desc"><?= nl2br(safeEcho(substr($paper['description'], 0, 150))) ?>...</div>
                <div class="meta">Category: <?= safeEcho($paper['category']) ?> | Author: <?= safeEcho($paper['username']) ?> | <?= date('Y-m-d', strtotime($paper['created_at'])) ?></div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<?php
// --- Jobs ---
$stmt_jobs = $conn->prepare("
    SELECT j.id, j.title, j.company, j.location, j.description, j.created_at, u.username 
    FROM jobs j 
    JOIN users u ON j.user_id = u.id 
    WHERE j.status = 'approved' 
      AND (j.title LIKE ? OR j.company LIKE ? OR j.location LIKE ? OR j.description LIKE ? OR j.category LIKE ? OR u.username LIKE ?)
    ORDER BY j.created_at DESC
");
$stmt_jobs->bind_param("ssssss", $like_keyword, $like_keyword, $like_keyword, $like_keyword, $like_keyword, $like_keyword);
$stmt_jobs->execute();
$result_jobs = $stmt_jobs->get_result();
?>

<div class="section">
    <h2>Jobs</h2>
    <?php if ($result_jobs->num_rows === 0): ?>
        <p class="no-results">No jobs found.</p>
    <?php else: ?>
        <?php while($job = $result_jobs->fetch_assoc()): ?>
            <div class="item">
                <a href="jobs.php?id=<?= (int)$job['id'] ?>&highlight_id=job<?= $job['id'] ?>"
                   class="title <?= $highlight_id === 'job' . $job['id'] ? 'highlight' : '' ?>">
                   <?= safeEcho($job['title']) ?>
                </a>
                <div class="desc"><?= nl2br(safeEcho(substr($job['description'], 0, 150))) ?>...</div>
                <div class="meta">Company: <?= safeEcho($job['company']) ?> | Location: <?= safeEcho($job['location']) ?> | Posted by: <?= safeEcho($job['username']) ?> | <?= date('Y-m-d', strtotime($job['created_at'])) ?></div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<?php
// --- Forum Posts ---
$stmt_posts = $conn->prepare("
    SELECT f.id, f.content, f.image_path, f.created_at, u.username 
    FROM forum_posts f
    JOIN users u ON f.user_id = u.id
    WHERE f.status = 'approved'
      AND (f.content LIKE ? OR u.username LIKE ?)
    ORDER BY f.created_at DESC
");
$stmt_posts->bind_param("ss", $like_keyword, $like_keyword);
$stmt_posts->execute();
$result_posts = $stmt_posts->get_result();
?>


<div class="section">
    <h2>Forum Posts</h2>
    <?php if ($result_posts->num_rows === 0): ?>
        <p class="no-results">No forum posts found.</p>
    <?php else: ?>
        <?php while($post = $result_posts->fetch_assoc()): ?>
            <div class="item">
                <a href="forums.php?id=<?= (int)$post['id'] ?>&highlight_id=post<?= $post['id'] ?>"
                   class="title <?= $highlight_id === 'post' . $post['id'] ? 'highlight' : '' ?>">
                   Post by <?= safeEcho($post['username']) ?>
                </a>
                <div class="desc"><?= nl2br(safeEcho(substr($post['content'], 0, 150))) ?>...</div>
                <div class="meta"><?= date('Y-m-d', strtotime($post['created_at'])) ?></div>
                <?php if ($post['image_path']): ?>
                    <img src="<?= safeEcho($post['image_path']) ?>" alt="Post Image" style="max-width:200px; margin-top: 8px; border-radius:8px;">
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<?php endif; ?>

</body>
</html>
