<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION["user_id"])) {
    echo "User not logged in!";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $interests = implode(",", $_POST["interests"]); // assuming checkbox or multiple select
    $user_id = $_SESSION["user_id"]; // ⭐ get the last signed up user id

    // Update the user's interests
    $stmt = $conn->prepare("UPDATE users SET interests = ? WHERE id = ?");
    $stmt->bind_param("si", $interests, $user_id);

    if ($stmt->execute()) {
        echo "Interests updated successfully!";
        // You can redirect to a login page or dashboard here
        header("Location: index.php");
        exit();
    } else {
        echo "Error updating interests: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ScholarSync Interest Selection</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #D3E0EB, #8A9BAA);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .interest-container {
            background-color: #A4B9BB;
            border-radius: 30px;
            padding: 40px;
            width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .interest-title {
            text-align: center;
            color: #000;
            font-size: 28px;
            margin-bottom: 25px;
        }

        .interest-description {
            color: #333;
            font-size: 16px;
            margin-bottom: 20px;
            text-align: center;
        }

        .department {
            margin-bottom: 20px;
            width: 100%;
        }

        .department h3 {
            font-size: 20px;
            color: #222;
            margin-bottom: 10px;
        }

        .interest-options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .interest-options label {
            background-color: #dbe2e8;
            padding: 8px 18px;
            border-radius: 20px;
            cursor: pointer;
            color: #2e2b2b;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .interest-options input[type="checkbox"] {
            display: none;
        }

        .interest-options input[type="checkbox"]:checked + label {
            background-color: #78909c;
            color: #fff;
        }

        button.submit-btn {
            margin-top: 20px;
            background-color: #78909c;
            color: #fff;
            padding: 12px 30px;
            border: none;
            border-radius: 20px;
            font-weight: bold;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        button.submit-btn:hover {
            background-color: #607d8b;
        }
    </style>
</head>
<body>
    <div class="interest-container">
        <h1 class="interest-title">Select Your Interests</h1>
        <form method="POST">

            <!-- Engineering Department -->
            <div class="department">
                <h3>Engineering</h3>
                <div class="interest-options">
                    <input type="checkbox" id="ml" name="interests[]" value="Machine Learning"><label for="ml">Machine Learning</label>
                    <input type="checkbox" id="ai" name="interests[]" value="Artificial Intelligence"><label for="ai">Artificial Intelligence</label>
                    <input type="checkbox" id="cs" name="interests[]" value="Cybersecurity"><label for="cs">Cybersecurity</label>
                    <input type="checkbox" id="ds" name="interests[]" value="Data Science"><label for="ds">Data Science</label>
                    <input type="checkbox" id="iot" name="interests[]" value="Internet of Things"><label for="iot">Internet of Things</label>
                    <input type="checkbox" id="robotics" name="interests[]" value="Robotics"><label for="robotics">Robotics</label>
                </div>
            </div>

            <!-- Business Department -->
            <div class="department">
                <h3>Business</h3>
                <div class="interest-options">
                    <input type="checkbox" id="marketing" name="interests[]" value="Marketing"><label for="marketing">Marketing</label>
                    <input type="checkbox" id="finance" name="interests[]" value="Finance"><label for="finance">Finance</label>
                    <input type="checkbox" id="entrepreneurship" name="interests[]" value="Entrepreneurship"><label for="entrepreneurship">Entrepreneurship</label>
                    <input type="checkbox" id="hr" name="interests[]" value="Human Resources"><label for="hr">Human Resources</label>
                    <input type="checkbox" id="economics" name="interests[]" value="Economics"><label for="economics">Economics</label>
                </div>
            </div>

            <!-- Arts Department -->
            <div class="department">
                <h3>Arts & Humanities</h3>
                <div class="interest-options">
                    <input type="checkbox" id="history" name="interests[]" value="History"><label for="history">History</label>
                    <input type="checkbox" id="philosophy" name="interests[]" value="Philosophy"><label for="philosophy">Philosophy</label>
                    <input type="checkbox" id="literature" name="interests[]" value="Literature"><label for="literature">Literature</label>
                    <input type="checkbox" id="languages" name="interests[]" value="Languages"><label for="languages">Languages</label>
                    <input type="checkbox" id="visual_arts" name="interests[]" value="Visual Arts"><label for="visual_arts">Visual Arts</label>
                </div>
            </div>

            <!-- Medical Department -->
            <div class="department">
                <h3>Medical</h3>
                <div class="interest-options">
                    <input type="checkbox" id="medicine" name="interests[]" value="Medicine"><label for="medicine">Medicine</label>
                    <input type="checkbox" id="nursing" name="interests[]" value="Nursing"><label for="nursing">Nursing</label>
                    <input type="checkbox" id="public_health" name="interests[]" value="Public Health"><label for="public_health">Public Health</label>
                    <input type="checkbox" id="biotech" name="interests[]" value="Biotechnology"><label for="biotech">Biotechnology</label>
                </div>
            </div>

            <!-- Law Department -->
            <div class="department">
                <h3>Law</h3>
                <div class="interest-options">
                    <input type="checkbox" id="corporate_law" name="interests[]" value="Corporate Law"><label for="corporate_law">Corporate Law</label>
                    <input type="checkbox" id="criminal_law" name="interests[]" value="Criminal Law"><label for="criminal_law">Criminal Law</label>
                    <input type="checkbox" id="environmental_law" name="interests[]" value="Environmental Law"><label for="environmental_law">Environmental Law</label>
                    <input type="checkbox" id="human_rights" name="interests[]" value="Human Rights"><label for="human_rights">Human Rights</label>
                </div>
            </div>

            <button type="submit" class="submit-btn">Save Interests</button>
        </form>
    </div>
</body>
</html>
