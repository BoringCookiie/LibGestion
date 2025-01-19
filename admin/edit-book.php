<?php
include 'includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: books.php");
    exit();
}

$id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $author_id = $_POST['author_id'];
    $category_id = $_POST['category_id'];
    $isbn = $_POST['isbn'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];

    $sql = "UPDATE books SET title=?, author_id=?, category_id=?, isbn=?, description=?, quantity=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siissii", $title, $author_id, $category_id, $isbn, $description, $quantity, $id);
    
    if ($stmt->execute()) {
        header("Location: books.php");
        exit();
    } else {
        $error = "Error updating book: " . $conn->error;
    }
}

// Get book details
$sql = "SELECT * FROM books WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();

if (!$book) {
    header("Location: books.php");
    exit();
}

// Get authors and categories for dropdowns
$authors = $conn->query("SELECT * FROM authors ORDER BY name");
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>

<div class="content-header">
    <h2>Edit Book</h2>
</div>

<div class="form-container">
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($book['title']); ?>" required>
        </div>

        <div class="form-group">
            <label for="author_id">Author</label>
            <select id="author_id" name="author_id" required>
                <option value="">Select Author</option>
                <?php while ($author = $authors->fetch_assoc()): ?>
                    <option value="<?php echo $author['id']; ?>" <?php echo ($author['id'] == $book['author_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($author['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" required>
                <option value="">Select Category</option>
                <?php while ($category = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo ($category['id'] == $book['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="isbn">ISBN</label>
            <input type="text" id="isbn" name="isbn" value="<?php echo htmlspecialchars($book['isbn']); ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($book['description']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" id="quantity" name="quantity" min="1" value="<?php echo $book['quantity']; ?>" required>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Book</button>
            <a href="books.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
