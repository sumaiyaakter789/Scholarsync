<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Fetch all users except the current user for member selection
$user_query = "SELECT id, username FROM users WHERE id != ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $group_name = trim($_POST['group_name']);
    $members = $_POST['members'] ?? [];
    $thumbnail_path = "";

    if (empty($group_name)) {
        $error_message = "Group name is required!";
    } else {
        // Handle thumbnail upload
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['thumbnail']['tmp_name'];
            $file_name = basename($_FILES['thumbnail']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($file_ext, $allowed_exts)) {
                $new_filename = uniqid("group_") . '.' . $file_ext;
                $upload_dir = 'uploads/groups/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $thumbnail_path = $upload_dir . $new_filename;
                move_uploaded_file($file_tmp, $thumbnail_path);
            } else {
                $error_message = "Only JPG, JPEG, PNG, and GIF files are allowed.";
            }
        }

        if (!isset($error_message)) {
            $creator_id = $_SESSION['user_id'];

            $insert_group = "INSERT INTO groups (name, thumbnail_path, created_by) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_group);
            $stmt->bind_param("ssi", $group_name, $thumbnail_path, $creator_id);
            if ($stmt->execute()) {
                $group_id = $stmt->insert_id;

                // Add creator as a member
                $insert_members = $conn->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
                $insert_members->bind_param("ii", $group_id, $creator_id);
                $insert_members->execute();

                // Add selected members
                foreach ($members as $member_id) {
                    $insert_members->bind_param("ii", $group_id, $member_id);
                    $insert_members->execute();
                }

                $success_message = "Group created successfully!";
            } else {
                $error_message = "Failed to create group.";
            }
        }
    }
}

include "header.php";
?>

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
    textarea {
        resize: vertical;
    }
    button {
        background-color: #78909C;
        color: black;
        border: none;
        padding: 12px 20px;
        font-size: 16px;
        border-radius: 8px;
        cursor: pointer;
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
    .checkbox-group {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .checkbox-item {
        flex: 0 0 48%;
        background-color: #fff;
        padding: 8px 12px;
        border: 1px solid #ccc;
        border-radius: 8px;
    }
    .checkbox-item label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 15px;
        color: #333;
    }
</style>

<div class="form-container">
    <h2>Create a Group</h2>

    <?php if (isset($error_message)) { echo "<p class='error-message'>$error_message</p>"; } ?>
    <?php if (isset($success_message)) { echo "<p class='success-message'>$success_message</p>"; } ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="group_name">Group Name</label>
            <input type="text" id="group_name" name="group_name" required>
        </div>

        <div class="form-group">
            <label>Select Members</label>
            <div class="checkbox-group">
                <?php foreach ($users as $user): ?>
                    <div class="checkbox-item">
                        <label>
                            <input type="checkbox" name="members[]" value="<?= $user['id'] ?>">
                            <?= htmlspecialchars($user['username']) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-group">
            <label for="thumbnail">Group Thumbnail (optional)</label>
            <input type="file" id="thumbnail" name="thumbnail" accept="image/*">
        </div>

        <button type="submit">Create Group</button>
    </form>
</div>

<?php include "footer.php"; ?>
