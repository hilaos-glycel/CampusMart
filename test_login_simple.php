<?php
/**
 * Simple Login Test - Bypass activity logging
 */

require_once 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginId = $_POST['loginId'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($loginId) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        try {
            $db = getDBConnection();
            
            // Find user by student ID, username, or email
            $stmt = $db->prepare("
                SELECT id, student_id, username, email, password_hash, first_name, last_name, course, year_level, status 
                FROM users 
                WHERE (student_id = ? OR username = ? OR email = ?) AND status = 'active'
            ");
            $stmt->execute([$loginId, $loginId, $loginId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $error = 'Invalid credentials - user not found';
            } elseif (!password_verify($password, $user['password_hash'])) {
                $error = 'Invalid credentials - wrong password';
            } else {
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
                
                $success = 'Login successful! Redirecting to dashboard...';
                header('refresh:2;url=dashboard.php');
            }
            
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Login Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <h2>Simple Login Test</h2>
    
    <?php if (isset($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="loginId">Username (try: hilaos)</label>
            <input type="text" id="loginId" name="loginId" value="<?php echo htmlspecialchars($loginId ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password (try: hilaos123)</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit">Test Login</button>
    </form>
    
    <hr>
    <h3>Test Accounts:</h3>
    <ul>
        <li><strong>hilaos</strong> / hilaos123</li>
        <li><strong>sapuay</strong> / sapuay123</li>
        <li><strong>legaspi</strong> / legaspi123</li>
    </ul>
</body>
</html>
