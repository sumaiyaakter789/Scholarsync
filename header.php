<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ScholarSync</title>

  <style>
    /* --- Previous your CSS stays same --- */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', sans-serif;
    }
    body {
        font-family: 'Segoe UI', sans-serif;
    }
    .topbar {
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 1000;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 30px;
    background-color: #f5f5f5;
    border-bottom: 2px solid #ccc;
}

    .navbar {
        position: fixed;
        top: 80px; /* Adjust depending on your .topbar height */
        width: 100%;
        z-index: 999;
        background-color: #f0f0f0;
        padding: 10px 0;
        border-bottom: 2px solid #ccc;
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .logo img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
    }
    .logo p {
        font-size: 22px;
        font-weight: bold;
        color: #78909C;
    }
    .search {
        display: flex;
        align-items: center;
        gap: 8px;
        background-color:#A4B9BB;
        padding: 5px 15px;
        border-radius: 20px;
        width: 450px;
    }
    .search img {
        width: 20px;
        height: 20px;
    }
    .search p {
        font-size: 16px;
        color:black;
    }
    .search input {
        border: none;
        background-color: transparent; /* no extra background */
        outline: none;
        font-size: 16px;
        color: black;
        width: 100%;
        cursor: text; /* mouse cursor will be text bar */
        opacity: 60%;
    }
    .search ::placeholder{
        opacity: 100%;
        color: #000;
    }
    .button {
        display: flex;
        align-items: center; /* important to align vertically */
        gap: 15px;
    }
    .btn {
        padding: 8px 20px;
        border: none;
        background-color: transparent; /* Make background transparent */
        color: black;
        font-size: 14px;
        font-weight: 600;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px; /* Ensure space between the circle and button text */
        transition: 0.3s;
    }

    .btn1:hover {
        background-color: #78909C;
    }
    .dropdown {
        position: relative;
        display: inline-block;
    }
    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #f9f9f9;
        min-width: 160px;
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
        z-index: 1;
        right: 0;
    }
    .dropdown:hover .dropdown-content {
        display: block;
    }
    .dropdown-content a {
        padding: 8px 16px;
        text-decoration: none;
        display: block;
        font-size: 14px;
        color: #555;
    }
    .dropdown-content a:hover {
        background-color: #ddd;
    }
    /* Add background color to the circle with username letter */
    .circle {
        width: 35px;
        height: 35px;
        color: black; /* Text color inside circle */
        background-color: #A4B9BB !important; /* Background color for the circle */
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: bold;
        border-radius: 50%; /* Makes the shape a circle */
        font-size: 18px;
        text-transform: uppercase;
        border: none; /* No border for the circle */
        margin-right: 10px; /* Optional: for spacing between text and circle */
    }
        .btn1 {
        padding: 8px 20px;
        border: none;
        background-color: #A4B9BB;  /* <-- Add this line */
        color: black;
        font-size: 14px;
        font-weight: 600;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        transition: 0.3s;
    }


    .btn .circle {
        background: none;
    }

    .dropdown > .btn {
        padding: 0;
        background: none;
        border: none;
    }



    body {
        padding-top: 130px; /* Adjust based on .topbar + .navbar total height */
    }
    .navbar ul {
        list-style: none;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 50px;
    }
    .navbar li {
        font-size: 16px;
        font-weight: 500;
        color: #555;
        cursor: pointer;
        transition: 0.3s;
    }
    .navbar li:hover {
        color: #000;
    }

    /* ---- New welcome animation CSS ---- */
    .welcome-message {
        font-size: 16px;
        font-weight: bold;
        color: #78909C;
        animation: slideIn 1s forwards;
        opacity: 0;
    }
    @keyframes slideIn {
        0% {
            transform: translateX(-100%);
            opacity: 0;
        }
        100% {
            transform: translateX(0);
            opacity: 1;
        }
    }
    .navbar ul li a {
        text-decoration: none;
        color: #555;
        transition: 0.3s;
        font-weight: 500;
    }

    .navbar ul li a:hover {
        color: #000;
    }

    .navbar ul li a.active {
        background-color: #A4B9BB;  /* same as your btn1 background */
        color: black !important;
        border-radius: 5px;
        padding: 8px 20px;
        font-weight: 600;
        transition: 0.3s;
    }

    .navbar ul li a.active:hover {
    background-color: #78909C;
    color: black !important;
    }

  </style>

  <script>
    // ---- New welcome animation JS ----
    window.onload = function() {
        const welcomeMessage = document.getElementById('welcomeMessage');
        if (welcomeMessage) {
            welcomeMessage.style.display = 'inline-block';
            setTimeout(() => {
                welcomeMessage.style.display = 'none';
            }, 3000);
        }
    }
  </script>
</head>

<body>

<div class="topbar">
    <a href="index.php" style="text-decoration: none;"><div class="logo">
        <img src="logo.jpg" alt="Logo">
        <p>ScholarSync</p>
    </div></a>
    <div class="search">
        <form action="search.php" method="GET" style="display: flex; align-items: center; width: 100%;">
            <img src="images/search_icon.png" alt="Search Icon" />
            <input type="text" name="q" placeholder="Search" style="flex-grow: 1; border: none; background-color: transparent; outline: none; font-size: 16px; color: black; opacity: 0.6; padding-left: 8px;" />
            <button type="submit" style="display:none;">Search</button>
        </form>
    </div>

    <div class="button">
        <?php if (isset($_SESSION['username'])): ?>
            <!-- New welcome message just before profile -->
            <div id="welcomeMessage" class="welcome-message">
                <?php echo 'Welcome, '; ?>
            </div>

            <div class="dropdown">
                <button class="btn">
                    <span class="circle"><?php echo strtoupper($_SESSION['username'][0]); ?></span> <!-- Show first letter inside circle -->
                </button>
                <div class="dropdown-content">
                    <a href="user_profile.php">Profile</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>

        <?php else: ?>
            <a href="login.php" class="btn1">Login</a>
            <a href="signup.php" class="btn1">Signup</a>
        <?php endif; ?>
    </div>
</div>

<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="navbar">
  <ul>
    <li><a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Home</a></li>
    <li><a href="papers.php" class="<?= $current_page == 'papers.php' ? 'active' : '' ?>">Paper</a></li>
    <li><a href="forums.php" class="<?= $current_page == 'forums.php' ? 'active' : '' ?>">Forum</a></li>
    <li><a href="jobs.php" class="<?= $current_page == 'jobs.php' ? 'active' : '' ?>">Jobs</a></li>
    <li><a href="teams.php" class="<?= $current_page == 'teams.php' ? 'active' : '' ?>">Team</a></li>
    <li><a href="leaderboard.php" class="<?= $current_page == 'leaderboard.php' ? 'active' : '' ?>">Leaderboard</a></li>
  </ul>
</div>

</body>
</html>
