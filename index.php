<?php
session_start();

include "header.php";
include "db_connect.php";

$topReviews = [];

$reviewQuery = "
    SELECT r.review, r.rating, r.created_at, u.username, u.occupation, u.institution
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.review IS NOT NULL AND r.review != ''
    ORDER BY r.rating DESC, r.created_at DESC
    LIMIT 5
";

$result = $conn->query($reviewQuery);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $topReviews[] = $row;
    }
}

$user_id = $_SESSION['user_id'] ?? NULL;
$current_email = "";

// Fetch user's current email from database
$email_stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$email_stmt->bind_param("i", $user_id);
$email_stmt->execute();
$email_stmt->bind_result($current_email);
$email_stmt->fetch();
$email_stmt->close();

if (isset($_SESSION['username']) && (!isset($_GET['view']) || $_GET['view'] !== 'guest')) {
    include "db_connect.php";

    $username = $_SESSION['username'];

    // Fetch user interests
    $stmt = $conn->prepare("SELECT interests FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($interests);
    $stmt->fetch();
    $stmt->close();

    // Fetch newsletter email if exists
    $user_id = $_SESSION['user_id'] ?? null;
    if ($user_id) {
        $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($email);
        $stmt->fetch();
        $stmt->close();
    }

    // Continue the rest of the code...
    $papers = [];
    $jobs = [];

    if (!empty($interests)) {
        $interestList = array_map('trim', explode(',', $interests));
        $placeholders = implode(',', array_fill(0, count($interestList), '?'));

        $query = "SELECT * FROM research_papers WHERE status = 'approved' AND category IN ($placeholders) ORDER BY created_at DESC";
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $types = str_repeat("s", count($interestList));
        $stmt->bind_param($types, ...$interestList);
        $stmt->execute();
        $papersResult = $stmt->get_result();

        $jobQuery = "SELECT j.*, u.username AS poster_name 
                     FROM jobs j 
                     JOIN users u ON j.user_id = u.id 
                     WHERE j.status = 'approved' AND j.category IN ($placeholders) 
                     ORDER BY j.created_at DESC";
        $jobStmt = $conn->prepare($jobQuery);
        if ($jobStmt) {
            $jobStmt->bind_param($types, ...$interestList);
            $jobStmt->execute();
            $jobsResult = $jobStmt->get_result();
        }

        $merged = [];
        while ($row = $papersResult->fetch_assoc()) {
            $row['type'] = 'paper';
            $merged[] = $row;
        }
        while ($job = $jobsResult->fetch_assoc()) {
            $job['type'] = 'job';
            $merged[] = $job;
        }

        usort($merged, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
    }
?>

<style>
.paper-list {
    padding: 40px;
    max-width: 1000px;
    margin: 0 auto;
}
.card {
    background-color: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 20px;
}
.thumbnail {
    width: 180px;
    height: 120px;
    object-fit: cover;
    border-radius: 8px;
    flex-shrink: 0;
}
.content {
    flex: 1;
}
.content h3 {
    margin-bottom: 8px;
    color: #2c3e50;
}
.content p {
    color: #555;
    margin: 5px 0;
}
.content a {
    display: inline-block;
    margin-top: 10px;
    background-color: #78909C;
    color: black;
    padding: 8px 14px;
    border-radius: 5px;
    text-decoration: none;
}
.content a:hover {
    background-color: #607D8B;
}
.center-text {
    text-align: center;
}
.button-style {
    background-color: #78909C;
    color: black;
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
    display: inline-block;
}
.button-style:hover {
    background-color: #607D8B;
    color: white;
}
.newsletter {
    max-width: 600px;
    margin: 40px auto;
    background-color: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
}
.newsletter input[type="email"] {
    padding: 12px;
    width: 80%;
    border: 1px solid #ccc;
    border-radius: 8px;
    margin-bottom: 15px;
}
.newsletter button {
    background-color: #78909C;
    color: black;
    border: none;
    padding: 10px 25px;
    border-radius: 6px;
    cursor: pointer;
}
.newsletter button:hover {
    background-color: #607D8B;
    color: white;
}
</style>

<div style="text-align:center; margin: 40px;">
    <a href="?view=guest" class="button-style">🔍 View as Guest</a>
</div>

<div class="paper-list">
    <h2 class="text-2xl font-bold mb-6 center-text">Recent Papers and Jobs Recommended for You</h2>
    <?php if (!empty($merged)): ?>
        <?php foreach ($merged as $item): ?>
            <div class="card">
                <?php if (!empty($item['thumbnail'])): ?>
                    <img src="<?= htmlspecialchars($item['thumbnail']) ?>" alt="Thumbnail" class="thumbnail">
                <?php endif; ?>
                <div class="content">
                    <h3><?= htmlspecialchars($item['title']) ?></h3>
                    <?php if ($item['type'] == 'paper'): ?>
                        <p><strong>Category:</strong> <?= htmlspecialchars($item['category']) ?></p>
                        <p><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                        <a href="view_pdf.php?file=<?= urlencode($item['file_path']) ?>" target="_blank">View Full Paper</a>
                    <?php elseif ($item['type'] == 'job'): ?>
                        <p><strong>Company:</strong> <?= htmlspecialchars($item['company']) ?></p>
                        <p><strong>Location:</strong> <?= htmlspecialchars($item['location']) ?></p>
                        <a href="apply_job.php?job_id=<?= $item['id'] ?>">Apply Now</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No recommendations found based on your interests.</p>
    <?php endif; ?>
</div>

<?php
} else {
?>
<style>
.hero-section {
    position: relative;
    height: 550px;
    overflow: hidden;
    color: white;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
}
.hero-slide {
    position: absolute;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0;
    transition: opacity 0.5s ease-in-out;
}
.hero-slide.active {
    opacity: 1;
}
.hero-content {
    position: relative;
    z-index: 2;
    background: rgba(0,0,0,0.6);
    padding: 30px 50px;
    border-radius: 10px;
}
.hero-content h1 {
    font-size: 36px;
    margin-bottom: 20px;
}
.hero-content p {
    font-size: 18px;
    margin-bottom: 30px;
}
.animated-word {
    opacity: 0;
    display: inline-block;
    transition: opacity 0.1s ease-in-out;
}
.animated-word.show {
    opacity: 1;
}
.button-style {
    background-color: #78909C;
    color: black;
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: bold;
    display: inline-block;
}
.button-style:hover {
    background-color: #607D8B;
    color: white;
}
.guest-section {
    background-color: #fff;
    max-width: 1000px;
    margin: 40px auto;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    text-align: center;
}
.guest-features {
    background: #f9fbfd;
    padding: 60px 30px;
    text-align: center;
}
.guest-features h2 {
    font-size: 32px;
    color: #37474F;
    margin-bottom: 30px;
}
.guest-feature-list {
    max-width: 900px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 30px;
}
.guest-feature {
    background: #ffffff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.07);
    transition: transform 0.2s;
}
.guest-feature:hover {
    transform: translateY(-5px);
}
.guest-feature i {
    font-size: 32px;
    color: #0288D1;
    margin-bottom: 10px;
}
.guest-feature p {
    font-size: 16px;
    color: #555;
}
.cta-button {
    margin-top: 40px;
}
.newsletter {
    max-width: 600px;
    margin: 40px auto;
    background-color: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
}
.newsletter h2{
    color: #607D8B;
}
.newsletter input[type="email"] {
    padding: 12px;
    width: 80%;
    border: 1px solid #ccc;
    border-radius: 8px;
    margin-bottom: 15px;
}
.newsletter button {
    background-color: #78909C;
    color: black;
    border: none;
    padding: 10px 25px;
    border-radius: 6px;
    cursor: pointer;
}
.newsletter button:hover {
    background-color: #607D8B;
    color: white;
}
</style>

<div class="hero-section">
    <img src="images/URL1.jpg" class="hero-slide active" alt="Slide 1">
    <img src="images/URL2.jpg" class="hero-slide" alt="Slide 2">
    <img src="images/URL3.jpg" class="hero-slide" alt="Slide 3">
    <img src="images/URL4.jpg" class="hero-slide" alt="Slide 4">

    <div class="hero-content">
        <h1>Welcome to ScholarSync</h1>
        <p id="animated-paragraph"></p>
        <?php if (isset($_SESSION['username'])): ?>
            <div style="margin-top: 20px;">
                <a href="index.php" class="button-style">🔙 Back to Personalized Feed</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- About Us Section -->
<div style="background-color: #f5f5f5; padding: 50px 20px;">
    <div style="max-width: 800px; margin: auto; text-align: center;">
        <h2 style="color: #37474F;">About ScholarSync</h2>
        <p style="color: #555; font-size: 16px; line-height: 1.6;">
            ScholarSync is your academic companion — helping you discover papers, apply for research jobs, 
            and connect with like-minded scholars worldwide.
        </p>
    </div>
</div>

<!-- Feature Section -->
<div class="guest-features">
    <h2>Why Join ScholarSync?</h2>
    <div class="guest-feature-list">
        <div class="guest-feature">
            <i>📚</i>
            <p>Explore and publish cutting-edge research papers.</p>
        </div>
        <div class="guest-feature">
            <i>💼</i>
            <p>Find job opportunities aligned with your academic interests.</p>
        </div>
        <div class="guest-feature">
            <i>👥</i>
            <p>Join discussion groups and grow your academic network.</p>
        </div>
        <div class="guest-feature">
            <i>⭐</i>
            <p>Get recognized through contributor leaderboards.</p>
        </div>
        <div class="guest-feature">
            <i>🧠</i>
            <p>Exchange feedback and grow together with peers.</p>
        </div>
        <div class="guest-feature">
            <i>🎓</i>
            <p>Collaborate with scholars across institutions worldwide.</p>
        </div>
    </div>

    <div class="cta-button">
        <a href="signup.php" class="button-style">🚀 Join ScholarSync Now</a>
    </div>
</div>

<div class="newsletter">
    <h2>📩 Subscribe to Our Newsletter</h2>
    <p>Stay updated with research, jobs, and community news.</p>
    <form action="newsletter_submit.php" method="POST">
        <input type="email" name="new_email" required value="<?= htmlspecialchars($current_email) ?>">
        <br>
        <div style="margin-top: 15px;">
            <button type="submit" name="subscribe" style="margin-right: 10px;">Subscribe</button>
        </div>
    </form>
</div>

<!-- Testimonials Section -->
<div style="padding: 50px 20px; background-color: #ffffff;">
    <div style="max-width: 1000px; margin: auto;">
        <h2 class="center-text" style="color: #37474F;">What Scholars Are Saying</h2>
        <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 30px; margin-top: 30px;">
            <div style="flex: 1 1 300px; background: #f0f0f0; padding: 20px; border-radius: 10px;">
                <p>"ScholarSync helped me get recognized for my research across institutions."</p>
                <p><strong>– Nafisa A., Postgrad Researcher</strong></p>
            </div>
            <div style="flex: 1 1 300px; background: #f0f0f0; padding: 20px; border-radius: 10px;">
                <p>"The jobs and papers match exactly what I'm interested in. I love it!"</p>
                <p><strong>– Omar K., Final Year Student</strong></p>
            </div>
        </div>
    </div>
</div>

<!-- Top Reviews Section -->
<div style="background-color: #f5f5f5; padding: 60px 20px;">
    <div style="max-width: 1000px; margin: auto; text-align: center;">
        <h2 style="color: #37474F;">💬 Top Reviews from Scholars</h2>
        <?php if (!empty($topReviews)): ?>
            <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 30px; margin-top: 30px;">
                <?php foreach ($topReviews as $review): ?>
                    <div style="flex: 1 1 280px; background: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.08); text-align: left;">
                        <p style="font-style: italic; color: #555;">"<?= htmlspecialchars($review['review']) ?>"</p>
                        <p style="margin-top: 10px;"><strong><?= htmlspecialchars($review['username']) ?></strong><br>
                            <?= htmlspecialchars($review['occupation'] ?? '') ?><br>
                            <?= htmlspecialchars($review['institution'] ?? '') ?>
                        </p>
                        <p style="margin-top: 5px; color: #FFA000;">
                            <?php for ($i = 0; $i < (int)$review['rating']; $i++) echo '⭐'; ?>
                        </p>
                        <p style="font-size: 12px; color: #999;"><?= date('F j, Y', strtotime($review['created_at'])) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="margin-top: 20px;">No reviews available yet.</p>
        <?php endif; ?>
    </div>
</div>


<script>
let slides = document.querySelectorAll('.hero-slide');
let current = 0;
setInterval(() => {
    slides[current].classList.remove('active');
    current = (current + 1) % slides.length;
    slides[current].classList.add('active');
}, 1500);

const sentence = "Explore research papers, find job opportunities, and grow with a collaborative academic community.";
const paragraph = document.getElementById("animated-paragraph");
const parts = sentence.match(/(\S+|\s+)/g);

parts.forEach((part, index) => {
    const span = document.createElement("span");
    span.className = "animated-word";
    span.innerHTML = part.trim() === "" ? "&nbsp;" : part;
    paragraph.appendChild(span);
    setTimeout(() => {
        span.classList.add("show");
    }, index * 50);
});
</script>
<?php } ?>

<?php include "footer.php"; ?>
