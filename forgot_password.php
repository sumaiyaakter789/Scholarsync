<?php
session_start();
include "db_connect.php";

$message = $error = "";

// Step 1: If form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $captcha_input = (int)$_POST['captcha'];

    if (isset($_SESSION['captcha_answer']) && $captcha_input === $_SESSION['captcha_answer']) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);

        if ($stmt->execute()) {
            $message = "Password updated successfully.";
        } else {
            $error = "Failed to update password.";
        }
    } else {
        $error = "Incorrect CAPTCHA answer.";
    }

    // Clear the used CAPTCHA after submission
    unset($_SESSION['captcha_answer']);
    unset($_SESSION['captcha_question']);
}

// Step 2: Always generate new CAPTCHA for fresh form or next attempt
function generateCaptcha() {
    $a = rand(1, 10);
    $b = rand(1, 10);
    $c = rand(1, 5);

    $type = rand(1, 5);

    switch ($type) {
        case 1:
            $question = "$a + $b = ?";
            $answer = $a + $b;
            break;
        case 2:
            $question = "$a - $b = ?";
            $answer = $a - $b;
            break;
        case 3:
            $question = "$a × $b = ?";
            $answer = $a * $b;
            break;
        case 4:
            $dividend = $a * $b;
            $question = "$dividend ÷ $a = ?";
            $answer = $dividend / $a;
            break;
        case 5:
            $question = "($a + $b) × $c = ?";
            $answer = ($a + $b) * $c;
            break;
        default:
            $question = "$a + $b = ?";
            $answer = $a + $b;
    }

    $_SESSION['captcha_question'] = $question;
    $_SESSION['captcha_answer'] = $answer;
}
generateCaptcha(); // call it always after unset or first load
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <style>
        .form-container {
            margin: 50px auto;
            max-width: 700px;
            background-color: #f5f5f5;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-size: 16px;
            color: #555;
            display: block;
            margin-bottom: 6px;
        }

        input, textarea, select {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        button {
            background-color: #78909C;
            color: black;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
            display: block;
            width: 100%;
            margin-top: 10px;
        }

        button:hover {
            background-color: #607d8b;
            color: white;
        }

        .error-message {
            color: red;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .success-message {
            color: green;
            font-size: 16px;
            margin-bottom: 20px;
        }

        
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
    <div class="form-container">
        <h2>Forgot Password</h2>
        <?php if (!empty($message)) echo "<p class='success-message'>$message</p>"; ?>
        <?php if (!empty($error)) echo "<p class='error-message'>$error</p>"; ?>

        <form method="POST">
    <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" required>
    </div>

    <div class="form-group">
        <label>New Password</label>
        <input type="password" name="new_password" required>
    </div>

    <div class="form-group">
        <label>Solve CAPTCHA: <?= $_SESSION['captcha_question'] ?></label>
        <input type="number" name="captcha" required>
    </div>

    <button type="submit">Reset Password</button>
</form>

    </div>
</body>
</html>
