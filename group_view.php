<?php
session_start();
include 'db_connect.php';

if (!isset($_GET['group_id']) || !isset($_SESSION['user_id'])) {
    echo "Invalid access.";
    exit();
}

$group_id = $_GET['group_id'];
$user_id = $_SESSION['user_id'];

// Get group details
$stmt = $conn->prepare("SELECT * FROM groups WHERE id = ?");
$stmt->bind_param("i", $group_id);
$stmt->execute();
$group = $stmt->get_result()->fetch_assoc();

// Get group messages
$msgStmt = $conn->prepare("
    SELECT gm.*, u.username, 
        rm.message AS replied_message,
        ru.username AS replied_username
    FROM group_messages gm
    JOIN users u ON gm.user_id = u.id
    LEFT JOIN group_messages rm ON gm.reply_to = rm.id
    LEFT JOIN users ru ON rm.user_id = ru.id
    LEFT JOIN deleted_messages dm ON gm.id = dm.message_id AND dm.user_id = ?
    WHERE gm.group_id = ? AND dm.id IS NULL
    ORDER BY gm.sent_at ASC

");
$msgStmt->bind_param("ii", $user_id, $group_id);

$msgStmt->execute();
$messages = $msgStmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($group['name']); ?> - Group Chat</title>
    <link rel="stylesheet" href="your-existing-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"> <!-- Link to your main stylesheet if needed -->
    <style>
        <?php include 'style-snippet.css'; ?> /* Place your CSS block here or in a separate file */
        
      /* Body and Background */
        body {
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #f1f1f1; /* Light background */
        }

        /* Profile Container */
        .profile-container {
            width: 50vw; /* Width set to 50% of the viewport width */
            max-width: 800px; /* Max width to ensure it doesn't get too wide */
            height: 90vh; /* Height set to 90% of the viewport height */
            background: #ffffff; /* White background */
            border-radius: 15px; /* Rounded corners */
            overflow: hidden;
             /* Ensure nothing overflows */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Subtle shadow for a nice effect */
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        /* Group Header */
        .group-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #e0e0e0;
            padding: 8px 16px; /* Slightly reduced padding */
            border-radius: 10px;
            position: relative;
            margin-bottom: 15px;
        }

        /* Message Area */
        .message-box {
            display: flex;
            flex-direction: column;
            align-items: flex-start; /* default for other messages */
            overflow-y: auto;
            height: 60%;
            background: #fff;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }


        /* Individual Chat Message */
        .message {
            display: inline-block;
            max-width: 70%;
            padding: 10px 14px;
            border-radius: 10px;
            word-wrap: break-word;
            margin-bottom: 10px;
            color: white;
            background-color: #58615b;
            position: relative;
        }

        .own-message {
            background-color: #468b94;
            align-self: flex-end;
            text-align: left;
        }

        .other-message {
            background-color: #58615b;
            align-self: flex-start;
            text-align: left;
        }

        .message-dropdown {
            position: absolute;
            top: 8px;
            right: 8px;
            cursor: pointer;
        }

        .dropdown-options {
            position: absolute;
            right: 0;
            top: 30px;
            background: white;
            border: 1px solid #ccc;
            border-radius: 8px;
            display: none;
            z-index: 10;
            width: 150px;
        }

        .dropdown-options a {
            display: block;
            padding: 8px 12px;
            font-size: 14px;
            color: #333;
            text-decoration: none;
            transition: background 0.2s ease;
        }

        .dropdown-options a:hover {
            background-color: #78909C;
        }

        /* Group Options */
        .group-options {
            position: absolute;
            background: white;
            border: 1px solid #ccc;
            padding: 10px;
            display: none;
            z-index: 10;
            border-radius: 8px;
            width: 220px;
        }

        .group-options a {
            display: block;
            padding: 8px 12px;
            text-decoration: none;
            color: #333;
            border-radius: 6px;
            transition: background 0.2s ease;
        }

        .group-options a:hover {
            background-color: #78909C;
        }



        /* Chat Input */
        .chat-input {
            display: flex;
            gap: 6px;
        }

        .chat-input textarea {
            flex: 1;
            resize: none;
            padding: 8px;
            font-size: 14px;
        }

        /* Other styles (buttons, file label) */
        .file-label {
            background-color: #78909C;
            color: black;
            padding: 6px 8px;
            border-radius: 8px;
            cursor: pointer;
        }


        .group-thumbnail {
            width: 45px; /* Slightly smaller */
            height: 45px; /* Slightly smaller */
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
        }

        .call-icons button {
            background: #78909C;
            color: black;
            border: none;
            padding: 8px 10px;
            margin-left: 6px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }

        .call-icons button:hover {
            background: #607d8b;
            color: white;
        }

        .right-sidebar {
        position: fixed;
        right: 30px;
        margin-right: 300px;
        top: 50%;
        transform: translateY(-50%);
        display: flex;
        flex-direction: column;
        gap: 10px;
        z-index: 100;
        }

        .right-sidebar button {
            padding: 8px 10px;
            border-radius: 8px;
            border: none;
            background: #78909C;
            color: black;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: background 0.2s ease;
        }

        .right-sidebar button:hover {
            background: #607D8B;
            color: white;
        }

        .popup {
            display: none;
            position: fixed;
            top: 5%;           /* upper part of the page */
            right: 5%;         /* aligned to right side */
            left: auto;        /* override any previous left positioning */
            width: 30%;        /* adjust width as needed */
            background: white;
            padding: 20px;
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
            z-index: 1000;
        }


        .popup.active {
            display: block;
        }

        .popup-box {
            position: absolute;
            top: 70px;
            right: 0;
            width: 300px;
            background-color: #fff;
            border: 1px solid #ccc;
            display: none;
            padding: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .popup-box.active {
            display: block;
        }
        .right-sidebar button {
            display: block;
            margin: 10px;
            width: 100%;
        }

        #replyPreview {
            background:#eee;
            padding:10px;
            border-left: 4px solid #78909C;
            margin-bottom: 8px;
            display:none;
            position: relative;
        }

        .back-link {
            position: absolute;
            top: 150px;
            right: 30px;
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
        <?php include "header.php"?>
    <div class="profile-container">
        <div class="group-header">
            <div style="position: relative;">
                <img src="<?php echo $group['thumbnail_path'] ?? 'default_group.png'; ?>" alt="Group Icon" class="group-thumbnail" onclick="toggleOptions()">
                <span style="font-weight: bold; font-size: 18px;"><?php echo htmlspecialchars($group['name']); ?></span>
                <div class="group-options" id="groupOptions">
                    <a href="add_member.php?group_id=<?= $group_id ?>">Add Member</a>
                    <a href="remove_member.php?group_id=<?= $group_id ?>">Remove Member</a>
                    <a href="view_media.php?group_id=<?= $group_id ?>">Media files</a>
                    <a href="leave_group.php?group_id=<?= $group_id ?>">Leave Group</a>
                    <a href="change_photo.php?group_id=<?= $group_id ?>">Change Group Photo</a>
                    <a href="change_name.php?group_id=<?= $group_id ?>">Change Group Name</a>
                </div>
            </div>

            <div class="call-icons">
                <button title="Audio Call"><i class="fas fa-phone"></i></button>
                <button title="Video Call"><i class="fas fa-video"></i></button>
            </div>
            
        </div>

                <!-- Message area -->
                <div class="message-box" id="messageBox">

                        <?php while ($msg = $messages->fetch_assoc()) :
                            $isOwn = $msg['user_id'] == $user_id ? 'own-message' : 'other-message'; ?>
                            <div class="message <?= $isOwn ?>" style="align-self: <?= $isOwn === 'own-message' ? 'flex-end' : 'flex-start' ?>; position: relative;">
                            <strong>
                        <?= htmlspecialchars($msg['username']) ?>:
                        <?php if ($msg['is_pinned']): ?>
                            <i class="fas fa-thumbtack" style="color:#DB1507; margin-left: 5px;" title="Pinned"></i>
                        <?php endif; ?>
                    </strong>

                    <?php if (!empty($msg['reply_to']) && !empty($msg['replied_message'])): ?>
                        <div class="replied-message" style="background: #ddd; padding: 6px 10px; border-left: 4px solid #78909C;        margin: 6px 0; font-size: 13px; color: #333;">
                            <strong><?= htmlspecialchars($msg['replied_username']) ?> said:</strong><br>
                            <?= nl2br(htmlspecialchars(substr($msg['replied_message'], 0, 100))) ?><?= strlen($msg['replied_message']) > 100 ? '...' : '' ?>
                        </div>
                    <?php endif; ?>

                    <?= nl2br(htmlspecialchars($msg['message'])) ?>

                                
                    <?php if ($msg['attachment_path']) : ?>
                        <div><a href="<?= $msg['attachment_path'] ?>" target="_blank">📎 Attachment</a></div>
                    <?php endif; ?>

                    <div style="font-size:12px; color:#999;"><?= $msg['sent_at'] ?></div>

                    <!-- Dropdown -->
                    <div class="message-dropdown" onclick="toggleDropdown(this)">
                        <i class="fas fa-ellipsis-v"></i>
                        <div class="dropdown-options">
                            <a href="reply_to_message.php" onclick="setReply(<?= $msg['id'] ?>, '<?= addslashes(htmlspecialchars($msg['message'])) ?>', '<?= addslashes(htmlspecialchars($msg['username'])) ?>'); return false;">Reply</a>

                            <?php if ($msg['is_pinned']): ?>
                                <a href="pin_message.php?action=unpin&message_id=<?= $msg['id'] ?>">Unpin</a>
                            <?php else: ?>
                                <a href="pin_message.php?action=pin&message_id=<?= $msg['id'] ?>">Pin</a>
                            <?php endif; ?>

                            <a href="delete_for_me.php?message_id=<?= $msg['id'] ?>">Delete for me</a>

                            <?php if ($msg['user_id'] == $user_id): ?>
                                <a href="delete_for_everyone.php?message_id=<?= $msg['id'] ?>">Delete for everyone</a>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
                <?php endwhile; ?>
            </div>

            <div id="replyPreview">
                <button onclick="cancelReply()" style="position:absolute; top:5px; right:5px; border:none; background:none; cursor:pointer;">❌</button>
            </div>

            <form action="send_message.php" method="POST" enctype="multipart/form-data" class="chat-input">
                <input type="hidden" name="group_id" value="<?= $group_id ?>">
                <input type="hidden" name="reply_to" id="replyToInput" value="">
                
                <textarea name="message" placeholder="Write your message..."></textarea>

                <input type="file" name="attachment" id="fileInput" style="display: none;" />
                <label for="fileInput" class="file-label">📎</label>

                <button type="submit" class="file-label">Send</button>     
            </form>


    </div>

    

    <!-- Sidebar and Popups -->
    <div class="right-sidebar">
        <button onclick="loadPopup('view_pinned_message.php')">📌 Pinned Messages</button>
        <button onclick="loadPopup('view_member.php')">👥 View Members</button>
        <button onclick="loadPopup('view_info.php')">ℹ️ Group Info</button>
        <button onclick="loadPopup('search_chat.php')">🔍 Search Conversation</button>
    </div>


    <div id="popupContainer" class="popup"></div>

    <a href="user_profile.php" class="back-link">← Back to Dashboard</a>

    <script>
    // Script for dropdown toggling
        function pinMessage(messageId) {
            fetch('pin_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'message_id=' + messageId + '&pin=1'
            })
            .then(res => res.text())
            .then(data => alert(data));
        }

        function deleteForMe(messageId) {
            fetch('delete_for_me.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'message_id=' + messageId
            })
            .then(res => res.text())
            .then(data => alert(data));
        }

        function deleteForEveryone(messageId) {
            fetch('delete_for_everyone.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'message_id=' + messageId
            })
            .then(res => res.text())
            .then(data => alert(data));
        }

        function editMessage(messageId) {
            // You can implement edit logic here
            alert('Edit clicked for message ID ' + messageId);
        }

        let currentReplyTo = null;

        function setReply(messageId, username, messageText) {
            currentReplyTo = messageId;
            document.getElementById('replyToInput').value = messageId;
            const replyPreview = document.getElementById('replyPreview');
            replyPreview.style.display = 'block';
            replyPreview.innerHTML = `<strong>${username} said:</strong><br>${messageText.substring(0, 100)}${messageText.length > 100 ? '...' : ''}`;
        }

        function cancelReply() {
            currentReplyTo = null;
            document.getElementById('replyToInput').value = '';
            document.getElementById('replyPreview').style.display = 'none';
        }


        function toggleDropdown(element) {
            const dropdown = element.querySelector('.dropdown-options');
            document.querySelectorAll('.dropdown-options').forEach(el => {
                if (el !== dropdown) el.style.display = 'none';
            });
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }

        // Optional: close dropdown when clicking elsewhere
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.message-dropdown')) {
                document.querySelectorAll('.dropdown-options').forEach(el => el.style.display = 'none');
            }
        });

        function toggleOptions() {
        const options = document.getElementById('groupOptions');
        if (!options) return;
        // Hide other dropdowns if needed (optional)
        // e.g. close message dropdowns too if you want to avoid overlap

        // Toggle display:
        options.style.display = (options.style.display === 'block') ? 'none' : 'block';
        }

        // Optional: close the group options when clicking outside
        document.addEventListener('click', function(event) {
        const groupOptions = document.getElementById('groupOptions');
        const groupThumbnail = document.querySelector('.group-thumbnail');
        if (!groupOptions || !groupThumbnail) return;

        if (!groupOptions.contains(event.target) && !groupThumbnail.contains(event.target)) {
            groupOptions.style.display = 'none';
        }
        });

        window.onload = function() {
        const messageBox = document.querySelector('.message-box');
        if (messageBox) {
            messageBox.scrollTop = messageBox.scrollHeight;
        }
        };

        function loadPopup(url) {
            console.log("Loading URL:", url);
            const popup = document.getElementById('popupContainer');
            const groupId = <?php echo json_encode($group_id); ?>;

            fetch(`${url}?group_id=${groupId}`)
                .then(response => response.text())
                .then(html => {
                    popup.innerHTML = html;
                    popup.classList.add('active');
                })
                .catch(err => {
                    popup.innerHTML = '<p>Error loading content.</p>';
                    popup.classList.add('active');
                    console.error('Popup Load Error:', err);
                });
        }

        // Optional: Close popup when clicking outside
        window.addEventListener('click', function(e) {
            const popup = document.getElementById('popupContainer');
            if (popup.classList.contains('active') && !popup.contains(e.target) && !e.target.closest('.right-sidebar')) {
                popup.classList.remove('active');
            }
        });


                // Add similar functions for pinned, group info, and search popups

    </script>

<?php include "footer.php"?>

</body>
</html>
