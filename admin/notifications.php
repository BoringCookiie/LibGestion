<?php
include 'includes/header.php';

// Mark notifications as read
if (isset($_POST['mark_read'])) {
    $ids = $_POST['notification_ids'];
    if (!empty($ids)) {
        $ids_string = implode(',', array_map('intval', $ids));
        $conn->query("UPDATE notifications SET is_read = 1 WHERE id IN ($ids_string)");
    }
}

// Get all notifications for admin
$sql = "SELECT n.*, u.username 
        FROM notifications n 
        JOIN users u ON n.user_id = u.id 
        WHERE u.user_type = 'client' 
        ORDER BY n.created_at DESC";
$notifications = $conn->query($sql);
?>

<div class="content-header">
    <h2>Notifications</h2>
</div>

<form method="POST" action="">
    <div class="notifications-container">
        <?php while ($notification = $notifications->fetch_assoc()): ?>
        <div class="notification-item <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>">
            <input type="checkbox" name="notification_ids[]" value="<?php echo $notification['id']; ?>">
            <div class="notification-content">
                <p class="notification-message">
                    <strong><?php echo htmlspecialchars($notification['username']); ?>:</strong>
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
