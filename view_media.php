<?php
session_start();
include 'db_connect.php';

if (!isset($_GET['group_id']) || !isset($_SESSION['user_id'])) {
    echo "Invalid access.";
    exit();
}

$group_id = $_GET['group_id'];
$user_id = $_SESSION['user_id'];

// Check if user is a member of the group
$checkStmt = $conn->prepare("SELECT * FROM group_members WHERE group_id = ? AND user_id = ?");
$checkStmt->bind_param("ii", $group_id, $user_id);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows == 0) {
    echo "You are not a member of this group.";
    exit();
}

// Fetch group name
$groupStmt = $conn->prepare("SELECT name FROM groups WHERE id = ?");
$groupStmt->bind_param("i", $group_id);
$groupStmt->execute();
$groupResult = $groupStmt->get_result()->fetch_assoc();
$group_name = $groupResult['name'];

// Fetch media files
$mediaStmt = $conn->prepare("SELECT * FROM group_media WHERE group_id = ? ORDER BY uploaded_at DESC");
$mediaStmt->bind_param("i", $group_id);
$mediaStmt->execute();
$mediaResult = $mediaStmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Media - <?= htmlspecialchars($group_name) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 50px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-bottom: 20px;
        }

        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 20px;
        }

        .media-item {
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 10px;
            background: #f9f9f9;
            text-align: center;
        }

        .media-item img, .media-item video {
            max-width: 100%;
            max-height: 150px;
            border-radius: 8px;
        }

        .media-item a {
            display: block;
            margin-top: 8px;
            text-decoration: none;
            color: #0066cc;
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

<a href="group_view.php?group_id=<?= $group_id ?>" class="back-link">← Back to Group</a>

<div class="container">
    <h2>Shared Media in "<?= htmlspecialchars($group_name) ?>"</h2>

    <?php if ($mediaResult->num_rows > 0): ?>
        <div class="media-grid">
            <?php while ($media = $mediaResult->fetch_assoc()): ?>
                <div class="media-item">
                    <?php
                    $file = $media['file_path'];
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                    $isVideo = in_array($ext, ['mp4', 'webm']);
                    $isDoc = in_array($ext, ['pdf', 'docx', 'pptx', 'xlsx']);
                    ?>

                    <?php if ($isImage): ?>
                        <img src="<?= htmlspecialchars($file) ?>" alt="Image">
                        <a href="<?= htmlspecialchars($file) ?>" target="_blank">🔍 View Full Image</a>
                    <?php elseif ($isVideo): ?>
                        <video controls>
                            <source src="<?= htmlspecialchars($file) ?>" type="video/<?= $ext ?>">
                            Your browser does not support the video tag.
                        </video>
                        <a href="<?= htmlspecialchars($file) ?>" target="_blank">🎬 Watch Full Video</a>
                    <?php elseif ($isDoc): ?>
                        <a href="<?= htmlspecialchars($file) ?>" target="_blank">📄 <?= basename($file) ?></a>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($file) ?>" download>📎 <?= basename($file) ?></a>
                    <?php endif; ?>

                    <small>Uploaded on <?= date("M d, Y H:i", strtotime($media['uploaded_at'])) ?></small>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No media shared yet.</p>
    <?php endif; ?>
</div>

</body>
</html>
