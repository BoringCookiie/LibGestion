<?php include 'includes/header.php'; ?>

<div class="dashboard-stats">
    <div class="stat-card">
        <?php
        $result = $conn->query("SELECT COUNT(*) as total FROM books");
        $books = $result->fetch_assoc()['total'];
        ?>
        <h3>Total Books</h3>
        <p><?php echo $books; ?></p>
        <i class="fas fa-book"></i>
    </div>
    
    <div class="stat-card">
        <?php
        $result = $conn->query("SELECT COUNT(*) as total FROM categories");
        $categories = $result->fetch_assoc()['total'];
        ?>
        <h3>Categories</h3>
        <p><?php echo $categories; ?></p>
        <i class="fas fa-list"></i>
    </div>
    
    <div class="stat-card">
        <?php
        $result = $conn->query("SELECT COUNT(*) as total FROM authors");
        $authors = $result->fetch_assoc()['total'];
        ?>
        <h3>Authors</h3>
        <p><?php echo $authors; ?></p>
        <i class="fas fa-user-edit"></i>
    </div>
    
    <div class="stat-card">
        <?php
        $result = $conn->query("SELECT COUNT(*) as total FROM borrow_requests WHERE status='pending'");
        $pending = $result->fetch_assoc()['total'];
        ?>
        <h3>Pending Requests</h3>
        <p><?php echo $pending; ?></p>
        <i class="fas fa-clock"></i>
    </div>
</div>

<div class="recent-section">
    <h2>Recent Borrow Requests</h2>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Book</th>
                    <th>Request Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT br.*, u.username, b.title 
                        FROM borrow_requests br 
                        JOIN users u ON br.user_id = u.id 
                        JOIN books b ON br.book_id = b.id 
                        ORDER BY br.request_date DESC LIMIT 5";
                $result = $conn->query($sql);
                
                while ($row = $result->fetch_assoc()):
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo date('Y-m-d', strtotime($row['request_date'])); ?></td>
                    <td><span class="status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                    <td>
                        <?php if ($row['status'] == 'pending'): ?>
                        <a href="process-request.php?id=<?php echo $row['id']; ?>&action=approve" class="btn btn-small btn-primary">Approve</a>
                        <a href="process-request.php?id=<?php echo $row['id']; ?>&action=reject" class="btn btn-small btn-secondary">Reject</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<link rel="stylesheet" href="theme.css">

<style>
    .dashboard-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .stat-card {
        background-color: #f7f7f7;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    
    .stat-card h3 {
        margin-top: 0;
    }
    
    .stat-card p {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 10px;
    }
    
    .stat-card i {
        font-size: 36px;
        color: #666;
    }
    
    .recent-section {
        margin-top: 40px;
    }
    
    .table-responsive {
        overflow-x: auto;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    th, td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: left;
    }
    
    th {
        background-color: #f0f0f0;
    }
    
    .status-pending {
        color: #666;
    }
    
    .btn-small {
        padding: 5px 10px;
        font-size: 12px;
    }
    
    .btn-primary {
        background-color: #4CAF50;
        color: #fff;
    }
    
    .btn-secondary {
        background-color: #ccc;
        color: #666;
    }
</style>

<?php include 'includes/footer.php'; ?>
