<?php
/**
 * Simple Login Test - No JavaScript, direct PHP processing
 */

session_start();
require_once 'config/config.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        require_once 'includes/auth.php';
        
        $loginId = $_POST['loginId'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($loginId) || empty($password)) {
            $message = 'Please fill in all fields';
            $messageType = 'error';
        } else {
            $auth = new Auth();
            $result = $auth->login($loginId, $password);
            
            if ($result['success']) {
                $message = 'Login successful! Redirecting to dashboard...';
                $messageType = 'success';
                header('refresh:2;url=dashboard.php');
            } else {
                $message = $result['message'];
                $messageType = 'error';
            }
        }
        
    } catch (Exception $e) {
        $message = 'Database Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Login Test - CampusMart</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; background: #f8f9fa; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #28a745; text-align: center; margin-bottom: 30px; }
        .form-group { margin: 20px 0; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #333; }
        input[type="text"], input[type="password"] { 
            width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 5px; 
            box-sizing: border-box; font-size: 16px; 
        }
        input:focus { border-color: #667eea; outline: none; }
        .btn { 
            width: 100%; padding: 15px; background: #28a745; color: white; 
            border: none; border-radius: 5px; font-size: 16px; cursor: pointer; 
            font-weight: bold;
        }
        .btn:hover { background: #218838; }
        .message { 
            padding: 15px; border-radius: 5px; margin: 20px 0; text-align: center; font-weight: bold;
        }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .test-accounts { 
            background: #e9ecef; padding: 20px; border-radius: 5px; margin: 20px 0; 
        }
        .test-accounts h3 { margin-top: 0; color: #495057; }
        .quick-fill { 
            display: inline-block; padding: 5px 10px; background: #667eea; color: white; 
            text-decoration: none; border-radius: 3px; font-size: 12px; margin: 2px;
        }
        .quick-fill:hover { background: #5a67d8; }
        .links { text-align: center; margin-top: 30px; }
        .links a { color: #667eea; text-decoration: none; margin: 0 10px; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Simple Login Test</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="loginId">Username, Student ID, or Email:</label>
                <input type="text" id="loginId" name="loginId" 
                       value="<?php echo htmlspecialchars($_POST['loginId'] ?? ''); ?>" 
                       placeholder="Enter username, student ID, or email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" 
                       placeholder="Enter your password" required>
            </div>
            
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="test-accounts">
            <h3>🧪 Test Accounts - Quick Fill</h3>
            <p>Click to auto-fill login form:</p>
            <a href="#" class="quick-fill" onclick="fillForm('hilaos', 'hilaos123')">hilaos / hilaos123</a>
            <a href="#" class="quick-fill" onclick="fillForm('sapuay', 'sapuay123')">sapuay / sapuay123</a>
            <a href="#" class="quick-fill" onclick="fillForm('legaspi', 'legaspi123')">legaspi / legaspi123</a>
            
            <p style="margin-top: 15px; font-size: 14px; color: #666;">
                <strong>Manual Entry:</strong><br>
                Username: hilaos, Password: hilaos123<br>
                Username: sapuay, Password: sapuay123<br>
                Username: legaspi, Password: legaspi123
            </p>
        </div>
        
        <div class="links">
            <a href="debug_login.php">🐛 Full Debug</a> |
            <a href="fix_database.php">🔧 Fix Database</a> |
            <a href="login.php">🔐 Regular Login</a> |
            <a href="dashboard.php">📊 Dashboard</a>
        </div>
    </div>

    <script>
    function fillForm(username, password) {
        document.getElementById('loginId').value = username;
        document.getElementById('password').value = password;
    }
    </script>
</body>
</html>
