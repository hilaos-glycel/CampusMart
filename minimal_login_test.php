<?php
/**
 * Minimal Login Test - No Auth class, direct database
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginId = $_POST['loginId'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<h3>Testing Login for: " . htmlspecialchars($loginId) . "</h3>";
    
    try {
        // Direct database connection
        $pdo = new PDO(
            "mysql:host=localhost;dbname=campusmart;charset=utf8mb4",
            "root",
            "",
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        
        echo "✅ Database connected<br>";
        
        // Find user
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 'active'");
        $stmt->execute([$loginId]);
        $user = $stmt->fetch();
        
        if ($user) {
            echo "✅ User found: " . $user['first_name'] . " " . $user['last_name'] . "<br>";
            
            if (password_verify($password, $user['password_hash'])) {
                echo "✅ Password correct<br>";
                
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['first_name'] = $user['first_name'];
                
                echo "✅ Session set<br>";
                echo "<strong>LOGIN SUCCESSFUL!</strong><br>";
                echo "<a href='dashboard.php'>Go to Dashboard</a>";
                
            } else {
                echo "❌ Wrong password<br>";
            }
        } else {
            echo "❌ User not found<br>";
        }
        
    } catch (PDOException $e) {
        echo "❌ Database Error: " . $e->getMessage() . "<br>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Minimal Login Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; }
        input { width: 100%; padding: 10px; margin: 5px 0; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Minimal Login Test</h2>
    
    <form method="POST">
        <label>Username:</label>
        <input type="text" name="loginId" value="hilaos" required>
        
        <label>Password:</label>
        <input type="password" name="password" value="hilaos123" required>
        
        <button type="submit">Test Login</button>
    </form>
    
    <p><strong>Test Accounts:</strong></p>
    <ul>
        <li>hilaos / hilaos123</li>
        <li>sapuay / sapuay123</li>
        <li>legaspi / legaspi123</li>
    </ul>
</body>
</html>
