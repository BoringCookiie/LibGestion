<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Library Management System</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <style>
        .feature-card {
            padding: 30px;
            border-radius: 12px;
            background-color: rgba(255, 255, 255, 0.9);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            border: 2px solid transparent;
            margin-bottom: 20px;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 20px rgba(92, 64, 51, 0.2);
            border-color: var(--primary-light);
            background-color: rgba(255, 255, 255, 0.95);
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-light), var(--accent-light));
            opacity: 0;
            transition: opacity 0.4s ease;
            z-index: 1;
        }

        .feature-card:hover::before {
            opacity: 0.15;
        }

        .feature-card::after {
            content: '';
            position: absolute;
            top: -100%;
            left: -100%;
            width: 300%;
            height: 300%;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
            transform: rotate(45deg);
            transition: all 0.6s ease;
            z-index: 1;
        }

        .feature-card:hover::after {
            top: -50%;
            left: -50%;
        }

        .feature-card h3 {
            color: var(--primary-dark);
            font-size: 1.6em;
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
            transition: all 0.3s ease;
            padding-bottom: 10px;
            border-bottom: 2px solid transparent;
        }

        .feature-card:hover h3 {
            color: var(--primary-medium);
            border-bottom-color: var(--primary-light);
            transform: translateX(5px);
        }

        .feature-card p {
            color: var(--text-dark);
            line-height: 1.7;
            position: relative;
            z-index: 2;
            transition: all 0.3s ease;
            opacity: 0.9;
        }

        .feature-card:hover p {
            opacity: 1;
            transform: translateX(5px);
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            padding: 20px;
        }

        @media (max-width: 768px) {
            .grid-container {
                grid-template-columns: 1fr;
            }
            
            .feature-card {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="navbar-brand">Library MS</a>
            <ul class="navbar-nav">
                <li><a href="auth/login.php">Login</a></li>
                <li><a href="auth/register.php">Register</a></li>
            </ul>
        </div>
    </nav>

    <div class="hero-section">
        <div class="container">
            <h1>Welcome to Our Library</h1>
            <p>Discover a world of knowledge at your fingertips. Browse our extensive collection of books, 
               manage your borrowings, and explore new horizons with our modern library management system.</p>
            <div style="margin-top: 30px;">
                <a href="auth/login.php" class="btn btn-primary" style="margin-right: 15px;">Login</a>
                <a href="auth/register.php" class="btn btn-secondary">Register</a>
            </div>
        </div>
    </div>

    <div class="container" style="margin-top: 50px;">
        <div class="grid-container">
            <div class="feature-card">
                <h3>Easy Borrowing</h3>
                <p>Request books with just a few clicks. Get notifications about your request status 
                   and manage your borrowings efficiently. Our streamlined process makes borrowing books 
                   a breeze.</p>
            </div>
            <div class="feature-card">
                <h3>Vast Collection</h3>
                <p>Access our extensive collection of books across various categories. 
                   Find exactly what you're looking for with our advanced search system and 
                   discover new titles that match your interests.</p>
            </div>
            <div class="feature-card">
                <h3>Digital Management</h3>
                <p>Keep track of your borrowed books, due dates, and reading history. 
                   Get reminders for returns and manage your account easily through our 
                   intuitive digital interface.</p>
            </div>
        </div>
    </div>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>
