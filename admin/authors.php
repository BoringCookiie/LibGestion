<?php
include 'includes/header.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$error = '';
$success = '';

// Handle author deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM authors WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $success = "Author deleted successfully";
    } else {
        $error = "Error deleting author: " . $stmt->error;
    }
}

// Handle author addition/update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $biography = trim($_POST['biography'] ?? '');
    
    if (empty($name)) {
        $error = "Author name is required";
    } else {
        try {
            $conn->begin_transaction();
            
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                // Update existing author
                $id = (int)$_POST['id'];
                $stmt = $conn->prepare("UPDATE authors SET name = ?, biography = ? WHERE id = ?");
                $stmt->bind_param("ssi", $name, $biography, $id);
            } else {
                // Add new author
                $stmt = $conn->prepare("INSERT INTO authors (name, biography) VALUES (?, ?)");
                $stmt->bind_param("ss", $name, $biography);
            }
            
            if ($stmt->execute()) {
                $conn->commit();
                $success = isset($_POST['id']) ? "Author updated successfully" : "Author added successfully";
                header("Location: authors.php?success=" . urlencode($success));
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

// Get all authors
try {
    $authors = $conn->query("SELECT * FROM authors ORDER BY name");
    if (!$authors) {
        throw new Exception($conn->error);
    }
} catch (Exception $e) {
    $error = "Error fetching authors: " . $e->getMessage();
    $authors = false;
}
?>

<div class="content-header">
    <h2>Manage Authors</h2>
    <button class="btn btn-primary" onclick="showAddModal()">Add Author</button>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div class="grid-container">
    <?php if ($authors && $authors->num_rows > 0): ?>
        <?php while ($author = $authors->fetch_assoc()): ?>
            <div class="grid-item">
                <div class="grid-item-header">
                    <h3 class="grid-item-title"><?php echo htmlspecialchars($author['name']); ?></h3>
                    <div class="grid-item-actions">
                        <button class="btn btn-small btn-primary" onclick='showEditModal(<?php echo json_encode($author); ?>)'>
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="?delete=<?php echo $author['id']; ?>" class="btn btn-small btn-secondary" 
                           onclick="return confirm('Are you sure you want to delete this author?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
                <p><?php echo htmlspecialchars($author['biography'] ?? ''); ?></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="no-records">No authors found.</p>
    <?php endif; ?>
</div>

<!-- Add/Edit Modal -->
<div id="authorModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add Author</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST" action="" id="authorForm">
            <input type="hidden" id="authorId" name="id">
            <div class="form-group">
                <label for="name">Author Name</label>
                <input type="text" id="authorName" name="name" required>
            </div>
            <div class="form-group">
                <label for="biography">Biography</label>
                <textarea id="authorBiography" name="biography" rows="4"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
const modal = document.getElementById('authorModal');
const modalTitle = document.getElementById('modalTitle');
const authorForm = document.getElementById('authorForm');
const authorId = document.getElementById('authorId');
const authorName = document.getElementById('authorName');
const authorBiography = document.getElementById('authorBiography');

function showAddModal() {
    modalTitle.textContent = 'Add Author';
    authorForm.reset();
    authorId.value = '';
    modal.style.display = 'block';
}

function showEditModal(author) {
    modalTitle.textContent = 'Edit Author';
    authorId.value = author.id;
    authorName.value = author.name;
    authorBiography.value = author.biography || '';
    modal.style.display = 'block';
}

function closeModal() {
    modal.style.display = 'none';
    authorForm.reset();
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target == modal) {
        closeModal();
    }
}

// Prevent form submission if required fields are empty
authorForm.onsubmit = function(e) {
    if (!authorName.value.trim()) {
        e.preventDefault();
        alert('Author name is required');
        return false;
    }
    return true;
}
</script>

<?php include 'includes/footer.php'; ?>
