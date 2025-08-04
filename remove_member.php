<?php
session_start();
include 'db_connect.php';

if (!isset($_GET['group_id']) || !isset($_SESSION['user_id'])) {
    echo "Invalid access.";
    exit();
}

$group_id = $_GET['group_id'];
$current_user_id = $_SESSION['user_id'];

// Check if the current user is the group creator
$stmt = $conn->prepare("SELECT * FROM groups WHERE id = ? AND created_by = ?");
$stmt->bind_param("ii", $group_id, $current_user_id);
$stmt->execute();
$group = $stmt->get_result()->fetch_assoc();

if (!$group) {
    echo "You do not have permission to remove members from this group.";
    exit();
}

// Fetch group members except the creator
$membersStmt = $conn->prepare("
    SELECT u.id, u.username, u.email 
    FROM group_members gm
    JOIN users u ON gm.user_id = u.id
    WHERE gm.group_id = ? AND gm.user_id != ?
");
$membersStmt->bind_param("ii", $group_id, $current_user_id);
$membersStmt->execute();
$members = $membersStmt->get_result();

// Handle member removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_user_id'])) {
    $remove_user_id = $_POST['remove_user_id'];

    $removeStmt = $conn->prepare("DELETE FROM group_members WHERE group_id = ? AND user_id = ?");
    $removeStmt->bind_param("ii", $group_id, $remove_user_id);
    if ($removeStmt->execute()) {
        echo "<script>alert('Member removed successfully.'); window.location.href='remove_member.php?group_id=$group_id';</script>";
        exit();
    } else {
        echo "Error removing member.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Remove Member - <?php echo htmlspecialchars($group['name']); ?></title>
    <link rel="stylesheet" href="your-existing-style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 50px;
        }

        .container {
            background: #fff;
            padding: 30px;
            width: 500px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h2 {
            margin-bottom: 20px;
        }

        .member-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f9f9f9;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .member-box button {
            background: #d9534f;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
        }

        .member-box button:hover {
            background: #c9302c;
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
    <h2>Remove Members from "<?= htmlspecialchars($group['name']) ?>"</h2>

    <?php if ($members->num_rows > 0): ?>
        <?php while ($member = $members->fetch_assoc()): ?>
            <div class="member-box">
                <div>
                    <strong><?= htmlspecialchars($member['username']) ?></strong><br>
                    <small><?= htmlspecialchars($member['email']) ?></small>
                </div>
                <form method="post" onsubmit="return confirm('Are you sure you want to remove this member?');">
                    <input type="hidden" name="remove_user_id" value="<?= $member['id'] ?>">
                    <button type="submit">Remove</button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No members to remove.</p>
    <?php endif; ?>
</div>

</body>
</html>
