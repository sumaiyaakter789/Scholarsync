<?php
session_start();
include "db_connect.php";

// Fetch top 3 contributors
$query = "
    SELECT 
        u.id,
        u.username,
        u.email,
        COUNT(DISTINCT rp.id) AS total_papers,
        COUNT(DISTINCT j.id) AS total_jobs,
        COUNT(DISTINCT fp.id) AS total_forum_posts,
        COUNT(DISTINCT gc.id) AS total_group_chats,
        (
            COUNT(DISTINCT rp.id) + 
            COUNT(DISTINCT j.id) + 
            COUNT(DISTINCT fp.id) + 
            COUNT(DISTINCT gc.id)
        ) AS total_contributions
    FROM users u
    LEFT JOIN research_papers rp ON rp.user_id = u.id AND rp.status = 'approved'
    LEFT JOIN jobs j ON j.user_id = u.id AND j.status = 'approved'
    LEFT JOIN forum_posts fp ON fp.user_id = u.id AND fp.status = 'approved'
    LEFT JOIN group_messages gc ON gc.user_id = u.id
    GROUP BY u.id
    ORDER BY total_contributions DESC
    LIMIT 3
";

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Top 3 Contributors</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #ECEFF1;
            display: flex;
            flex-direction: column;
        }
        .container {
            flex: 1;
            max-width: 1000px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            text-align: center;
        }
        h2 {
            color: #37474F;
            margin-bottom: 40px;
        }
        .card-row {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .card {
            background: #f7f9fb;
            border-radius: 15px;
            padding: 20px;
            width: 280px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: scale(1.03);
        }
        .medal {
            font-size: 2.2em;
            margin-bottom: 10px;
        }
        .gold { color: #FFD700; }
        .silver { color: #C0C0C0; }
        .bronze { color: #CD7F32; }
        .username {
            font-size: 1.3em;
            font-weight: bold;
            color: #37474F;
        }
        .email {
            font-size: 0.9em;
            color: #607D8B;
            margin-bottom: 15px;
        }
        .stats {
            text-align: left;
            font-size: 0.95em;
            margin-bottom: 15px;
        }
        .stats p {
            margin: 4px 0;
        }
        .badge {
            display: inline-block;
            background: linear-gradient(135deg, #0288D1, #00BCD4);
            color: white;
            padding: 10px 18px;
            font-size: 1.1em;
            border-radius: 30px;
            font-weight: bold;
            box-shadow: 0 3px 6px rgba(0,0,0,0.2);
        }
        .gold-badge {
            background: linear-gradient(135deg, #FFD700, #FFC107);
            color: #333;
        }
        .silver-badge {
            background: linear-gradient(135deg, #C0C0C0, #B0BEC5);
            color: #333;
        }
        .bronze-badge {
            background: linear-gradient(135deg, #CD7F32, #A1887F);
            color: #fff;
        }
        footer {
            text-align: center;
            padding: 20px;
            background: #37474F;
            color: white;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
<?php include "header.php"; ?>

<div class="container">
    <h2>🏆 Top 3 Contributors</h2>
    <div class="card-row">
        <?php
        $place = 1;
        while ($row = $result->fetch_assoc()):
            $medal = $badgeClass = "";
            if ($place == 1) {
                $medal = '<span class="medal gold">🥇</span>';
                $badgeClass = "badge gold-badge";
            } elseif ($place == 2) {
                $medal = '<span class="medal silver">🥈</span>';
                $badgeClass = "badge silver-badge";
            } elseif ($place == 3) {
                $medal = '<span class="medal bronze">🥉</span>';
                $badgeClass = "badge bronze-badge";
            }
        ?>
        <div class="card">
            <?= $medal ?>
            <div class="username"><?= htmlspecialchars($row['username']) ?></div>
            <div class="email"><?= htmlspecialchars($row['email']) ?></div>
            <div class="stats">
                <p><strong>Papers:</strong> <?= $row['total_papers'] ?></p>
                <p><strong>Jobs:</strong> <?= $row['total_jobs'] ?></p>
                <p><strong>Forum Posts:</strong> <?= $row['total_forum_posts'] ?></p>
                <p><strong>Group Chats:</strong> <?= $row['total_group_chats'] ?></p>
            </div>
            <div class="<?= $badgeClass ?>">Total: <?= $row['total_contributions'] ?></div>
        </div>
        <?php
            $place++;
        endwhile;
        ?>
    </div>
</div>

<?php include "footer.php"; ?>
</body>
</html>
