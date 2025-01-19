<?php
include 'includes/header.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = '';
$success = '';

// Get error/success messages from URL
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}

// Get all borrow requests with book and user details
try {
    $sql = "SELECT br.*, b.title as book_title, b.isbn, 
                   u.username as user_name,
                   u.email as user_email
            FROM borrow_requests br
            JOIN books b ON br.book_id = b.id
            JOIN users u ON br.user_id = u.id
            ORDER BY br.request_date DESC";
            
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    $error = "Error fetching borrow requests: " . $e->getMessage();
    $result = false;
}
?>

<div class="content-header">
    <h2>Manage Borrow Requests</h2>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="grid-container">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($request = $result->fetch_assoc()): ?>
            <div class="grid-item">
                <div class="grid-item-header">
                    <h3 class="grid-item-title"><?php echo htmlspecialchars($request['book_title']); ?></h3>
                    <div class="request-status <?php echo strtolower($request['status']); ?>">
                        <?php echo htmlspecialchars(ucfirst($request['status'])); ?>
                    </div>
                </div>
                
                <div class="request-details">
                    <p><strong>Requested By:</strong> <?php echo htmlspecialchars($request['user_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($request['user_email']); ?></p>
                    <p><strong>ISBN:</strong> <?php echo htmlspecialchars($request['isbn']); ?></p>
                    <p><strong>Request Date:</strong> <?php echo date('M d, Y', strtotime($request['request_date'])); ?></p>
                    
                    <?php if ($request['status'] === 'pending'): ?>
                        <div class="request-actions">
                            <a href="process-request.php?id=<?php echo $request['id']; ?>&action=approve" 
                               class="btn btn-small btn-primary"
                               onclick="return confirm('Are you sure you want to approve this request?')">
                                Approve
                            </a>
                            <a href="process-request.php?id=<?php echo $request['id']; ?>&action=reject" 
                               class="btn btn-small btn-secondary"
                               onclick="return confirm('Are you sure you want to reject this request?')">
                                Reject
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="no-records">No borrow requests found.</p>
    <?php endif; ?>
</div>

<style>
.request-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.9em;
    font-weight: bold;
}

.request-status.pending {
    background-color: #ffd700;
    color: #000;
}

.request-status.approved {
    background-color: #90EE90;
    color: #006400;
}

.request-status.rejected {
    background-color: #ffcccb;
    color: #8b0000;
}

.request-actions {
    margin-top: 15px;
    display: flex;
    gap: 10px;
}

.request-details {
    margin-top: 10px;
}

.request-details p {
    margin: 5px 0;
}
</style>

<?php include 'includes/footer.php'; ?>
