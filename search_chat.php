<?php
include 'db_connect.php';

$group_id = $_GET['group_id'] ?? 0;
$search = $_GET['search'] ?? '';

if (!$group_id) {
    echo "<p>Invalid group ID.</p>";
    exit;
}

?>

<h3>Search Conversation</h3>
<hr>

<!-- Search form -->
<form method="GET" action="search_chat.php" style="margin-bottom: 15px;">
    <input type="hidden" name="group_id" value="<?php echo htmlspecialchars($group_id); ?>">
    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Enter keyword..." style="width: 70%; padding: 8px;">
    <button type="submit" style="padding: 8px 12px; background-color: #78909C;">Search</button>
</form>

<?php
$search = trim($search);

if ($search === '') {
    echo "<p>Please enter a keyword to search messages.</p>";
    exit;
}

// Prepare and execute search query
$stmt = $conn->prepare("
    SELECT gm.message, gm.sent_at, u.username 
    FROM group_messages gm
    JOIN users u ON gm.user_id = u.id
    WHERE gm.group_id = ? AND gm.message LIKE ?
    ORDER BY gm.sent_at DESC
");
$like_search = '%' . $search . '%';
$stmt->bind_param("is", $group_id, $like_search);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<p>Results for <strong>" . htmlspecialchars($search) . "</strong>:</p>";
    while ($msg = $result->fetch_assoc()) {
        echo "<div style='margin-bottom: 10px; padding: 5px; border-bottom: 1px solid #ccc;'>";
        echo "<strong>" . htmlspecialchars($msg['username']) . ":</strong> " . htmlspecialchars($msg['message']) . "<br>";
        echo "<small>Sent at: " . date("F j, Y, g:i a", strtotime($msg['sent_at'])) . "</small>";
        echo "</div>";
    }
} else {
    echo "<p>No messages found matching your search.</p>";
}

$stmt->close();
$conn->close();
?>
