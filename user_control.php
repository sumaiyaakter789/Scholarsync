<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include "db_connect.php";

// Handle banning user
if (isset($_GET['ban']) && is_numeric($_GET['ban'])) {
    $user_id = intval($_GET['ban']);
    $stmt = $conn->prepare("UPDATE users SET status = 'banned' WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    header("Location: user_control.php");
    exit();
}

// Fetch all users
$result = $conn->query("SELECT id, username, email, status FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Control</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
            padding: 40px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #34495e;
        }

        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 15px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #A4B9BB;
            color: black;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .ban-btn {
            padding: 8px 15px;
            background-color: #e74c3c;
            color: black;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .ban-btn:hover {
            background-color: #c0392b;
            color: white;
        }

        .status {
            font-weight: bold;
        }

        .banned {
            color: red;
        }

        .active {
            color: green;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            text-decoration: none;
            color: #3498db;
        }

        th, td {
            padding: 15px;
            border-bottom: 1px solid #ddd;
            text-align: center;
            border-right: 1px solid #ccc;
        }

        th:last-child, td:last-child {
            border-right: none; 
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

<a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>

    <h2>User Management</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td class="status <?= $row['status'] == 'banned' ? 'banned' : 'active' ?>">
                    <?= ucfirst($row['status']) ?>
                </td>
                <td>
                    <?php if ($row['status'] !== 'banned'): ?>
                        <a href="?ban=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to ban this user?')">
                            <button class="ban-btn">Ban</button>
                        </a>
                    <?php else: ?>
                        <em>Banned</em>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

</body>
</html>
