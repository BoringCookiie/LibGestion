# Library Management System

A PHP-based Library Management System with separate interfaces for administrators and clients.

## Features

### Admin Interface
- Manage Books (CRUD operations)
- Manage Categories (CRUD operations)
- Manage Authors (CRUD operations)
- Handle Book Borrowing Requests
- View Notifications

### Client Interface
- Browse Available Books
- Search Books by Category/Author/Title
- Borrow Books
- View Borrowing History

## Installation
1. Import the database schema from `database/library_db.sql`
2. Configure database connection in `config/database.php`
3. Start your PHP server
4. Access the application through your web browser

## Requirements
- PHP 7.4+
- MySQL 5.7+
- Web Server (Apache/Nginx)

## Default Admin Credentials
- Username: admin
- Password: admin123
