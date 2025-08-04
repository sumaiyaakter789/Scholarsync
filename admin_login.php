<?php
session_start();
include 'db_connect.php';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$errorMsg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';

    if (empty($email) || empty($password)) {
        $errorMsg = "Both email and password are required!";
    } else {
        $sql = "SELECT id, username, password FROM admins WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $username, $stored_password);
            $stmt->fetch();

            if (password_verify($password, $stored_password)) {
                // If password is already hashed and matched
                $_SESSION['admin_id'] = $id;
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_email'] = $email;

                header("Location: admin_dashboard.php");
                exit();
            } elseif ($password === $stored_password) {
                // Password was stored as plain text, now hash and update it
                $new_hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $update_sql = "UPDATE admins SET password = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("si", $new_hashed_password, $id);
                $update_stmt->execute();
                $update_stmt->close();

                // Now login the user
                $_SESSION['admin_id'] = $id;
                $_SESSION['admin_username'] = $username;
                $_SESSION['admin_email'] = $email;

                header("Location: admin_dashboard.php");
                exit();
            } else {
                $errorMsg = "Incorrect password!";
            }
        } else {
            $errorMsg = "No admin account found with that email!";
        }
        $stmt->close();
    }
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <style>
       * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', sans-serif;
}

body {
    background: white;
    display: flex;
    justify-content: center;
    align-items: center;
}

.login-container {
    background-color: rgba(164, 185, 187, 0.7); /* Soft color with 70% opacity */
    border-radius: 30px;
    display: flex;
    padding: 10px;
    width: 650px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    margin-top: 140px;
}

.logo-section img {
    width: 220px;
    height: 210px;
    margin-left: -100px;
    margin-top: 120px;
    margin-right: 20px;
    border-radius: 50%;
    opacity: 0.8;
}

.form-section {
    flex: 2;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 20px;
}

.form-title {
    text-align: center;
    margin-bottom: 30px;
    color: #000;
    padding: 10px 0;
    border-radius: 20px;
    margin-left: -70px;
    margin-top: -10px;
}

form {
    display: flex;
    flex-direction: column;
}

form label {
    margin-bottom: 5px;
    font-weight: bold;
    color: #333;
}

form input {
    width: 350px;
    padding: 10px;
    margin-bottom: 20px;
    border: none;
    border-bottom: 1px solid #000;
    background-color: transparent;
    outline: none;
}

::placeholder {
    color: #000;
    opacity: 80%;
}

form button {
    background-color: #78909c;
    color: black;
    padding: 12px;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    font-weight: bold;
    width: 350px;
}

form button:hover {
    background-color: #607D8B;
    color: white;
}

.form-links {
    margin-top: 15px;
    margin-left: 90px;
}

.form-links a {
    color: #003366;
    text-decoration: none;
    margin: 0 8px 5px 0;
    font-size: 14px;
    font-weight: 500;
}

.remember-forgot {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    font-size: 14px;
}

.remember-label {
    display: flex;
    margin: 10px 10px 3px 10px;
    color: #2e2b2b;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 14px;
    padding-left: 2px;
}

.remember-label input {
    accent-color: #78909c;
    width: 16px;
    height: 16px;
    cursor: pointer;
}

.forgot-link {
    color: #195189;
    text-decoration: none;
    transition: color 0.2s ease;
}

.forgot-link:hover {
    text-decoration: underline;
    color: #0f3b66;
}

.message {
    padding: 10px;
    margin-bottom: 15px;
    margin-right: 100px;
    border-radius: 5px;
    font-weight: bold;
}

.error { background-color: #ffd6d6; color: #a30000; }
.success { background-color: #d6ffdb; color: #007a2f; }

.back-link {
            position: absolute;
            top: 20px;
            left: 30px;
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
<a href="index.php" class="back-link">← Back to Home</a>
    <div class="login-container">
        <div class="logo-section">
            <img src="logo.jpg" alt="Admin Logo">
        </div>

        <div class="form-section">
            <h1 class="form-title">Admin Login</h1>

            <?php if (!empty($errorMsg)): ?>
                <div class="message error"><?php echo $errorMsg; ?></div>
            <?php endif; ?>

            <form method="POST" action="admin_login.php">
                <label>Email Address :</label>
                <input type="email" name="email" placeholder="Enter your admin email" required>

                <label>Password :</label>
                <input type="password" name="password" placeholder="Enter your password" required>

                <button type="submit">Login</button>

                <div class="form-links">
                    <p>Not an admin? <a href="login.php">User Login</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
