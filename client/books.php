<?php
include 'includes/header.php';

// Handle book borrowing request
if (isset($_POST['borrow'])) {
    $book_id = (int)$_POST['book_id'];
    
    // Check if book is available
    $sql = "SELECT available_quantity FROM books WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $available = $stmt->get_result()->fetch_assoc()['available_quantity'];
    
    if ($available > 0) {
        // Check if user already has a pending or approved request for this book
        $sql = "SELECT COUNT(*) as count FROM borrow_requests 
                WHERE user_id = ? AND book_id = ? 
                AND (status = 'pending' OR (status = 'approved' AND actual_return_date IS NULL))";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $_SESSION['user_id'], $book_id);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc()['count'];
        
        if ($existing == 0) {
            // Create borrow request
            $sql = "INSERT INTO borrow_requests (user_id, book_id, return_date) 
                    VALUES (?, ?, DATE_ADD(CURRENT_DATE, INTERVAL 14 DAY))";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $_SESSION['user_id'], $book_id);
            
            if ($stmt->execute()) {
                $success = "Borrow request submitted successfully!";
            } else {
                $error = "Error submitting request. Please try again.";
            }
        } else {
            $error = "You already have an active request for this book.";
        }
    } else {
        $error = "This book is currently unavailable.";
    }
}

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$author = isset($_GET['author']) ? (int)$_GET['author'] : 0;

// Build query
$sql = "SELECT b.*, a.name as author_name, c.name as category_name 
        FROM books b 
        LEFT JOIN authors a ON b.author_id = a.id 
        LEFT JOIN categories c ON b.category_id = c.id 
        WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $sql .= " AND (b.title LIKE ? OR b.description LIKE ?)";
    $search = "%$search%";
    $params[] = $search;
    $params[] = $search;
    $types .= "ss";
}

if ($category) {
    $sql .= " AND b.category_id = ?";
    $params[] = $category;
    $types .= "i";
}

if ($author) {
    $sql .= " AND b.author_id = ?";
    $params[] = $author;
    $types .= "i";
}

$sql .= " ORDER BY b.title";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$books = $stmt->get_result();

// Get categories and authors for filters
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
$authors = $conn->query("SELECT * FROM authors ORDER BY name");
?>

<div class="content-header">
    <h2>Browse Books</h2>
</div>

<div class="filters-section">
    <form method="GET" action="" class="filters-form">
        <div class="form-group">
            <input type="text" name="search" placeholder="Search books..." 
                   value="<?php echo htmlspecialchars($search); ?>">
        </div>
        
        <div class="form-group">
            <select name="category">
                <option value="">All Categories</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                <option value="<?php echo $cat['id']; ?>" 
                        <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat['name']); ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="form-group">
            <select name="author">
                <option value="">All Authors</option>
                <?php while ($auth = $authors->fetch_assoc()): ?>
                <option value="<?php echo $auth['id']; ?>" 
                        <?php echo $author == $auth['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($auth['name']); ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<div class="grid-container">
    <?php while ($book = $books->fetch_assoc()): ?>
    <div class="grid-item book-card">
        <div class="grid-item-header">
            <h3 class="grid-item-title"><?php echo htmlspecialchars($book['title']); ?></h3>
        </div>
        
        <div class="book-details">
            <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author_name']); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($book['category_name']); ?></p>
            <p><strong>Available:</strong> <?php echo $book['available_quantity']; ?>/<?php echo $book['quantity']; ?></p>
            <?php if (!empty($book['description'])): ?>
            <p class="book-description"><?php echo htmlspecialchars(substr($book['description'], 0, 100)) . '...'; ?></p>
            <?php endif; ?>
            
            <?php if ($book['available_quantity'] > 0): ?>
            <form method="POST" action="">
                <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                <button type="submit" name="borrow" class="btn btn-primary">Borrow</button>
            </form>
            <?php else: ?>
            <button class="btn btn-secondary" disabled>Not Available</button>
            <?php endif; ?>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<style>
.filters-section {
    margin-bottom: 2rem;
}

.filters-form {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.book-card {
    display: flex;
    flex-direction: column;
}

.book-details {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.book-details form {
    margin-top: auto;
}

.book-description {
    margin: 1rem 0;
    color: #666;
}
</style>

<?php include 'includes/footer.php'; ?>
