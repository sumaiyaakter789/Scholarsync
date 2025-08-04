<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to change the group name.";
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['group_id']) || !is_numeric($_GET['group_id'])) {
    echo "Invalid group ID.";
    exit();
}

$group_id = (int)$_GET['group_id'];

// Check if user is creator of the group
$stmt = $conn->prepare("SELECT name, created_by FROM groups WHERE id = ?");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$stmt->bind_result($current_name, $created_by);
if (!$stmt->fetch()) {
    echo "Group not found.";
    exit();
}
$stmt->close();

if ($created_by != $user_id) {
    echo "You do not have permission to change this group's name.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = trim($_POST['group_name'] ?? '');

    if (empty($new_name)) {
        $error = "Group name cannot be empty.";
    } elseif (strlen($new_name) > 255) {
        $error = "Group name cannot exceed 255 characters.";
    } else {
        $stmt = $conn->prepare("UPDATE groups SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $new_name, $group_id);
        if ($stmt->execute()) {
            header("Location: group_view.php?group_id=$group_id&msg=name_updated");
            exit();
        } else {
            $error = "Failed to update group name in database.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Group Name</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7f9;
            padding: 40px;
        }

        .container {
            max-width: 500px;
            background: #fff;
            padding: 30px;
            margin: 0 auto;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h2 {
            margin-bottom: 20px;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            font-size: 16px;
        }

        button {
            background: #78909C;
            color: black;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #607D8B;
            color: white;
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

        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<a href="group_view.php?group_id=<?= $group_id ?>" class="back-link">← Back to Group</a>
<div class="container">
    <h2>Change Group Name</h2>
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="text" name="group_name" value="<?= htmlspecialchars($current_name) ?>" required maxlength="255">
        <button type="submit">Update Name</button>
    </form>
</div>
</body>
</html>
