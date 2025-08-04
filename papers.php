<?php
session_start();
include "db_connect.php";

// Highlight ID from URL (like paper5)
$highlight_id = $_GET['highlight_id'] ?? '';

// Extract numeric part of highlight_id for ordering
$highlight_numeric_id = 0;
if (preg_match('/paper(\d+)/', $highlight_id, $matches)) {
    $highlight_numeric_id = (int)$matches[1];
}

// Base filter query
$filterQuery = "WHERE p.status = 'approved'";
$params = [];

// Filtering by date
if (!empty($_GET['created_at'])) {
    $filterQuery .= " AND DATE(p.created_at) = ?";
    $params[] = $_GET['created_at'];
}

// Filtering by category
if (!empty($_GET['category'])) {
    $filterQuery .= " AND p.category LIKE ?";
    $params[] = "%" . $_GET['category'] . "%";
}

// Filtering by author (username)
if (!empty($_GET['author'])) {
    $filterQuery .= " AND u.username LIKE ?";
    $params[] = "%" . $_GET['author'] . "%";
}

if ($highlight_numeric_id) {
    // If highlight id present, order by that paper first, then by created_at desc
    $orderBy = "ORDER BY (p.id = ?) DESC, p.created_at DESC";
    $query = "SELECT p.*, u.username AS author_name 
              FROM research_papers p 
              JOIN users u ON p.user_id = u.id 
              $filterQuery 
              $orderBy";

    $stmt = $conn->prepare($query);

    if ($params) {
        $types = str_repeat("s", count($params)) . "i";
        $allParams = array_merge($params, [$highlight_numeric_id]);
        $stmt->bind_param($types, ...$allParams);
    } else {
        // No other filters, only highlight param
        $stmt->bind_param("i", $highlight_numeric_id);
    }
} else {
    // No highlight, normal ordering
    $query = "SELECT p.*, u.username AS author_name 
              FROM research_papers p 
              JOIN users u ON p.user_id = u.id 
              $filterQuery 
              ORDER BY p.created_at DESC";

    $stmt = $conn->prepare($query);

    if ($params) {
        $types = str_repeat("s", count($params));
        $stmt->bind_param($types, ...$params);
    }
}

$stmt->execute();
$result = $stmt->get_result();

function generateCitation($paper, $style = 'APA') {
    $author = htmlspecialchars($paper['author_name']); // Use the alias from your query
    $title = htmlspecialchars($paper['title']);
    $year = date('Y', strtotime($paper['created_at']));

    switch ($style) {
        case 'APA':
        default:
            return "$author. ($year). <i>$title</i>. Retrieved from yoursite.com/view_pdf.php?file=" . urlencode(basename($paper['file_path']));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Research Papers</title>
    <style>
        /* Styles (same as your original) */
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

        .filter-form input[type="text"],
        .filter-form input[type="date"] {
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
            border-radius: 12px; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 450px;
        }

        .three-dots {
            position: absolute;
            bottom: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 22px;
            user-select: none;
            color: black;
        }

        .hidden-menu {
            display: none;
            position: absolute;
            bottom: 35px;
            right: 10px;
            background: #f9f9f9;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
            z-index: 10;
        }

        .hidden-menu a {
            display: block;
            padding: 5px 10px;
            text-decoration: none;
            color: black;
            font-size: 14px;
        }

        .hidden-menu a:hover {
            background: #e0e0e0;
            border-radius: 5px;
        }

        .card-title { font-size: 20px; font-weight: bold; margin-bottom: 10px; }
        .card-category { font-size: 16px; color: #666; margin-bottom: 10px; }
        .card-description { font-size: 14px; margin-bottom: 15px; color: #333; }
        .card-author { font-size: 14px; color: #999; margin-bottom: 15px; }

        .card-links {
            margin-top: 10px;
        }
        .card-links a, .card-links form button {
            display: inline-block;
            margin: 5px 5px 0 0;
            padding: 8px 12px;
            font-size: 14px;
            border-radius: 6px;
            text-decoration: none;
            background: #78909C;
            color: white;
            border: none;
            cursor: pointer;
        }
        .card-links a:hover, .card-links form button:hover {
            background: #607d8b;
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

        .review-section { margin-top: 10px; }
        .star {
            font-size: 24px;
            color: gray;
            cursor: pointer;
        }

        .review-form {
            margin-top: 15px;
            display: none;
        }

        .review-form textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            margin-bottom: 10px;
            resize: none;
        }

        .review-form button {
            padding: 8px 16px;
            background-color: #78909C;
            color: white;
            border-radius: 6px;
            border: none;
            cursor: pointer;
        }

        .review-form button:hover {
            background-color: #607d8b;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 20px;
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.3);
        }
        .modal-content .close {
            float: right;
            font-size: 22px;
            cursor: pointer;
        }

        .card-btn {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 12px;
            font-size: 14px;
            border-radius: 6px;
            text-decoration: none;
            background: #78909C;
            color: black;
            border: none;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .card-btn:hover {
            background: #607d8b;
            color: white;
        }

        .highlight {
            background-color: #e0f7fa;
            border: 2px solid #00796b;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>

<?php include "header.php"; ?>

<div class="container">
    <h2>All Research Papers</h2>

    <!-- Filter Form -->
    <form method="GET" class="filter-form">
        <input type="date" name="created_at" value="<?php echo htmlspecialchars($_GET['created_at'] ?? '') ?>" placeholder="Date">
        <input type="text" name="category" value="<?php echo htmlspecialchars($_GET['category'] ?? '') ?>" placeholder="Category">
        <input type="text" name="author" value="<?php echo htmlspecialchars($_GET['author'] ?? '') ?>" placeholder="Author">
        <button type="submit">Filter</button>
    </form>

    <?php if ($result->num_rows === 0): ?>
        <p style="text-align:center; font-size: 18px; color: gray;">No papers found matching your filters.</p>
    <?php else: ?>
        <div class="grid">
            <?php while($paper = $result->fetch_assoc()):
                $paperId = $paper['id'];
                $highlightClass = ($highlight_id === 'paper' . $paperId) ? 'highlight' : '';
            ?>
               <div class="card <?= $highlightClass ?>" data-paper-id="<?= $paperId ?>">
                    <div class="three-dots" onclick="toggleMenu(this)">&#8942;</div>
                    <div class="hidden-menu">
                        <a href="<?php echo htmlspecialchars($paper['file_path']); ?>" download>Download</a>
                        <a href="view_pdf.php?file=<?php echo urlencode(basename($paper['file_path'])); ?>" target="_blank">Read Online</a>
                    </div>

                    <?php if (!empty($paper['thumbnail'])): ?>
                        <div class="card-thumbnail">
                            <img src="<?php echo htmlspecialchars($paper['thumbnail']); ?>" alt="Thumbnail">
                        </div>
                    <?php endif; ?>

                    <div class="card-title"><?php echo htmlspecialchars($paper['title']); ?></div>
                    <div class="card-category"><?php echo htmlspecialchars($paper['category']); ?></div>
                    <div class="card-description"><?php echo nl2br(htmlspecialchars($paper['description'])); ?></div>
                    <div class="card-author">Author: <?php echo htmlspecialchars($paper['author_name']); ?></div>
                    
                    <div class="review-section">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star" data-rating="<?php echo $i; ?>" onclick="selectRating(this, <?php echo $paperId; ?>)">&#9733;</span>
                        <?php endfor; ?>
                    </div>

                    <div class="review-form" id="review-form-<?php echo $paperId; ?>">
                        <textarea id="review-text-<?php echo $paperId; ?>" placeholder="Write your review..."></textarea>
                        <button onclick="submitReview(<?php echo $paperId; ?>)">Submit Review</button>
                    </div>

                    <!-- Citation Button -->
                    <button class="card-btn" onclick="openCitationModal(<?= $paperId ?>)">📄 Cite</button>

                    <!-- Citation Modal -->
                    <div id="citationModal-<?= $paperId ?>" class="modal" style="display:none;">
                        <div class="modal-content">
                            <span class="close" onclick="closeCitationModal(<?= $paperId ?>)">&times;</span>
                            <h3>Citation (APA Style)</h3>
                            <textarea readonly style="width:100%;height:100px;"><?= generateCitation($paper) ?></textarea>
                            <button class="card-btn"  onclick="copyCitation(<?= $paperId ?>)">Copy</button>
                        </div>
                    </div>

                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php include "footer.php"; ?>

<script>
const selectedRatings = {};

function selectRating(starElement, paperId) {
    const rating = starElement.getAttribute('data-rating');
    selectedRatings[paperId] = rating;

    const card = starElement.closest('.card');
    const stars = card.querySelectorAll('.star');

    stars.forEach(star => {
        star.style.color = (star.getAttribute('data-rating') <= rating) ? 'gold' : 'gray';
    });

    card.querySelector('.review-form').style.display = 'block';
}

function submitReview(paperId) {
    const reviewText = document.getElementById('review-text-' + paperId).value;
    const rating = selectedRatings[paperId];

    if (!rating || reviewText.trim() === "") {
        alert("Please select a rating and write a review.");
        return;
    }

    const formData = new FormData();
    formData.append('paper_id', paperId);
    formData.append('rating', rating);
    formData.append('review', reviewText);

    fetch('review.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                alert('Review submitted successfully!');
                location.reload();
            } else {
                alert('Error submitting review: ' + data.message);
            }
        } catch (e) {
            alert("Invalid response from server.");
        }
    })
    .catch(error => {
        alert("An error occurred while submitting the review.");
    });
}

function toggleMenu(dotElement) {
    const menu = dotElement.nextElementSibling;
    document.querySelectorAll('.hidden-menu').forEach(m => {
        if (m !== menu) m.style.display = 'none';
    });

    menu.style.display = (menu.style.display === "block") ? "none" : "block";
}

window.addEventListener('click', function(e) {
    if (!e.target.matches('.three-dots')) {
        document.querySelectorAll('.hidden-menu').forEach(m => m.style.display = 'none');
    }
});

function openCitationModal(id) {
    document.getElementById("citationModal-" + id).style.display = "block";
}

function closeCitationModal(id) {
    document.getElementById("citationModal-" + id).style.display = "none";
}

function copyCitation(id) {
    const textarea = document.querySelector("#citationModal-" + id + " textarea");
    textarea.select();
    document.execCommand("copy");
    alert("Citation copied!");
}
</script>

</body>
</html>
