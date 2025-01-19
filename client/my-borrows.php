<?php
include 'includes/header.php';

// Get all borrow requests for the current user
$sql = "SELECT br.*, b.title, b.isbn 
        FROM borrow_requests br 
        JOIN books b ON br.book_id = b.id 
        WHERE br.user_id = ? 
        ORDER BY br.request_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$borrows = $stmt->get_result();
?>

<div class="content-header">
    <h2>My Borrows</h2>
</div>

<div class="table-responsive">
    <table>
        <thead>
            <tr>
                <th>Book</th>
                <th>ISBN</th>
                <th>Request Date</th>
                <th>Return Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($borrow = $borrows->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($borrow['title']); ?></td>
                <td><?php echo htmlspecialchars($borrow['isbn']); ?></td>
                <td><?php echo date('Y-m-d', strtotime($borrow['request_date'])); ?></td>
                <td>
                    <?php 
                    if ($borrow['actual_return_date']) {
                        echo date('Y-m-d', strtotime($borrow['actual_return_date']));
                    } elseif ($borrow['return_date']) {
                        echo date('Y-m-d', strtotime($borrow['return_date']));
                    } else {
                        echo '-';
                    }
                    ?>
                </td>
                <td>
                    <span class="status-<?php echo $borrow['status']; ?>">
                        <?php echo ucfirst($borrow['status']); ?>
                    </span>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
