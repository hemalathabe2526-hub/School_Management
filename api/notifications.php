<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Create notifications table if not exists
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['send_notification'])) {
        $title = $_POST['title'];
        $message = $_POST['message'];
        $recipient_type = $_POST['recipient_type'];
        $sent_by = $_SESSION['user_id'];

        // Get recipient IDs based on type
        $recipient_ids = '';
        if ($recipient_type == 'students') {
            $result = $conn->query("SELECT GROUP_CONCAT(id) as ids FROM students");
            $recipient_ids = $result->fetch_assoc()['ids'];
        } elseif ($recipient_type == 'parents') {
            $result = $conn->query("SELECT GROUP_CONCAT(DISTINCT parent_phone) as phones FROM students WHERE parent_phone != ''");
            $recipient_ids = $result->fetch_assoc()['phones'];
        } elseif ($recipient_type == 'staff') {
            $result = $conn->query("SELECT GROUP_CONCAT(id) as ids FROM users WHERE role != 'admin'");
            $recipient_ids = $result->fetch_assoc()['ids'];
        }
        // For 'all', leave empty

        $sql = "INSERT INTO notifications (title, message, recipient_type, recipient_ids, sent_by) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $title, $message, $recipient_type, $recipient_ids, $sent_by);
        $stmt->execute();
        $success = "Notification sent successfully!";
    }
}

// Get recent notifications
$sql_notifications = "SELECT n.*, u.username as sender FROM notifications n JOIN users u ON n.sent_by = u.id ORDER BY n.sent_at DESC LIMIT 20";
$result_notifications = $conn->query($sql_notifications);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Student Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-school"></i> Student Management</a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">Welcome, <?php echo $_SESSION['username']; ?> (<?php echo $_SESSION['role']; ?>)</span>
                <button id="darkModeToggle" class="btn btn-outline-light me-2"><i class="fas fa-moon"></i></button>
                <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a class="nav-link" href="index.php"><i class="fas fa-list"></i> Students</a>
                <a class="nav-link" href="add.php"><i class="fas fa-plus"></i> Add Student</a>
                <a class="nav-link" href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a>
                <a class="nav-link" href="grades.php"><i class="fas fa-graduation-cap"></i> Grades</a>
                <a class="nav-link active" href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
                <a class="nav-link" href="calendar.php"><i class="fas fa-calendar"></i> Calendar</a>
                <a class="nav-link" href="export.php"><i class="fas fa-download"></i> Export</a>
                <a class="nav-link" href="messages.php"><i class="fas fa-envelope"></i> Messages</a>
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <h1 class="mb-4"><i class="fas fa-bell"></i> Notifications</h1>
        
        <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
        
        <div class="row">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h5>Send New Notification</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Title:</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Message:</label>
                                <textarea name="message" class="form-control" rows="4" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Send to:</label>
                                <select name="recipient_type" class="form-control" required>
                                    <option value="all">All Users</option>
                                    <option value="students">Students</option>
                                    <option value="parents">Parents</option>
                                    <option value="staff">Staff</option>
                                </select>
                            </div>
                            <button type="submit" name="send_notification" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Notification</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Notifications</h5>
                    </div>
                    <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                        <?php while ($notification = $result_notifications->fetch_assoc()) { ?>
                            <div class="notification-item mb-3 p-3 border rounded">
                                <h6><?php echo htmlspecialchars($notification['title']); ?></h6>
                                <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                <small class="text-muted">
                                    Sent to: <?php echo ucfirst($notification['recipient_type']); ?> | 
                                    By: <?php echo $notification['sender']; ?> | 
                                    <?php echo date('M j, Y g:i A', strtotime($notification['sent_at'])); ?>
                                </small>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p>&copy; 2026 Student Management System. All rights reserved.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('darkModeToggle').addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
        });

        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>