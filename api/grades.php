<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Create grades table if not exists
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
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (teacher_id) REFERENCES users(id)
)");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_grade'])) {
        $student_id = $_POST['student_id'];
        $subject = $_POST['subject'];
        $grade = $_POST['grade'];
        $semester = $_POST['semester'];
        $year = $_POST['year'];
        $comments = $_POST['comments'];
        $teacher_id = $_SESSION['user_id'];

        $sql = "INSERT INTO grades (student_id, subject, grade, semester, year, teacher_id, comments, date_recorded) 
                VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssis", $student_id, $subject, $grade, $semester, $year, $teacher_id, $comments);
        $stmt->execute();
        $success = "Grade added successfully!";
    }
}

// Get all grades with student info
$sql_grades = "SELECT g.*, s.name as student_name, s.grade as student_grade, u.username as teacher
               FROM grades g
               JOIN students s ON g.student_id = s.id
               JOIN users u ON g.teacher_id = u.id
               ORDER BY g.date_recorded DESC";
$result_grades = $conn->query($sql_grades);

// Get students for dropdown
$sql_students = "SELECT id, name FROM students ORDER BY name";
$result_students = $conn->query($sql_students);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grades Management - Student Management</title>
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
                <a class="nav-link active" href="grades.php"><i class="fas fa-graduation-cap"></i> Grades</a>
                <a class="nav-link" href="calendar.php"><i class="fas fa-calendar"></i> Calendar</a>
                <a class="nav-link" href="export.php"><i class="fas fa-download"></i> Export</a>
                <a class="nav-link" href="messages.php"><i class="fas fa-envelope"></i> Messages</a>
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <h1 class="mb-4"><i class="fas fa-graduation-cap"></i> Grades Management</h1>
        
        <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Add Grade</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Student:</label>
                                <select name="student_id" class="form-control" required>
                                    <option value="">Select Student</option>
                                    <?php while ($student = $result_students->fetch_assoc()) { ?>
                                        <option value="<?php echo $student['id']; ?>"><?php echo $student['name']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Subject:</label>
                                <input type="text" name="subject" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Grade:</label>
                                <select name="grade" class="form-control" required>
                                    <option value="A+">A+</option>
                                    <option value="A">A</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B">B</option>
                                    <option value="B-">B-</option>
                                    <option value="C+">C+</option>
                                    <option value="C">C</option>
                                    <option value="C-">C-</option>
                                    <option value="D">D</option>
                                    <option value="F">F</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Semester:</label>
                                <input type="text" name="semester" class="form-control" placeholder="e.g., Fall 2023" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Year:</label>
                                <input type="number" name="year" class="form-control" value="<?php echo date('Y'); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Comments:</label>
                                <textarea name="comments" class="form-control" rows="2"></textarea>
                            </div>
                            <button type="submit" name="add_grade" class="btn btn-success"><i class="fas fa-plus"></i> Add Grade</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>All Grades</h5>
                    </div>
                    <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Subject</th>
                                    <th>Grade</th>
                                    <th>Semester</th>
                                    <th>Year</th>
                                    <th>Teacher</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($grade = $result_grades->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?php echo $grade['student_name']; ?> (<?php echo $grade['student_grade']; ?>)</td>
                                        <td><?php echo $grade['subject']; ?></td>
                                        <td><span class="badge bg-<?php echo ($grade['grade'][0] == 'A') ? 'success' : (($grade['grade'][0] == 'B') ? 'primary' : (($grade['grade'][0] == 'C') ? 'warning' : 'danger')); ?>"><?php echo $grade['grade']; ?></span></td>
                                        <td><?php echo $grade['semester']; ?></td>
                                        <td><?php echo $grade['year']; ?></td>
                                        <td><?php echo $grade['teacher']; ?></td>
                                        <td><?php echo $grade['date_recorded']; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
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