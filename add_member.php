<?php
session_start();
include 'db_connect.php';

if (!isset($_GET['group_id']) || !isset($_SESSION['user_id'])) {
    echo "Invalid access.";
    exit();
}

$group_id = $_GET['group_id'];
$user_id = $_SESSION['user_id'];

// Fetch group name
$stmt = $conn->prepare("SELECT name FROM groups WHERE id = ?");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$result = $stmt->get_result();
$group = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_member_id'])) {
    $new_member_id = $_POST['new_member_id'];

    // Check if already a member
    $checkStmt = $conn->prepare("SELECT * FROM group_members WHERE group_id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $group_id, $new_member_id);
    $checkStmt->execute();
    $exists = $checkStmt->get_result()->fetch_assoc();
    $checkStmt->close();

    if (!$exists) {
        $addStmt = $conn->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
        $addStmt->bind_param("ii", $group_id, $new_member_id);
        $addStmt->execute();
        $addStmt->close();

        echo "<script>alert('Member added successfully'); window.location.href='add_member.php?group_id=$group_id';</script>";
        exit();
    } else {
        echo "<script>alert('User is already a member of this group');</script>";
    }
}

// Get all users not in the group
$userStmt = $conn->prepare("
    SELECT u.id, u.username FROM users u
    WHERE u.id NOT IN (
        SELECT user_id FROM group_members WHERE group_id = ?
    )
");
$userStmt->bind_param("i", $group_id);
$userStmt->execute();
$availableUsers = $userStmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Member to <?= htmlspecialchars($group['name']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background: #f7f7f7;
        }

        .container {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            width: 400px;
            margin: auto;
            box-shadow: 0 5px 10px rgba(0,0,0,0.1);
        }

        h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        select, button {
            padding: 10px;
            font-size: 16px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        button {
            background: #78909C;
            color: black;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background:  #607d8b;
            color:white;
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
        <h2>Add Member to "<?= htmlspecialchars($group['name']) ?>"</h2>

        <form method="POST">
            <select name="new_member_id" required>
                <option value="" disabled selected>Select user to add</option>
                <?php while ($user = $availableUsers->fetch_assoc()): ?>
                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
                <?php endwhile; ?>
            </select>

            <button type="submit">Add Member</button>
        </form>
    </div>
</body>
</html>
