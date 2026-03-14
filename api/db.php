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

// Ensure all tables exist for serverless persistence (important for Vercel)
$conn->query("CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(128) PRIMARY KEY,
    data TEXT,
    last_access TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'staff') DEFAULT 'admin',
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

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

$conn->query("CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    date DATE,
    status ENUM('present', 'absent', 'late'),
    notes TEXT,
    recorded_by INT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id)
)");

$conn->query("CREATE TABLE IF NOT EXISTS grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    subject VARCHAR(100),
    grade VARCHAR(10),
    semester VARCHAR(50),
    year YEAR,
    teacher_id INT,
    comments TEXT,
    date_recorded DATE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id)
)");

$conn->query("CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT,
    receiver_id INT,
    message TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
)");

$conn->query("CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    message TEXT,
    recipient_type ENUM('all', 'students', 'parents', 'staff'),
    recipient_ids TEXT,
    sent_by INT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sent_by) REFERENCES users(id)
)");

// Create default admin if users table is empty
$admin_check = $conn->query("SELECT id FROM users WHERE username = 'admin'");
if ($admin_check && $admin_check->num_rows == 0) {
    $hashed_pass = password_hash('password', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (username, password, role) VALUES ('admin', '$hashed_pass', 'admin')");
}

/**
 * Custom Session Handler for Vercel (Database-backed)
 */
class DBSessionHandler implements SessionHandlerInterface {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function open($path, $name): bool { return true; }
    public function close(): bool { return true; }

    public function read($id): string {
        $stmt = $this->db->prepare("SELECT data FROM sessions WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['data'];
        }
        return "";
    }

    public function write($id, $data): bool {
        // Prevent writing if the database connection is already closed or hasn't been established
        if (!($this->db instanceof mysqli) || $this->db->connect_errno) {
            return false;
        }
        try {
            $stmt = @$this->db->prepare("REPLACE INTO sessions (id, data) VALUES (?, ?)");
            if (!$stmt) return false;
            $stmt->bind_param("ss", $id, $data);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    public function destroy($id): bool {
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE id = ?");
        $stmt->bind_param("s", $id);
        return $stmt->execute();
    }

    public function gc($max_lifetime): int|false {
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE last_access < DATE_SUB(NOW(), INTERVAL ? SECOND)");
        $stmt->bind_param("i", $max_lifetime);
        return $stmt->execute() ? 1 : false;
    }
}

// Start the session with the custom handler
if (isset($conn)) {
    $handler = new DBSessionHandler($conn);
    session_set_save_handler($handler, true);
}

// Ensure cookies are valid across the whole site (important when using Vercel rewrites)
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'] ?? '',
    'secure' => $secure,
    'httponly' => true,
    // Use None for cross-site support when HTTPS is enabled (Vercel uses HTTPS)
    'samesite' => $secure ? 'None' : 'Lax',
]);

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * CRITICAL: Ensure session is written BEFORE the database connection is closed.
 * This prevents "mysqli object is already closed" errors during PHP shutdown.
 */
register_shutdown_function('session_write_close');
?>