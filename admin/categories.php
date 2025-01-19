<?php
include 'includes/header.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$error = '';
$success = '';

// Handle category deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success = "Category deleted successfully";
    } else {
        $error = "Error deleting category: " . $stmt->error;
    }
}

// Handle category addition/update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($name)) {
        $error = "Category name is required";
    } else {
        try {
            $conn->begin_transaction();
            
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                // Update existing category
                $id = (int)$_POST['id'];
                $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
                $stmt->bind_param("ssi", $name, $description, $id);
            } else {
                // Add new category
                $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                $stmt->bind_param("ss", $name, $description);
            }
            
            if ($stmt->execute()) {
                $conn->commit();
                $success = isset($_POST['id']) ? "Category updated successfully" : "Category added successfully";
                header("Location: categories.php?success=" . urlencode($success));
                exit();
            } else {
                throw new Exception($stmt->error);
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Database Error: " . $e->getMessage();
        }
    }
}

// Get success message from URL
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}

// Get all categories
try {
    $categories = $conn->query("SELECT * FROM categories ORDER BY name");
    if (!$categories) {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    $error = "Error fetching categories: " . $e->getMessage();
    $categories = false;
}
?>

<div class="content-header">
    <h2>Manage Categories</h2>
    <button class="btn btn-primary" onclick="showAddModal()">Add Category</button>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="grid-container">
    <?php if ($categories && $categories->num_rows > 0): ?>
        <?php while ($category = $categories->fetch_assoc()): ?>
            <div class="grid-item">
                <div class="grid-item-header">
                    <h3 class="grid-item-title"><?php echo htmlspecialchars($category['name']); ?></h3>
                    <div class="grid-item-actions">
                        <button class="btn btn-small btn-primary" onclick='showEditModal(<?php echo json_encode($category); ?>)'>
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="?delete=<?php echo $category['id']; ?>" class="btn btn-small btn-secondary" 
                           onclick="return confirm('Are you sure you want to delete this category?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
                <p><?php echo htmlspecialchars($category['description'] ?? ''); ?></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="no-records">No categories found.</p>
    <?php endif; ?>
</div>

<!-- Add/Edit Modal -->
<div id="categoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add Category</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST" action="" id="categoryForm">
            <input type="hidden" id="categoryId" name="id">
            <div class="form-group">
                <label for="name">Category Name</label>
                <input type="text" id="categoryName" name="name" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="categoryDescription" name="description" rows="3"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
const modal = document.getElementById('categoryModal');
const modalTitle = document.getElementById('modalTitle');
const categoryForm = document.getElementById('categoryForm');
const categoryId = document.getElementById('categoryId');
const categoryName = document.getElementById('categoryName');
const categoryDescription = document.getElementById('categoryDescription');

function showAddModal() {
    modalTitle.textContent = 'Add Category';
    categoryForm.reset();
    categoryId.value = '';
    modal.style.display = 'block';
}

function showEditModal(category) {
    modalTitle.textContent = 'Edit Category';
    categoryId.value = category.id;
    categoryName.value = category.name;
    categoryDescription.value = category.description || '';
    modal.style.display = 'block';
}

function closeModal() {
    modal.style.display = 'none';
    categoryForm.reset();
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target == modal) {
        closeModal();
    }
}

// Prevent form submission if required fields are empty
categoryForm.onsubmit = function(e) {
    if (!categoryName.value.trim()) {
        e.preventDefault();
        alert('Category name is required');
        return false;
    }
    return true;
}
</script>

<?php include 'includes/footer.php'; ?>
