<?php
include 'db.php';
include 'security.php';

// Setup session security
setupSessionSecurity();

// Get error/info messages from URL
$error = isset($_GET['error']) ? $_GET['error'] : '';
$info = isset($_GET['info']) ? $_GET['info'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = "security_error";
    } else {
        // Get and validate input
        $username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        // Validate username format
        if (!validateUsername($username)) {
            $error = "invalid_input";
        } else {
            // Check rate limiting
            $identifier = $username . '_' . getClientIP();
            if (!checkRateLimit($identifier, $conn)) {
                $error = "too_many_attempts";
            } else {
                // Query database
                $sql = "SELECT id, username, password, role FROM users WHERE username = ?";
                $stmt = $conn->prepare($sql);
                
                if ($stmt) {
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows == 1) {
                        $user = $result->fetch_assoc();
                        if (password_verify($password, $user['password'])) {
                            // Clear failed attempts
                            $conn->query("DELETE FROM login_attempts WHERE identifier LIKE '$username%'");
                            
                            // Set session variables
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['username'] = $user['username'];
                            $_SESSION['role'] = $user['role'];
                            
                            // Log successful login
                            logActivity($user['id'], 'LOGIN', 'User logged in', $conn);
                            
                            header("Location: dashboard.php");
                            exit();
                        } else {
                            // Log failed attempt
                            logFailedAttempt($identifier, $conn);
                            $error = "invalid_credentials";
                        }
                    } else {
                        // Log failed attempt (even if user doesn't exist)
                        logFailedAttempt($identifier, $conn);
                        $error = "invalid_credentials";
                    }
                    $stmt->close();
                } else {
                    $error = "database_error";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Student Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h2 class="text-center mb-4"><i class="fas fa-sign-in-alt"></i> Login</h2>
                        
                        <?php
                        // Display error messages
                        $error_messages = [
                            'security_error' => 'Security token validation failed. Please try again.',
                            'invalid_input' => 'Invalid username format. Please try again.',
                            'invalid_credentials' => 'Invalid username or password. Please try again.',
                            'too_many_attempts' => 'Too many login attempts. Please try again in 1 hour.',
                            'database_error' => 'A system error occurred. Please try again later.',
                            'session_hijack' => 'Your session was invalidated for security reasons. Please login again.',
                            'login_required' => 'You must login to access this page.'
                        ];
                        
                        if ($error && isset($error_messages[$error])) {
                            echo "<div class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> " . $error_messages[$error] . "</div>";
                        }
                        
                        if ($info) {
                            echo "<div class='alert alert-info'><i class='fas fa-info-circle'></i> " . htmlspecialchars($info) . "</div>";
                        }
                        ?>
                        
                        <form method="post">
                            <?php echo getCSRFField(); ?>
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" pattern="[a-zA-Z0-9_]{3,50}" 
                                       placeholder="Username (alphanumeric and underscore)" required>
                                <small class="form-text text-muted">3-50 characters, letters, numbers, and underscores only</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" 
                                       placeholder="Enter your password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </button>
                        </form>
                        
                        <hr class="my-4">
                        <p class="text-center text-muted small">
                            <strong>Demo Credentials:</strong><br>
                            Username: <code>admin</code><br>
                            Password: Check setup.php or contact administrator
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
