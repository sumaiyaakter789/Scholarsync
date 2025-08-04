<?php
session_start();
include "db_connect.php";

// Get the highlight ID from the URL (to highlight the selected job)
$highlight_id = $_GET['highlight_id'] ?? '';
$highlight_numeric_id = str_replace('job', '', $highlight_id);

// Base query and filtering
$filterQuery = "WHERE j.status = 'approved'";
$params = [];

// Filtering by location
if (!empty($_GET['location'])) {
    $filterQuery .= " AND j.location LIKE ?";
    $params[] = "%" . $_GET['location'] . "%";
}

// Filtering by company
if (!empty($_GET['company'])) {
    $filterQuery .= " AND j.company LIKE ?";
    $params[] = "%" . $_GET['company'] . "%";
}

// Filtering by category
if (!empty($_GET['category'])) {
    $filterQuery .= " AND j.category LIKE ?";
    $params[] = "%" . $_GET['category'] . "%";
}

// Main query (highlighted job appears first)
$query = "SELECT j.*, u.username AS poster_name 
          FROM jobs j 
          JOIN users u ON j.user_id = u.id 
          $filterQuery 
          ORDER BY 
            CASE WHEN j.id = ? THEN 0 ELSE 1 END,
            j.created_at DESC";

$stmt = $conn->prepare($query);
$fullParams = array_merge([$highlight_numeric_id], $params);
$types = str_repeat("s", count($fullParams));
$stmt->bind_param($types, ...$fullParams);
$stmt->execute();
$result = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Jobs</title>
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

        .filter-form input[type="text"] {
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
            padding-bottom: 70px; /* enough space for the button */
            border-radius: 12px; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: block; /* ⬅ CHANGED from flex */
            height: auto;   /* ⬅ Remove fixed height */
        }

        .highlight {
            background-color: #e0f7fa; /* Highlight background */
            border: 2px solid #00796b; /* Border color */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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

        .card-title,
        .card-company,
        .card-location,
        .card-category,
        .card-requirement,
        .card-description,
        .card-author {
            font-size: 14px;
            margin-bottom: 10px;
            color: #333;
        }

        .card-title { font-weight: bold; font-size: 16px; }
        .card-category { font-weight: bold; }
        .card-links a {
            display: inline-block;
            padding: 8px 12px;
            font-size: 14px;
            border-radius: 6px;
            text-decoration: none;
            background: #78909C;
            color: white;
            margin-top: 10px;
        }

        .card-links {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background-color: transparent;
            cursor: pointer; /* optional: transparent background */
        }
        
        .card-links a {
            display: inline-block;
            padding: 8px 12px;
            font-size: 14px;
            border-radius: 6px;
            text-decoration: none;
            background: #78909C;
            color: black;
            transition: background-color 0.3s ease;
        }

        .card-links a:hover {
            background: #607D8B;
            color: white;
        }

        /* Optional: Styling for disabled state */
        .card-links a.disabled {
            background: #CFD8DC;
            color: black;
            pointer-events: none;
        }

        .card-links a.disabled:hover {
            background: #B0BEC5; /* slightly darker on hover */
        }

    </style>
</head>
<body>

<?php include "header.php"; ?>

<div class="container">
    <h2>Available Job Opportunities</h2>

    <!-- Filter Form -->
    <form method="GET" class="filter-form">
        <input type="text" name="location" value="<?php echo htmlspecialchars($_GET['location'] ?? '') ?>" placeholder="Location">
        <input type="text" name="company" value="<?php echo htmlspecialchars($_GET['company'] ?? '') ?>" placeholder="Company">
        <input type="text" name="category" value="<?php echo htmlspecialchars($_GET['category'] ?? '') ?>" placeholder="Category">
        <button type="submit">Filter</button>
    </form>

    <?php if ($result->num_rows === 0): ?>
        <p style="text-align:center; font-size: 18px; color: gray;">No jobs found matching your filters.</p>
    <?php else: ?>
        <div class="grid">
            <?php while($job = $result->fetch_assoc()): ?>
                <div class="card <?= $highlight_id === 'job' . $job['id'] ? 'highlight' : '' ?>">
                    <?php if (!empty($job['thumbnail'])): ?>
                        <div class="card-thumbnail">
                            <img src="<?php echo htmlspecialchars($job['thumbnail']); ?>" alt="Job Thumbnail">
                        </div>
                    <?php endif; ?>

                    <div class="card-title"><strong>Title:</strong> <?php echo htmlspecialchars($job['title']); ?></div>
                    <div class="card-company"><strong>Company:</strong> <?php echo htmlspecialchars($job['company']); ?></div>
                    <div class="card-location"><strong>Location:</strong> <?php echo htmlspecialchars($job['location']); ?></div>
                    <div class="card-category"><strong>Category:</strong> <?php echo htmlspecialchars($job['category']); ?></div>

                    <?php if (!empty($job['requirement'])): ?>
                        <div class="card-requirement"><strong>Requirements:</strong> <?php echo nl2br(htmlspecialchars($job['requirement'])); ?></div>
                    <?php endif; ?>

                    <?php if (!empty($job['description'])): ?>
                        <div class="card-description"><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($job['description'])); ?></div>
                    <?php endif; ?>

                    <div class="card-author"><strong>Posted by:</strong> <?php echo htmlspecialchars($job['poster_name']); ?></div>

                    <div class="card-links">
                        <?php if (!empty($job['apply_link'])): ?>
                            <a href="<?php echo htmlspecialchars($job['apply_link']); ?>" target="_blank">Apply Now</a>
                        <?php elseif (!empty($job['id'])): ?>
                            <a href="apply_job.php?job_id=<?php echo $job['id']; ?>">Apply Now</a>
                        <?php else: ?>
                            <a href="#" class="disabled">Apply Now</a>
                        <?php endif; ?>
                    </div>

                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php include "footer.php"; ?>

</body>
</html>
