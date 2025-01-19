<?php
include 'includes/header.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author_id = (int)($_POST['author_id'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $isbn = trim($_POST['isbn'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 1);

    // Validate input
    if (empty($title)) {
        $error = "Book title is required";
    } elseif ($author_id <= 0) {
        $error = "Please select an author";
    } elseif ($category_id <= 0) {
        $error = "Please select a category";
    } elseif (empty($isbn)) {
        $error = "ISBN is required";
    } else {
        try {
            $conn->begin_transaction();

            // Check if ISBN already exists
            $stmt = $conn->prepare("SELECT id FROM books WHERE isbn = ? AND id != ?");
            $stmt->bind_param("si", $isbn, $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                throw new Exception("A book with this ISBN already exists");
            }

            // Insert the book
            $sql = "INSERT INTO books (title, author_id, category_id, isbn, description, quantity, available_quantity) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siissii", $title, $author_id, $category_id, $isbn, $description, $quantity, $quantity);
            
            if ($stmt->execute()) {
                $conn->commit();
                header("Location: books.php?success=" . urlencode("Book added successfully"));
                exit();
            } else {
                throw new Exception($stmt->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error adding book: " . $e->getMessage();
        }
    }
}

// Get authors and categories for dropdowns
try {
    $authors = $conn->query("SELECT * FROM authors ORDER BY name");
    if (!$authors) {
        throw new Exception($conn->error);
    }
    
    $categories = $conn->query("SELECT * FROM categories ORDER BY name");
    if (!$categories) {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    $error = "Error loading form data: " . $e->getMessage();
}
?>

<div class="content-header">
    <h2>Add New Book</h2>
</div>

<div class="form-container">
    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="" id="bookForm">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" required 
                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="author_id">Author</label>
            <select id="author_id" name="author_id" required>
                <option value="">Select Author</option>
                <?php if ($authors && $authors->num_rows > 0): ?>
                    <?php while ($author = $authors->fetch_assoc()): ?>
                        <option value="<?php echo $author['id']; ?>" 
                                <?php echo (isset($_POST['author_id']) && $_POST['author_id'] == $author['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($author['name']); ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" required>
                <option value="">Select Category</option>
                <?php if ($categories && $categories->num_rows > 0): ?>
                    <?php while ($category = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $category['id']; ?>"
                                <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="isbn">ISBN</label>
            <input type="text" id="isbn" name="isbn" required 
                   value="<?php echo isset($_POST['isbn']) ? htmlspecialchars($_POST['isbn']) : ''; ?>">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"><?php 
                echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; 
            ?></textarea>
        </div>

        <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" id="quantity" name="quantity" min="1" 
                   value="<?php echo isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1; ?>" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Add Book</button>
            <a href="books.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
document.getElementById('bookForm').onsubmit = function(e) {
    const title = document.getElementById('title').value.trim();
    const author = document.getElementById('author_id').value;
    const category = document.getElementById('category_id').value;
    const isbn = document.getElementById('isbn').value.trim();
    
    if (!title) {
        e.preventDefault();
        alert('Book title is required');
        return false;
    }
    if (!author) {
        e.preventDefault();
        alert('Please select an author');
        return false;
    }
    if (!category) {
        e.preventDefault();
        alert('Please select a category');
        return false;
    }
    if (!isbn) {
        e.preventDefault();
        alert('ISBN is required');
        return false;
    }
    return true;
};
</script>

<?php include 'includes/footer.php'; ?>
