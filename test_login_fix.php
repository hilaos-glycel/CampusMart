<?php
/**
 * Quick Login Test - Tests the login functionality after database fix
 */

session_start();
require_once 'config/config.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Test - CampusMart</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; background: #f8f9fa; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #28a745; text-align: center; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"] { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn { width: 100%; padding: 12px; background: #28a745; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
        .btn:hover { background: #218838; }
        .result { margin: 20px 0; padding: 15px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; color: #0c5460; }
        .test-accounts { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Login Test</h1>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                require_once 'includes/auth.php';
                
                $loginId = $_POST['loginId'] ?? '';
                $password = $_POST['password'] ?? '';
                
                if (empty($loginId) || empty($password)) {
                    echo "<div class='result error'>❌ Please fill in all fields</div>";
                } else {
                    $auth = new Auth();
                    $result = $auth->login($loginId, $password);
                    
                    if ($result['success']) {
                        echo "<div class='result success'>✅ Login successful!</div>";
                        echo "<div class='result info'>
                            <strong>User Details:</strong><br>
                            Name: {$_SESSION['first_name']} {$_SESSION['last_name']}<br>
                            Username: {$_SESSION['username']}<br>
                            Student ID: {$_SESSION['student_id']}<br>
                            Course: {$_SESSION['course']}<br>
                            Year: {$_SESSION['year_level']}
                        </div>";
                        echo "<p><a href='dashboard.php' style='color: #28a745; font-weight: bold;'>Go to Dashboard →</a></p>";
                    } else {
                        echo "<div class='result error'>❌ Login failed: " . htmlspecialchars($result['message']) . "</div>";
                    }
                }
                
            } catch (Exception $e) {
                echo "<div class='result error'>❌ Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                echo "<div class='result info'>
                    <strong>Fix this by running:</strong><br>
                    <a href='fix_database.php' style='color: #667eea; font-weight: bold;'>Database Fix Script</a>
                </div>";
            }
        }
        ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="loginId">Username, Student ID, or Email:</label>
                <input type="text" id="loginId" name="loginId" value="<?= htmlspecialchars($_POST['loginId'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Test Login</button>
        </form>
        
        <div class="test-accounts">
            <h3>🧪 Test Accounts</h3>
            <p><strong>Use these accounts to test login:</strong></p>
            <ul>
                <li><strong>Username:</strong> hilaos <strong>Password:</strong> hilaos123</li>
                <li><strong>Username:</strong> sapuay <strong>Password:</strong> sapuay123</li>
                <li><strong>Username:</strong> legaspi <strong>Password:</strong> legaspi123</li>
            </ul>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="fix_database.php" style="color: #667eea;">🔧 Run Database Fix</a> | 
            <a href="login.php" style="color: #28a745;">🔐 Go to Login Page</a> | 
            <a href="index.php" style="color: #667eea;">🏠 Homepage</a>
        </div>
    </div>
</body>
</html>
