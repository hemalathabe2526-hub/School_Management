<?php
// Database configuration
$servername = getenv('DB_HOST') ?: "localhost";
$username = getenv('DB_USER') ?: "root"; // Change if different
$password = getenv('DB_PASS') ?: ""; // Change if different
$dbname = getenv('DB_NAME') ?: "school_db";
$port = getenv('DB_PORT') ?: 3307;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, (int)$port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>