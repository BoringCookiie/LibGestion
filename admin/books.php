<?php 
include 'includes/header.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = '';
$success = '';

// Get success message from URL
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}

// Handle book deletion
if (isset($_GET['delete'])) {
    try {
        $id = (int)$_GET['delete'];
        
        // Check if book exists and is not borrowed
        $stmt = $conn->prepare("SELECT available_quantity, quantity FROM books WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $book = $result->fetch_assoc();
        
        if (!$book) {
            throw new Exception("Book not found");
        }
        
        if ($book['available_quantity'] < $book['quantity']) {
            throw new Exception("Cannot delete book as it is currently borrowed");
        }
        
        // Delete the book
        $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $success = "Book deleted successfully";
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        $error = "Error deleting book: " . $e->getMessage();
    }
}

// Get all books with author and category names
try {
    $sql = "SELECT b.*, a.name as author_name, c.name as category_name 
            FROM books b 
            LEFT JOIN authors a ON b.author_id = a.id 
            LEFT JOIN categories c ON b.category_id = c.id
            ORDER BY b.title";
            
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    $error = "Error fetching books: " . $e->getMessage();
    $result = false;
}
?>

<div class="content-header">
    <h2>Manage Books</h2>
    <a href="add-book.php" class="btn btn-primary">Add New Book</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="grid-container">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($book = $result->fetch_assoc()): ?>
            <div class="grid-item">
                <div class="grid-item-header">
                    <h3 class="grid-item-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                    <div class="grid-item-actions">
                        <a href="edit-book.php?id=<?php echo $book['id']; ?>" class="btn btn-small btn-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="?delete=<?php echo $book['id']; ?>" class="btn btn-small btn-secondary" 
                           onclick="return confirm('Are you sure you want to delete this book?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
                
                <div class="book-details">
                    <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author_name'] ?? 'Unknown'); ?></p>
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($book['category_name'] ?? 'Uncategorized'); ?></p>
                    <p><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?></p>
                    <p><strong>Available:</strong> <?php echo $book['available_quantity']; ?>/<?php echo $book['quantity']; ?></p>
                    <?php if (!empty($book['description'])): ?>
                        <p class="book-description"><?php echo htmlspecialchars(substr($book['description'], 0, 100)) . '...'; ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="no-records">No books found.</p>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
