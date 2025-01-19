<?php include 'includes/header.php'; ?>

<div class="dashboard-stats">
    <div class="stat-card">
        <?php
        $sql = "SELECT COUNT(*) as total FROM borrow_requests 
                WHERE user_id = ? AND status = 'approved' 
                AND actual_return_date IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $active = $stmt->get_result()->fetch_assoc()['total'];
        ?>
        <h3>Active Borrows</h3>
        <p><?php echo $active; ?></p>
        <i class="fas fa-book-reader"></i>
    </div>
    
    <div class="stat-card">
        <?php
        $sql = "SELECT COUNT(*) as total FROM borrow_requests 
                WHERE user_id = ? AND status = 'pending'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $pending = $stmt->get_result()->fetch_assoc()['total'];
        ?>
        <h3>Pending Requests</h3>
        <p><?php echo $pending; ?></p>
        <i class="fas fa-clock"></i>
    </div>
    
    <div class="stat-card">
        <?php
        $sql = "SELECT COUNT(*) as total FROM borrow_requests 
                WHERE user_id = ? AND status = 'returned'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $returned = $stmt->get_result()->fetch_assoc()['total'];
        ?>
        <h3>Total Returned</h3>
        <p><?php echo $returned; ?></p>
        <i class="fas fa-check-circle"></i>
    </div>
    
    <div class="stat-card">
        <?php
        $sql = "SELECT COUNT(*) as total FROM notifications 
                WHERE user_id = ? AND is_read = 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $notifications = $stmt->get_result()->fetch_assoc()['total'];
        ?>
        <h3>New Notifications</h3>
        <p><?php echo $notifications; ?></p>
        <i class="fas fa-bell"></i>
    </div>
</div>

<div class="recent-section">
    <h2>Recent Borrows</h2>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Book</th>
                    <th>Borrow Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT br.*, b.title 
                        FROM borrow_requests br 
                        JOIN books b ON br.book_id = b.id 
                        WHERE br.user_id = ? 
                        ORDER BY br.request_date DESC LIMIT 5";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo date('Y-m-d', strtotime($row['request_date'])); ?></td>
                    <td>
                        <?php 
                        if ($row['actual_return_date']) {
                            echo date('Y-m-d', strtotime($row['actual_return_date']));
                        } elseif ($row['return_date']) {
                            echo date('Y-m-d', strtotime($row['return_date']));
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td>
                        <span class="status-<?php echo $row['status']; ?>">
                            <?php echo ucfirst($row['status']); ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$error = '';
$success = '';

// Get messages from URL
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}

// Get user's notifications
try {
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $notifications = $stmt->get_result();
} catch (Exception $e) {
    $error = "Error fetching notifications: " . $e->getMessage();
}

// Get user's borrow requests
try {
    $stmt = $conn->prepare("
        SELECT br.*, b.title as book_title, b.isbn
        FROM borrow_requests br
        JOIN books b ON br.book_id = b.id
        WHERE br.user_id = ?
        ORDER BY br.request_date DESC
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $requests = $stmt->get_result();
} catch (Exception $e) {
    $error = "Error fetching borrow requests: " . $e->getMessage();
}
?>

<div class="dashboard-container">
    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Notifications Section -->
    <?php if (isset($notifications) && $notifications->num_rows > 0): ?>
        <div class="notifications-section">
            <h2>New Notifications</h2>
            <div class="notifications-list">
                <?php while ($notification = $notifications->fetch_assoc()): ?>
                    <div class="notification-item">
                        <p><?php echo htmlspecialchars($notification['message']); ?></p>
                        <small>
                            <?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?>
                            <a href="mark-notification-read.php?id=<?php echo $notification['id']; ?>" 
                               class="mark-read-link">Mark as Read</a>
                        </small>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Borrow Requests Section -->
    <div class="requests-section">
        <h2>Your Borrow Requests</h2>
        <?php if (isset($requests) && $requests->num_rows > 0): ?>
            <div class="requests-list">
                <?php while ($request = $requests->fetch_assoc()): ?>
                    <div class="request-item">
                        <div class="request-header">
                            <h3><?php echo htmlspecialchars($request['book_title']); ?></h3>
                            <span class="status <?php echo strtolower($request['status']); ?>">
                                <?php echo ucfirst($request['status']); ?>
                            </span>
                        </div>
                        <div class="request-details">
                            <p><strong>ISBN:</strong> <?php echo htmlspecialchars($request['isbn']); ?></p>
                            <p><strong>Request Date:</strong> 
                                <?php echo date('M d, Y', strtotime($request['request_date'])); ?>
                            </p>
                            <?php if ($request['status'] === 'approved'): ?>
                                <p><strong>Return By:</strong> 
                                    <?php echo date('M d, Y', strtotime($request['return_date'])); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="no-records">You haven't made any borrow requests yet.</p>
        <?php endif; ?>
    </div>
</div>

<style>
.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.notifications-section {
    margin-bottom: 30px;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.notification-item {
    padding: 15px;
    border-bottom: 1px solid #eee;
}

.notification-item:last-child {
    border-bottom: none;
}

.notification-item p {
    margin: 0 0 8px 0;
}

.notification-item small {
    color: #666;
}

.mark-read-link {
    color: #007bff;
    margin-left: 10px;
    text-decoration: none;
}

.mark-read-link:hover {
    text-decoration: underline;
}

.requests-section {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.request-item {
    padding: 15px;
    border-bottom: 1px solid #eee;
}

.request-item:last-child {
    border-bottom: none;
}

.request-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.request-header h3 {
    margin: 0;
    font-size: 1.2em;
}

.status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.9em;
    font-weight: bold;
}

.status.pending {
    background-color: #ffd700;
    color: #000;
}

.status.approved {
    background-color: #90EE90;
    color: #006400;
}

.status.rejected {
    background-color: #ffcccb;
    color: #8b0000;
}

.request-details {
    margin-top: 10px;
}

.request-details p {
    margin: 5px 0;
}

.no-records {
    text-align: center;
    color: #666;
    padding: 20px;
}

/* New Styles */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.dashboard-container {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.grid-container {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    grid-gap: 20px;
}

.grid-item {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.navbar {
    background: #333;
    color: #fff;
    padding: 10px;
    text-align: center;
}

.navbar-brand {
    font-size: 1.5em;
    font-weight: bold;
    margin: 0;
}

.navbar-nav {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: space-between;
}

.navbar-nav li {
    margin-right: 20px;
}

.navbar-nav a {
    color: #fff;
    text-decoration: none;
}

.navbar-nav a:hover {
    text-decoration: underline;
}

/* Theme Styles */
.theme {
    background: #333;
    color: #fff;
    padding: 10px;
    text-align: center;
}

.theme h1 {
    font-size: 2em;
    font-weight: bold;
    margin: 0;
}

.theme p {
    margin: 10px 0;
}

.theme a {
    color: #fff;
    text-decoration: none;
}

.theme a:hover {
    text-decoration: underline;
}
</style>

<?php include 'includes/footer.php'; ?>
