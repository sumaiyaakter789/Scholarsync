<?php
session_start();
include "db_connect.php";

// Only allow admins (you may add actual role check here)
$result = $conn->query("
    SELECT ns.id, u.username, ns.email, ns.subscribed_at
    FROM newsletter_subscribers ns
    JOIN users u ON ns.user_id = u.id
    ORDER BY ns.subscribed_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Newsletter Subscribers</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #ECEFF1;
            margin: 0;
            padding: 30px;
        }
        .table-container {
            max-width: 900px;
            margin: auto;
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #607D8B;
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
    </style>
</head>
<body>
<a href="admin_dashboard.php" class="back-link">← Back to Dashboard</a>
    <div class="table-container">
        <h2>📋 Newsletter Subscribers</h2>
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Subscribed At</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= $row['subscribed_at'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
