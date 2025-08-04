<?php
include "db_connect.php";
include "header.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Research Papers</title>
    <style>
        .paper-card {
            background: #f9f9f9;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0px 2px 8px rgba(0,0,0,0.1);
        }
        .paper-card h3 {
            margin-top: 0;
        }
        .paper-card a {
            color: #1565C0;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>All Research Papers</h1>

    <?php
    // Fetch only papers with 'approved' status
    $sql = "SELECT research_papers.*, users.username 
            FROM research_papers
            JOIN users ON research_papers.user_id = users.id
            WHERE research_papers.status = 'approved'
            ORDER BY research_papers.created_at DESC";

    $result = $conn->query($sql);

    // Check if there are no approved papers
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<div class='paper-card'>";
            echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
            echo "<p><strong>Author:</strong> " . htmlspecialchars($row['username']) . "</p>";
            echo "<p><strong>Category:</strong> " . htmlspecialchars($row['category']) . "</p>";
            echo "<p><strong>Description:</strong> " . htmlspecialchars($row['description']) . "</p>";
            echo "<a href='" . htmlspecialchars($row['file_path']) . "' target='_blank'>Download Paper</a>";
            echo "</div>";
        }
    } else {
        echo "<p>No approved research papers available.</p>";
    }
    ?>

</div>

</body>
<?php include "footer.php"; ?>
</html>
