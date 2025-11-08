<?php
/**
 * Debug Login Error - Find exact issue
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Login Debug - Step by Step</h2>";

// Step 1: Test database connection
echo "<h3>Step 1: Database Connection Test</h3>";
try {
    require_once 'config/config.php';
    $db = getDBConnection();
    echo "✅ Database connection successful<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit();
}

// Step 2: Test user lookup
echo "<h3>Step 2: User Lookup Test</h3>";
try {
    $loginId = 'hilaos';
    $stmt = $db->prepare("
        SELECT id, student_id, username, email, password_hash, first_name, last_name, course, year_level, status 
        FROM users 
        WHERE (student_id = ? OR username = ? OR email = ?) AND status = 'active'
    ");
    $stmt->execute([$loginId, $loginId, $loginId]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "✅ User found: " . $user['username'] . " (" . $user['first_name'] . " " . $user['last_name'] . ")<br>";
    } else {
        echo "❌ User not found<br>";
    }
} catch (Exception $e) {
    echo "❌ User lookup failed: " . $e->getMessage() . "<br>";
}

// Step 3: Test password verification
echo "<h3>Step 3: Password Verification Test</h3>";
if (isset($user)) {
    $password = 'hilaos123';
    if (password_verify($password, $user['password_hash'])) {
        echo "✅ Password verification successful<br>";
    } else {
        echo "❌ Password verification failed<br>";
        echo "Hash in DB: " . substr($user['password_hash'], 0, 20) . "...<br>";
    }
}

// Step 4: Test Auth class
echo "<h3>Step 4: Auth Class Test</h3>";
try {
    require_once 'includes/auth.php';
    $auth = new Auth();
    echo "✅ Auth class loaded successfully<br>";
    
    // Test login method
    $result = $auth->login('hilaos', 'hilaos123');
    if ($result['success']) {
        echo "✅ Auth login successful<br>";
        echo "Session user_id: " . ($_SESSION['user_id'] ?? 'not set') . "<br>";
    } else {
        echo "❌ Auth login failed: " . $result['message'] . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Auth class error: " . $e->getMessage() . "<br>";
    echo "Error details: " . $e->getFile() . " line " . $e->getLine() . "<br>";
}

// Step 5: Test API endpoint
echo "<h3>Step 5: API Endpoint Test</h3>";
echo "<form method='POST' action='api/login.php' target='_blank'>";
echo "<input type='hidden' name='csrf_token' value='" . generateCSRFToken() . "'>";
echo "<input type='hidden' name='loginId' value='hilaos'>";
echo "<input type='hidden' name='password' value='hilaos123'>";
echo "<button type='submit'>Test API Login</button>";
echo "</form>";

echo "<br><a href='login.php'>Back to Login Page</a>";
?>
