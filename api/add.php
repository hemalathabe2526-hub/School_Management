<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $dob = $_POST['dob'];
    $grade = $_POST['grade'];
    $parent_name = $_POST['parent_name'];
    $parent_phone = $_POST['parent_phone'];
    $blood_group = $_POST['blood_group'];
    $emergency_contact = $_POST['emergency_contact'];
    $medical_info = $_POST['medical_info'];

    $photo = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["photo"]["name"]);
        move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file);
        $photo = $target_file;
    }

    $sql = "INSERT INTO students (name, email, phone, address, dob, grade, parent_name, parent_phone, blood_group, emergency_contact, medical_info, photo) VALUES ('$name', '$email', '$phone', '$address', '$dob', '$grade', '$parent_name', '$parent_phone', '$blood_group', '$emergency_contact', '$medical_info', '$photo')";

    if ($conn->query($sql) === TRUE) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-school"></i> Student Management</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a class="nav-link" href="index.php"><i class="fas fa-list"></i> Students</a>
                <a class="nav-link" href="add.php"><i class="fas fa-plus"></i> Add Student</a>                <a class="nav-link" href="attendance.php"><i class="fas fa-calendar-check"></i> Attendance</a>
                <a class="nav-link" href="grades.php"><i class="fas fa-graduation-cap"></i> Grades</a>
                <a class="nav-link" href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
                <a class="nav-link" href="calendar.php"><i class="fas fa-calendar"></i> Calendar</a>
                <a class="nav-link" href="export.php"><i class="fas fa-download"></i> Export</a>            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-plus"></i> Add New Student</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name:</label>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone:</label>
                                <input type="text" id="phone" name="phone" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address:</label>
                                <textarea id="address" name="address" class="form-control"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="dob" class="form-label">Date of Birth:</label>
                                <input type="date" id="dob" name="dob" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="grade" class="form-label">Grade:</label>
                                <input type="text" id="grade" name="grade" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="parent_name" class="form-label">Parent Name:</label>
                                <input type="text" id="parent_name" name="parent_name" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="parent_phone" class="form-label">Parent Phone:</label>
                                <input type="text" id="parent_phone" name="parent_phone" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="blood_group" class="form-label">Blood Group:</label>
                                <select id="blood_group" name="blood_group" class="form-control">
                                    <option value="">Select Blood Group</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="emergency_contact" class="form-label">Emergency Contact:</label>
                                <input type="text" id="emergency_contact" name="emergency_contact" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="medical_info" class="form-label">Medical Information:</label>
                                <textarea id="medical_info" name="medical_info" class="form-control" rows="3" placeholder="Allergies, medications, special conditions..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="photo" class="form-label">Photo:</label>
                                <input type="file" id="photo" name="photo" class="form-control" accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Add Student</button>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <a href="index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to List</a>
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
</body>
</html>

<?php

?>
