<?php
include 'db.php';

// Fetch all students
$sql = "SELECT * FROM students";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-school"></i> Student Management</a>
            <div class="navbar-nav ms-auto">
                <button id="darkModeToggle" class="btn btn-outline-light me-2"><i class="fas fa-moon"></i></button>
                <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a class="nav-link active" href="index.php"><i class="fas fa-list"></i> Students</a>
                <a class="nav-link" href="add.php"><i class="fas fa-plus"></i> Add Student</a>
                <a class="nav-link" href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a>
                <a class="nav-link" href="grades.php"><i class="fas fa-graduation-cap"></i> Grades</a>
                <a class="nav-link" href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
                <a class="nav-link" href="calendar.php"><i class="fas fa-calendar"></i> Calendar</a>
                <a class="nav-link" href="export.php"><i class="fas fa-download"></i> Export</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-users"></i> Student List</h1>
            <a href="add.php" class="btn btn-success"><i class="fas fa-plus"></i> Add New Student</a>
        </div>
        <div class="mb-3">
            <input type="text" id="search" class="form-control" placeholder="Search students...">
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="studentsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Date of Birth</th>
                    <th>Grade</th>
                    <th>Parent Name</th>
                    <th>Parent Phone</th>
                    <th>Blood Group</th>
                    <th>Emergency Contact</th>
                    <th>Photo</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["id"] . "</td>";
                        echo "<td>" . $row["name"] . "</td>";
                        echo "<td>" . $row["email"] . "</td>";
                        echo "<td>" . $row["phone"] . "</td>";
                        echo "<td>" . $row["address"] . "</td>";
                        echo "<td>" . $row["dob"] . "</td>";
                        echo "<td>" . $row["grade"] . "</td>";
                        echo "<td>" . $row["parent_name"] . "</td>";
                        echo "<td>" . $row["parent_phone"] . "</td>";
                        echo "<td>" . $row["blood_group"] . "</td>";
                        echo "<td>" . $row["emergency_contact"] . "</td>";
                        echo "<td><img src='" . $row["photo"] . "' alt='Photo' width='50' height='50'></td>";
                        echo "<td><a href='edit.php?id=" . $row["id"] . "' class='btn btn-primary btn-sm'>Edit</a> <a href='delete.php?id=" . $row["id"] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure?\")'>Delete</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='13'>No students found</td></tr>";
                }
                ?>
            </tbody>
        </div>
    </div>
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <div class="container">
            <p>&copy; 2026 Student Management System. All rights reserved.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/script.js"></script>
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

?>
