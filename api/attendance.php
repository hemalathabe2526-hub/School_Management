<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Debug helper: visit /attendance.php?debug=1 to see session info
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    echo '<pre style="background:#000;color:#0f0;padding:10px;">';
    echo "Session ID: " . session_id() . "\n\n";
    echo htmlspecialchars(print_r($_SESSION, true));
    echo '</pre>';
}

// Create attendance table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    date DATE,
    status ENUM('present', 'absent', 'late'),
    notes TEXT,
    recorded_by INT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id)
)");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['mark_attendance'])) {
        $date = $_POST['date'];
        $recorded_by = $_SESSION['user_id'];

        foreach ($_POST['status'] as $student_id => $status) {
            $notes = $_POST['notes'][$student_id] ?? '';

            $sql = "INSERT INTO attendance (student_id, date, status, notes, recorded_by) VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE status = VALUES(status), notes = VALUES(notes), recorded_by = VALUES(recorded_by)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssi", $student_id, $date, $status, $notes, $recorded_by);
            $stmt->execute();
        }
        $success = "Attendance saved successfully!";
    }
}

$date = $_GET['date'] ?? date('Y-m-d');
$sql_students = "SELECT s.id, s.name, s.grade, a.status, a.notes
                 FROM students s
                 LEFT JOIN attendance a ON s.id = a.student_id AND a.date = ?
                 ORDER BY s.grade, s.name";
$stmt = $conn->prepare($sql_students);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - Student Management</title>
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
                <a class="nav-link active" href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a>
                <a class="nav-link" href="calendar.php"><i class="fas fa-calendar"></i> Calendar</a>
                <a class="nav-link" href="export.php"><i class="fas fa-download"></i> Export</a>
                <a class="nav-link" href="messages.php"><i class="fas fa-envelope"></i> Messages</a>
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <h1 class="mb-4"><i class="fas fa-calendar-check"></i> Attendance Management</h1>
        
        <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
        
        <div class="mb-3">
            <form method="get" class="d-inline">
                <label for="date" class="form-label">Select Date:</label>
                <input type="date" id="date" name="date" value="<?php echo $date; ?>" class="form-control d-inline w-auto" onchange="this.form.submit()">
            </form>
            <a href="attendance_report.php" class="btn btn-info ms-3"><i class="fas fa-chart-bar"></i> View Reports</a>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Mark Attendance for <?php echo date('l, F j, Y', strtotime($date)); ?></h5>
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="date" value="<?php echo $date; ?>">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Grade</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($student = $result->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?php echo $student['name']; ?></td>
                                        <td><?php echo $student['grade']; ?></td>
                                        <td>
                                            <select name="status[<?php echo $student['id']; ?>]" class="form-select">
                                                <option value="present" <?php echo ($student['status'] == 'present') ? 'selected' : ''; ?>>Present</option>
                                                <option value="absent" <?php echo ($student['status'] == 'absent') ? 'selected' : ''; ?>>Absent</option>
                                                <option value="late" <?php echo ($student['status'] == 'late') ? 'selected' : ''; ?>>Late</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" name="notes[<?php echo $student['id']; ?>]" value="<?php echo $student['notes']; ?>" class="form-control" placeholder="Optional notes">
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" name="mark_attendance" class="btn btn-success"><i class="fas fa-save"></i> Save Attendance</button>
                </form>
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