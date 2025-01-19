<?php
include 'includes/header.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['id']) || !isset($_GET['action'])) {
    header("Location: borrow-requests.php?error=" . urlencode("Invalid request parameters"));
    exit();
}

$request_id = (int)$_GET['id'];
$action = $_GET['action'];

try {
    // Start transaction
    $conn->begin_transaction();

    // Get the borrow request details with user info
    $stmt = $conn->prepare("SELECT br.*, b.title as book_title, u.id as user_id 
                           FROM borrow_requests br
                           JOIN books b ON br.book_id = b.id
                           JOIN users u ON br.user_id = u.id 
                           WHERE br.id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();

    if (!$request) {
        throw new Exception("Borrow request not found");
    }

    // Get book details to check availability
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->bind_param("i", $request['book_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();

    if (!$book) {
        throw new Exception("Book not found");
    }

    $notification_message = '';

    if ($action === 'approve') {
        // Check if book is still available
        if ($book['available_quantity'] < 1) {
            throw new Exception("Book is no longer available");
        }

        // Set return date to 14 days from now
        $return_date = date('Y-m-d', strtotime('+14 days'));

        // Update request status
        $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'approved', return_date = ? WHERE id = ?");
        $stmt->bind_param("si", $return_date, $request_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update request status: " . $stmt->error);
        }

        // Update book available quantity
        $stmt = $conn->prepare("UPDATE books SET available_quantity = available_quantity - 1 WHERE id = ? AND available_quantity > 0");
        $stmt->bind_param("i", $request['book_id']);
        if (!$stmt->execute() || $stmt->affected_rows === 0) {
            throw new Exception("Failed to update book quantity");
        }

        $notification_message = "Your request to borrow '{$request['book_title']}' has been approved. Please return it by " . date('M d, Y', strtotime($return_date));
        $success = "Request approved successfully";
    } elseif ($action === 'reject') {
        // Update request status to rejected
        $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update request status: " . $stmt->error);
        }

        $notification_message = "Your request to borrow '{$request['book_title']}' has been rejected.";
        $success = "Request rejected successfully";
    } else {
        throw new Exception("Invalid action");
    }

    // Add notification for the user
    if (!empty($notification_message)) {
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $request['user_id'], $notification_message);
        if (!$stmt->execute()) {
            throw new Exception("Failed to create notification: " . $stmt->error);
        }
    }

    // Commit transaction
    $conn->commit();
    
    // Redirect with success message
    header("Location: borrow-requests.php?success=" . urlencode($success));
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    header("Location: borrow-requests.php?error=" . urlencode($e->getMessage()));
    exit();
}
?>
