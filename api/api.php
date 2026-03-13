<?php
include 'db.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'count':
        $sql = "SELECT COUNT(*) as count FROM students";
        $result = $conn->query($sql);
        $count = $result->fetch_assoc()['count'];
        echo json_encode(['count' => $count]);
        break;

    case 'search':
        $query = $_GET['q'] ?? '';
        $sql = "SELECT * FROM students WHERE name LIKE ? OR email LIKE ? OR grade LIKE ?";
        $stmt = $conn->prepare($sql);
        $search = "%$query%";
        $stmt->bind_param("sss", $search, $search, $search);
        $stmt->execute();
        $result = $stmt->get_result();
        $students = [];
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        echo json_encode($students);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}

$conn->close();
?>