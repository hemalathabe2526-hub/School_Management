<?php
// Database configuration
$servername = getenv('DB_HOST') ?: "localhost";
$username = getenv('DB_USER') ?: "root";
$password = getenv('DB_PASS') ?: "";
$dbname = getenv('DB_NAME') ?: "school_db";
$port = getenv('DB_PORT') ?: 3307;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, (int)$port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Auto-initialize tables if students table is missing
$check = $conn->query("SHOW TABLES LIKE 'students'");
if ($check->num_rows == 0) {
    // Create users table
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'teacher', 'staff') DEFAULT 'admin',
        email VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Create students table
    $conn->query("CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        phone VARCHAR(20),
        address TEXT,
        dob DATE,
        grade VARCHAR(20),
        parent_name VARCHAR(100),
        parent_phone VARCHAR(20),
        blood_group VARCHAR(5),
        emergency_contact VARCHAR(100),
        medical_info TEXT,
        photo VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Create default admin: admin / password
    $admin_check = $conn->query("SELECT id FROM users WHERE username = 'admin'");
    if ($admin_check->num_rows == 0) {
        $hashed_pass = password_hash('password', PASSWORD_DEFAULT);
        $conn->query("INSERT INTO users (username, password, role) VALUES ('admin', '$hashed_pass', 'admin')");
    }
}
?>