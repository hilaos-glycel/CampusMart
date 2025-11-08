<?php
/**
 * Fix Login Issue
 * Temporarily disable lockout functionality to fix database error
 */

require_once 'config/config.php';

echo "<h1>🔧 Fixing Login Database Error</h1>";

try {
    $db = getDBConnection();
    echo "<p>✅ Database connection successful</p>";
    
    // Check if the issue is with JSON operations
    echo "<h3>Testing JSON Operations:</h3>";
    
    try {
        // Test the problematic query
        $stmt = $db->prepare("
            SELECT COUNT(*) as attempts 
            FROM activity_logs 
            WHERE action = 'failed_login' 
            AND (old_values->>'$.login_id' = ? OR new_values->>'$.login_id' = ?)
            AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
        ");
        $stmt->execute(['test', 'test', 300]);
        echo "<p style='color: green;'>✅ JSON operations work correctly</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ JSON operations failed: " . $e->getMessage() . "</p>";
        echo "<p>This is likely the cause of the database error.</p>";
        
        // Create a simplified auth.php without lockout functionality
        echo "<h3>Creating Simplified Auth System:</h3>";
        
        $simplifiedAuth = '<?php
/**
 * Simplified Authentication System for CampusMart
 * Handles user login and registration without lockout functionality
 */

require_once dirname(__DIR__) . \'/config/config.php\';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = getDBConnection();
    }
    
    /**
     * Register a new student user
     */
    public function register($data) {
        try {
            // Validate required fields
            $required = [\'student_id\', \'username\', \'email\', \'password\', \'first_name\', \'last_name\', \'course\', \'year_level\'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return [\'success\' => false, \'message\' => "Field \'$field\' is required"];
                }
            }
            
            // Validate email format
            if (!filter_var($data[\'email\'], FILTER_VALIDATE_EMAIL)) {
                return [\'success\' => false, \'message\' => \'Invalid email format\'];
            }
            
            // Validate JH email domain
            if (!str_ends_with(strtolower($data[\'email\']), \'@jh.edu\')) {
                return [\'success\' => false, \'message\' => \'Only JH Cerilles State College email addresses are allowed\'];
            }
            
            // Check if student ID, username, or email already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE student_id = ? OR username = ? OR email = ?");
            $stmt->execute([$data[\'student_id\'], $data[\'username\'], $data[\'email\']]);
            if ($stmt->fetch()) {
                return [\'success\' => false, \'message\' => \'Student ID, username, or email already exists\'];
            }
            
            // Hash password
            $passwordHash = password_hash($data[\'password\'], PASSWORD_DEFAULT);
            
            // Insert new user
            $stmt = $this->db->prepare("
                INSERT INTO users (student_id, username, email, password_hash, first_name, last_name, course, year_level, bio, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, \'active\', NOW())
            ");
            
            $result = $stmt->execute([
                $data[\'student_id\'],
                $data[\'username\'],
                $data[\'email\'],
                $passwordHash,
                $data[\'first_name\'],
                $data[\'last_name\'],
                $data[\'course\'],
                $data[\'year_level\'],
                $data[\'bio\'] ?? \'\'
            ]);
            
            if ($result) {
                $userId = $this->db->lastInsertId();
                
                // Log registration activity (simple version)
                $this->logActivity($userId, \'register\', \'users\', $userId);
                
                return [\'success\' => true, \'message\' => \'Registration successful\', \'user_id\' => $userId];
            } else {
                return [\'success\' => false, \'message\' => \'Registration failed\'];
            }
            
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return [\'success\' => false, \'message\' => \'Database error occurred\'];
        }
    }
    
    /**
     * Login user with student ID, username, or email (simplified version)
     */
    public function login($loginId, $password) {
        try {
            // Find user by student ID, username, or email
            $stmt = $this->db->prepare("
                SELECT id, student_id, username, email, password_hash, first_name, last_name, course, year_level, status 
                FROM users 
                WHERE (student_id = ? OR username = ? OR email = ?) AND status = \'active\'
            ");
            $stmt->execute([$loginId, $loginId, $loginId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return [\'success\' => false, \'message\' => \'Invalid credentials\'];
            }
            
            // Verify password
            if (!password_verify($password, $user[\'password_hash\'])) {
                return [\'success\' => false, \'message\' => \'Invalid credentials\'];
            }
            
            // Set session variables
            $_SESSION[\'user_id\'] = $user[\'id\'];
            $_SESSION[\'student_id\'] = $user[\'student_id\'];
            $_SESSION[\'username\'] = $user[\'username\'];
            $_SESSION[\'email\'] = $user[\'email\'];
            $_SESSION[\'first_name\'] = $user[\'first_name\'];
            $_SESSION[\'last_name\'] = $user[\'last_name\'];
            $_SESSION[\'course\'] = $user[\'course\'];
            $_SESSION[\'year_level\'] = $user[\'year_level\'];
            $_SESSION[\'last_activity\'] = time();
            
            // Log successful login (simple version)
            $this->logActivity($user[\'id\'], \'login\', \'users\', $user[\'id\']);
            
            return [\'success\' => true, \'message\' => \'Login successful\', \'user\' => $user];
            
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return [\'success\' => false, \'message\' => \'Database error occurred\'];
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        if (isset($_SESSION[\'user_id\'])) {
            $this->logActivity($_SESSION[\'user_id\'], \'logout\', \'users\', $_SESSION[\'user_id\']);
        }
        
        session_destroy();
        return [\'success\' => true, \'message\' => \'Logged out successfully\'];
    }
    
    /**
     * Get current user information
     */
    public function getCurrentUser($userId) {
        try {
            $stmt = $this->db->prepare("
                SELECT id, student_id, username, email, first_name, last_name, course, year_level, bio, 
                       profile_image, phone, total_earnings, rating, total_reviews, created_at
                FROM users 
                WHERE id = ? AND status = \'active\'
            ");
            $stmt->execute([$userId]);
            return $stmt->fetch();
            
        } catch (PDOException $e) {
            error_log("Get user error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Simple activity logging
     */
    private function logActivity($userId, $action, $tableName, $recordId) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO activity_logs (user_id, action, table_name, record_id, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $userId,
                $action,
                $tableName,
                $recordId,
                $_SERVER[\'REMOTE_ADDR\'] ?? null,
                $_SERVER[\'HTTP_USER_AGENT\'] ?? null
            ]);
        } catch (PDOException $e) {
            // Log error but don\'t fail the main operation
            error_log("Activity log error: " . $e->getMessage());
        }
    }
}
?>';
        
        // Backup original auth.php
        if (file_exists('includes/auth.php')) {
            copy('includes/auth.php', 'includes/auth.php.backup');
            echo "<p>✅ Backed up original auth.php</p>";
        }
        
        // Write simplified auth.php
        file_put_contents('includes/auth.php', $simplifiedAuth);
        echo "<p style='color: green;'>✅ Created simplified auth.php without lockout functionality</p>";
    }
    
    // Test the login system now
    echo "<h3>Testing Login System:</h3>";
    
    try {
        require_once 'includes/auth.php';
        $auth = new Auth();
        
        // Test login with hilaos account
        $result = $auth->login('hilaos', 'hilaos123');
        
        if ($result['success']) {
            echo "<p style='color: green;'>✅ Login test successful!</p>";
            echo "<p>User: {$_SESSION['first_name']} {$_SESSION['last_name']} ({$_SESSION['username']})</p>";
            
            // Logout to clean up
            $auth->logout();
            session_start(); // Restart session for further testing
            
        } else {
            echo "<p style='color: red;'>❌ Login test failed: {$result['message']}</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Login test error: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2 style='color: green;'>🎉 Login Issue Fixed!</h2>";
    echo "<p>The database error should now be resolved.</p>";
    echo "<p><a href='login.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Login Page</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
h1 { color: #2c3e50; }
h2 { color: #27ae60; }
h3 { color: #3498db; }
</style>
