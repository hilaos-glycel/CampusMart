<?php
/**
 * Test Complete Login Flow
 */

session_start();
require_once 'config/config.php';

echo "<h2>Complete Login Flow Test</h2>";

// Test 1: Login via API
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_login'])) {
    echo "<h3>Testing Login API...</h3>";
    
    $loginData = [
        'loginId' => $_POST['loginId'],
        'password' => $_POST['password'],
        'csrf_token' => generateCSRFToken()
    ];
    
    // Simulate API call
    $_POST = $loginData;
    
    try {
        require_once 'includes/auth.php';
        $auth = new Auth();
        $result = $auth->login($loginData['loginId'], $loginData['password']);
        
        if ($result['success']) {
            echo "✅ Login API successful<br>";
            echo "User: " . $_SESSION['first_name'] . " " . $_SESSION['last_name'] . "<br>";
            echo "<a href='dashboard.php' target='_blank'>Test Dashboard Access</a><br>";
        } else {
            echo "❌ Login failed: " . $result['message'] . "<br>";
        }
    } catch (Exception $e) {
        echo "❌ Login error: " . $e->getMessage() . "<br>";
    }
}

// Test 2: Check current session
echo "<h3>Current Session Status:</h3>";
if (isLoggedIn()) {
    echo "✅ User is logged in<br>";
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "Username: " . $_SESSION['username'] . "<br>";
    echo "Name: " . $_SESSION['first_name'] . " " . $_SESSION['last_name'] . "<br>";
    echo "<a href='dashboard.php' target='_blank'>Go to Dashboard</a><br>";
} else {
    echo "❌ No active session<br>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Flow Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        input { width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ddd; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        .test-section { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="test-section">
        <h3>Test Login</h3>
        <form method="POST">
            <label>Username:</label>
            <input type="text" name="loginId" value="hilaos" required>
            
            <label>Password:</label>
            <input type="password" name="password" value="hilaos123" required>
            
            <button type="submit" name="test_login">Test Login</button>
        </form>
    </div>
    
    <div class="test-section">
        <h3>Test Accounts</h3>
        <ul>
            <li><strong>hilaos</strong> / hilaos123</li>
            <li><strong>sapuay</strong> / sapuay123</li>
            <li><strong>legaspi</strong> / legaspi123</li>
        </ul>
    </div>
    
    <div class="test-section">
        <h3>Quick Links</h3>
        <a href="login.php">Main Login Page</a> | 
        <a href="dashboard.php">Dashboard</a> | 
        <a href="simple_auth_fix.php">Simple Auth Test</a>
    </div>
</body>
</html>
