<?php
include 'includes/header.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$notification_id = (int)$_GET['id'];

try {
    // Verify the notification belongs to the current user
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 
                           WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $_SESSION['user_id']);
    $stmt->execute();
    
    header("Location: dashboard.php?success=Notification marked as read");
} catch (Exception $e) {
    header("Location: dashboard.php?error=" . urlencode($e->getMessage()));
}
exit();
?>
