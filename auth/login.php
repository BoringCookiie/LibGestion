<?php
session_start();

// If user is already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['user_type'] === 'admin' ? '../admin/dashboard.php' : '../client/dashboard.php'));
    exit();
}

require_once '../config/database.php';

$error = '';
$username = '';
$user_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $user_type = $_POST['user_type'] ?? '';
    
    if (empty($username) || empty($password) || empty($user_type)) {
        $error = "Please fill in all fields";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND user_type = ?");
        $stmt->bind_param("ss", $username, $user_type);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type'];
                
                header("Location: " . ($user['user_type'] === 'admin' ? '../admin/dashboard.php' : '../client/dashboard.php'));
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "Invalid username or user type";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Library Management System</title>
    <link rel="stylesheet" href="../assets/css/theme.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-form">
            <h2>Welcome Back</h2>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo htmlspecialchars($username); ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="user_type">Login As</label>
                    <select id="user_type" name="user_type" required>
                        <option value="">Select User Type</option>
                        <option value="client" <?php echo $user_type === 'client' ? 'selected' : ''; ?>>Client</option>
                        <option value="admin" <?php echo $user_type === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
            </form>

            <p style="text-align: center; margin-top: 20px;">
                Don't have an account? <a href="register.php" style="color: var(--primary-dark);">Sign up</a>
            </p>
        </div>
    </div>
</body>
</html>
