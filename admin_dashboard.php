<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include "db_connect.php";

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch admin information
$admin_id = $_SESSION['admin_id'];
$sql = "SELECT username, email FROM admins WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Update admin info if form submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['admin_name'];
    $email = $_POST['admin_email'];
    $password = $_POST['admin_password'];

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE admins SET username = ?, email = ?, password = ? WHERE id = ?");
        $update->bind_param("sssi", $username, $email, $hashed_password, $admin_id);
    } else {
        $update = $conn->prepare("UPDATE admins SET username = ?, email = ? WHERE id = ?");
        $update->bind_param("ssi", $username, $email, $admin_id);
    }

    if ($update->execute()) {
        $_SESSION['admin_username'] = $username; // Update session value
        $_SESSION['message'] = "Information updated successfully.";
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Update failed!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: #f4f6f8;
        }

        .navbar {
            background-color: #A4B9BB;
            padding: 15px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar .logo {
            color: black;
            font-size: 24px;
            font-weight: bold;
        }

        .navbar ul {
            list-style: none;
            display: flex;
            align-items: center;
        }

        .navbar ul li {
            margin-left: 30px;
        }

        .navbar ul li a {
            color: black;
            text-decoration: none;
            font-size: 18px;
            transition: color 0.3s;
        }

        .navbar ul li a:hover {
            color: #555555;
        }

        .profile-dropdown {
            position: relative;
            display: inline-block;
        }

        .profile-icon {
            width: 40px;
            height: 40px;
            background-color: #78909D;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
            cursor: pointer;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 50px;
            right: 0;
            background-color: #fff;
            min-width: 160px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            z-index: 1;
        }

        .dropdown-menu a {
            color: #333;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            border-bottom: 1px solid #eee;
            font-size: 16px;
        }

        .dropdown-menu a:last-child,
        .dropdown-menu a:first-child {
            border-bottom: none;
            color: black;
        }

        .dropdown-menu a:hover {
            background-color: #A4B9BB;
            color: black;
        }

        .main-content {
            padding: 40px;
            text-align: center;
        }

        .main-content h1 {
            color: #34495e;
            font-size: 36px;
            margin-bottom: 20px;
        }

        input {
            padding: 10px;
            width: 300px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            padding: 10px 20px;
            background-color: #78909C;
            color: black;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover{
            background-color: #607D8B;
            color: white;
        }

        .back-link {
            position: absolute;
            top: 500px;
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
<a href="index.php" class="back-link">← Back to home page</a>
    <nav class="navbar">
        <div class="logo">Admin Dashboard</div>
        <ul>
            <li><a href="user_control.php">Users</a></li>
            <li><a href="paper_control.php">Papers</a></li>
            <li><a href="post_control.php">Forums</a></li>
            <li><a href="job_control.php">Jobs</a></li>
            <li><a href="newsletter_admin.php">Subscribers</a></li>
            <li>
                <div class="profile-dropdown" id="profileDropdown">
                    <div class="profile-icon" id="profileIcon"><?php echo strtoupper(substr($_SESSION['admin_username'], 0, 1)); ?></div>
                    <div class="dropdown-menu" id="dropdownMenu">
                        <a href="recruit_admin.php">Recruit Admin</a>
                        <a href="admin_logout.php">Logout</a>
                    </div>
                </div>
            </li>
        </ul>
    </nav>

    <div class="main-content" id="welcomeSection">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</h1>
        <p>Select an option from the navigation bar to manage the system.</p>
    </div>

    <div class="main-content" id="adminInfoForm" style="display:none;">
        <h1>Admin Information Form</h1>
        <form action="" method="POST">
            <div>
                <input type="text" name="admin_name" value="<?php echo htmlspecialchars($admin['username']); ?>" placeholder="Admin Name">
            </div>
            <div>
                <input type="email" name="admin_email" value="<?php echo htmlspecialchars($admin['email']); ?>" placeholder="Admin Email">
            </div>
            <div>
                <input type="password" name="admin_password" placeholder="New Password (leave blank to keep old)">
            </div>
            <div>
                <button type="submit">Update Info</button>
            </div>
        </form>
    </div>

    <script>
        const profileIcon = document.getElementById('profileIcon');
        const dropdownMenu = document.getElementById('dropdownMenu');

        profileIcon.addEventListener('click', function (event) {
            event.stopPropagation();
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        });

        document.addEventListener('click', function () {
            dropdownMenu.style.display = 'none';
        });

        setTimeout(function() {
            document.getElementById('welcomeSection').style.display = 'none';
            document.getElementById('adminInfoForm').style.display = 'block';
        }, 4000);
    </script>

</body>
</html>
