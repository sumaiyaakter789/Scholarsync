<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $new_email = trim($_POST['new_email']);

    if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format!";
        exit();
    }

    $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
    $stmt->bind_param("si", $new_email, $user_id);

    if ($stmt->execute()) {
        $_SESSION['email'] = $new_email; // Optional
        header("Location: index.php");
        exit();
    } else {
        echo "Failed to update email.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Email</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; }
        .container {
            max-width: 500px;
            margin: 80px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        label, input, button {
            display: block;
            width: 100%;
            margin-bottom: 15px;
        }
        button {
            background: #78909C;
            color: black;
            padding: 10px;
            border: none;
            border-radius: 6px;
        }
        button :hover{
            background-color: #607D8B;
            color: white;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Change Your Newsletter Email</h2>
    <form method="POST">
        <label>New Email</label>
        <input type="email" name="new_email" required>
        <button type="submit">Update Email</button>
    </form>
</div>
</body>
</html>
