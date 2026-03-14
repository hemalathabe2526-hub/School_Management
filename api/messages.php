<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message'])) {
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message'];

    $sql = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $user_id, $receiver_id, $message);
    $stmt->execute();
}

$sql_users = "SELECT id, username FROM users WHERE id != ?";
$stmt = $conn->prepare($sql_users);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$users_result = $stmt->get_result();

$sql_messages = "SELECT m.*, u.username as sender FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.receiver_id = ? OR m.sender_id = ? ORDER BY m.timestamp DESC";
$stmt = $conn->prepare($sql_messages);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$messages_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Student Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/styles.css">
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
                <a class="nav-link" href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
                <a class="nav-link" href="calendar.php"><i class="fas fa-calendar"></i> Calendar</a>
                <a class="nav-link" href="export.php"><i class="fas fa-download"></i> Export</a>
                <a class="nav-link active" href="messages.php"><i class="fas fa-envelope"></i> Messages</a>
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <h1 class="mb-4"><i class="fas fa-envelope"></i> Internal Messages</h1>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Send Message</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">To:</label>
                                <select name="receiver_id" class="form-control" required>
                                    <?php while ($user = $users_result->fetch_assoc()) { ?>
                                        <option value="<?php echo $user['id']; ?>"><?php echo $user['username']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Message:</label>
                                <textarea name="message" class="form-control" rows="3" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Send</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Messages</h5>
                    </div>
                    <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                        <?php while ($msg = $messages_result->fetch_assoc()) { ?>
                            <div class="message mb-3 p-2 border rounded">
                                <strong><?php echo $msg['sender']; ?>:</strong> <?php echo $msg['message']; ?>
                                <small class="text-muted d-block"><?php echo $msg['timestamp']; ?></small>
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
