<?php
include 'includes/header.php';

// Mark notifications as read
if (isset($_POST['mark_read'])) {
    $ids = $_POST['notification_ids'];
    if (!empty($ids)) {
        $ids_string = implode(',', array_map('intval', $ids));
        $conn->query("UPDATE notifications SET is_read = 1 
                     WHERE id IN ($ids_string) AND user_id = {$_SESSION['user_id']}");
    }
}

// Get all notifications for the current user
$sql = "SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$notifications = $stmt->get_result();
?>

<div class="content-header">
    <h2>My Notifications</h2>
</div>

<form method="POST" action="">
    <div class="notifications-container">
        <?php while ($notification = $notifications->fetch_assoc()): ?>
        <div class="notification-item <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>">
            <input type="checkbox" name="notification_ids[]" value="<?php echo $notification['id']; ?>">
            <div class="notification-content">
                <p class="notification-message">
                    <?php echo htmlspecialchars($notification['message']); ?>
                </p>
                <span class="notification-time">
                    <?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?>
                </span>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    
    <div class="notification-actions">
        <button type="submit" name="mark_read" class="btn btn-primary">
            Mark Selected as Read
        </button>
    </div>
</form>

<style>
.notifications-container {
    max-width: 800px;
    margin: 0 auto;
}

.notification-item {
    display: flex;
    align-items: start;
    padding: 1rem;
    margin-bottom: 1rem;
    background-color: var(--white);
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.notification-item.unread {
    background-color: #f0f9ff;
}

.notification-item input[type="checkbox"] {
    margin-right: 1rem;
    margin-top: 0.25rem;
}

.notification-content {
    flex: 1;
}

.notification-message {
    margin-bottom: 0.5rem;
}

.notification-time {
    color: #666;
    font-size: 0.875rem;
}

.notification-actions {
    margin-top: 1rem;
    text-align: center;
}
</style>

<?php include 'includes/footer.php'; ?>
