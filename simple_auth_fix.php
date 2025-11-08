<?php
/**
 * Simple Auth Fix - Replace problematic auth system
 */

require_once 'config/config.php';

// Create a simplified auth class
class SimpleAuth {
    private $db;
    
    public function __construct() {
        try {
            $this->db = new PDO(
                "mysql:host=localhost;dbname=campusmart;charset=utf8mb4",
                "root",
                "",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    public function login($loginId, $password) {
        try {
            // Find user
            $stmt = $this->db->prepare("
                SELECT id, student_id, username, email, password_hash, first_name, last_name, course, year_level, status 
                FROM users 
                WHERE (student_id = ? OR username = ? OR email = ?) AND status = 'active'
            ");
            $stmt->execute([$loginId, $loginId, $loginId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
            
            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['student_id'] = $user['student_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['course'] = $user['course'];
            $_SESSION['year_level'] = $user['year_level'];
            $_SESSION['last_activity'] = time();
            
            return ['success' => true, 'message' => 'Login successful', 'user' => $user];
            
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error occurred'];
        }
    }
}

// Test the simple auth
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginId = $_POST['loginId'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($loginId) && !empty($password)) {
        try {
            $auth = new SimpleAuth();
            $result = $auth->login($loginId, $password);
            
            if ($result['success']) {
                echo "<div style='color: green; padding: 10px; background: #d4edda; margin: 10px 0;'>";
                echo "✅ Login successful! User: " . $_SESSION['first_name'] . " " . $_SESSION['last_name'];
                echo "<br><a href='dashboard.php'>Go to Dashboard</a>";
                echo "</div>";
            } else {
                echo "<div style='color: red; padding: 10px; background: #f8d7da; margin: 10px 0;'>";
                echo "❌ " . $result['message'];
                echo "</div>";
            }
        } catch (Exception $e) {
            echo "<div style='color: red; padding: 10px; background: #f8d7da; margin: 10px 0;'>";
            echo "❌ Error: " . $e->getMessage();
            echo "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Simple Auth Fix</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; }
        input { width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ddd; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; width: 100%; }
    </style>
</head>
<body>
    <h2>Simple Auth Fix Test</h2>
    
    <form method="POST">
        <label>Username:</label>
        <input type="text" name="loginId" value="hilaos" required>
        
        <label>Password:</label>
        <input type="password" name="password" value="hilaos123" required>
        
        <button type="submit">Test Login</button>
    </form>
    
    <p><strong>If this works, we can replace the main auth system.</strong></p>
    
    <p><strong>Test Accounts:</strong></p>
    <ul>
        <li>hilaos / hilaos123</li>
        <li>sapuay / sapuay123</li>
        <li>legaspi / legaspi123</li>
    </ul>
    
    <p><a href='check_db_function.php'>Check Database Function</a> | <a href='debug_login_error.php'>Debug Login Error</a></p>
</body>
</html>
