<?php
include 'includes/header.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Update borrow request status and set actual return date
    $sql = "UPDATE borrow_requests SET status = 'returned', actual_return_date = CURRENT_DATE WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Increase available quantity
        $sql = "UPDATE books b 
                JOIN borrow_requests br ON b.id = br.book_id 
                SET b.available_quantity = b.available_quantity + 1 
                WHERE br.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Add notification
        $sql = "INSERT INTO notifications (user_id, message) 
                SELECT user_id, 'Your book has been marked as returned' 
                FROM borrow_requests WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

header("Location: borrow-requests.php");
exit();
