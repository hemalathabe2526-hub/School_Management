<?php
include 'db.php';

// Drop tables if they exist (in reverse order due to foreign keys)
$conn->query("DROP TABLE IF EXISTS notifications");
$conn->query("DROP TABLE IF EXISTS messages");
$conn->query("DROP TABLE IF EXISTS grades");
$conn->query("DROP TABLE IF EXISTS attendance");
$conn->query("DROP TABLE IF EXISTS students");
$conn->query("DROP TABLE IF EXISTS users");

// Create users table
$conn->query("CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'staff') DEFAULT 'admin',
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create students table
$conn->query("CREATE TABLE students (
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

// Create attendance table
$conn->query("CREATE TABLE attendance (
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

// Create grades table
$conn->query("CREATE TABLE grades (
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

// Create messages table
$conn->query("CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT,
    receiver_id INT,
    message TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
)");

// Create notifications table
$conn->query("CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    message TEXT,
    recipient_type ENUM('all', 'students', 'parents', 'staff'),
    recipient_ids TEXT,
    sent_by INT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sent_by) REFERENCES users(id)
)");

// Insert default admin user if not exists
$check_admin = $conn->query("SELECT id FROM users WHERE username = 'admin'");
if ($check_admin->num_rows == 0) {
    $username = 'admin';
    $hashed_password = password_hash('password', PASSWORD_DEFAULT);
    $email = 'admin@school.com';
    $stmt = $conn->prepare("INSERT INTO users (username, password, role, email) VALUES (?, ?, 'admin', ?)");
    $stmt->bind_param("sss", $username, $hashed_password, $email);
    $stmt->execute();
    echo "Default admin user created: admin / password<br>";
}

// Insert sample students if table is empty
$check_students = $conn->query("SELECT COUNT(*) as count FROM students");
if ($check_students->fetch_assoc()['count'] == 0) {
    $sample_students = [
        ['John Doe', 'john@example.com', '1234567890', '123 Main St', '2010-05-15', 'Grade 5', 'Jane Doe', '0987654321', 'A+', 'Uncle Bob', 'Allergic to peanuts'],
        ['Alice Smith', 'alice@example.com', '2345678901', '456 Oak Ave', '2011-03-22', 'Grade 4', 'Bob Smith', '8765432109', 'B+', 'Aunt Mary', 'None'],
        ['Bob Johnson', 'bob@example.com', '3456789012', '789 Pine Rd', '2009-11-08', 'Grade 6', 'Carol Johnson', '7654321098', 'O-', 'Grandma', 'Asthma'],
        ['Emma Wilson', 'emma@example.com', '4567890123', '321 Elm St', '2012-01-30', 'Grade 3', 'David Wilson', '6543210987', 'AB+', 'Neighbor', 'None'],
        ['Michael Brown', 'michael@example.com', '5678901234', '654 Cedar Ln', '2010-09-12', 'Grade 5', 'Sarah Brown', '5432109876', 'A-', 'Cousin', 'Diabetes']
    ];

    $stmt = $conn->prepare("INSERT INTO students (name, email, phone, address, dob, grade, parent_name, parent_phone, blood_group, emergency_contact, medical_info) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($sample_students as $student) {
        $stmt->bind_param("sssssssssss", ...$student);
        $stmt->execute();
    }
    echo "Sample students added.<br>";
}

echo "Database setup completed successfully!<br>";
echo "You can now login with: admin / password";

$conn->close();
?>