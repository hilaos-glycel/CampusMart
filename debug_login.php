<?php
/**
 * Login Debug Script - Comprehensive diagnosis
 */

session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Debug - CampusMart</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; background: #f8f9fa; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #dc3545; text-align: center; }
        h2 { color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 12px; }
        .btn { display: inline-block; padding: 12px 24px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #218838; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🐛 Login Debug & Diagnosis</h1>
        
        <?php
        echo "<h2>1. Database Connection Test</h2>";
        
        try {
            // Test MySQL connection
            $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<div class='success'>✅ MySQL connection successful</div>";
            
            // Check campusmart database
            $stmt = $pdo->query("SHOW DATABASES LIKE 'campusmart'");
            $dbExists = $stmt->fetch();
            
            if (!$dbExists) {
                echo "<div class='error'>❌ Database 'campusmart' does not exist!</div>";
                echo "<div class='warning'>You need to run the database setup first.</div>";
                echo "<a href='fix_database.php' class='btn btn-danger'>Run Database Setup</a>";
                exit;
            } else {
                echo "<div class='success'>✅ Database 'campusmart' exists</div>";
            }
            
            // Connect to campusmart
            $campusPdo = new PDO("mysql:host=localhost;dbname=campusmart;charset=utf8mb4", 'root', '');
            $campusPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<div class='success'>✅ Connected to campusmart database</div>";
            
        } catch (PDOException $e) {
            echo "<div class='error'>❌ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</div>";
            echo "<a href='fix_database.php' class='btn btn-danger'>Fix Database Issues</a>";
            exit;
        }
        
        echo "<h2>2. Database Tables Check</h2>";
        
        try {
            $stmt = $campusPdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($tables) == 0) {
                echo "<div class='error'>❌ No tables found in database!</div>";
                echo "<a href='fix_database.php' class='btn btn-danger'>Create Database Tables</a>";
                exit;
            }
            
            echo "<div class='success'>✅ Found " . count($tables) . " tables</div>";
            echo "<div class='info'>Tables: " . implode(', ', $tables) . "</div>";
            
            // Check users table specifically
            if (!in_array('users', $tables)) {
                echo "<div class='error'>❌ Users table missing!</div>";
                echo "<a href='fix_database.php' class='btn btn-danger'>Create Users Table</a>";
                exit;
            }
            
        } catch (PDOException $e) {
            echo "<div class='error'>❌ Error checking tables: " . htmlspecialchars($e->getMessage()) . "</div>";
            exit;
        }
        
        echo "<h2>3. Users Table Analysis</h2>";
        
        try {
            // Check users count
            $stmt = $campusPdo->query("SELECT COUNT(*) as count FROM users");
            $userCount = $stmt->fetch()['count'];
            
            if ($userCount == 0) {
                echo "<div class='error'>❌ No users found in database!</div>";
                echo "<div class='warning'>You need test users to login.</div>";
                echo "<a href='fix_database.php' class='btn btn-danger'>Create Test Users</a>";
                exit;
            }
            
            echo "<div class='success'>✅ Found $userCount users in database</div>";
            
            // Show user details
            $stmt = $campusPdo->query("SELECT id, student_id, username, email, first_name, last_name, status FROM users LIMIT 10");
            $users = $stmt->fetchAll();
            
            echo "<table>";
            echo "<tr><th>ID</th><th>Student ID</th><th>Username</th><th>Email</th><th>Name</th><th>Status</th></tr>";
            foreach ($users as $user) {
                $statusColor = $user['status'] == 'active' ? 'color: green' : 'color: red';
                echo "<tr>";
                echo "<td>{$user['id']}</td>";
                echo "<td>{$user['student_id']}</td>";
                echo "<td><strong>{$user['username']}</strong></td>";
                echo "<td>{$user['email']}</td>";
                echo "<td>{$user['first_name']} {$user['last_name']}</td>";
                echo "<td style='$statusColor'>{$user['status']}</td>";
                echo "</tr>";
            }
            echo "</table>";
            
        } catch (PDOException $e) {
            echo "<div class='error'>❌ Error checking users: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        
        echo "<h2>4. Login System Files Check</h2>";
        
        $requiredFiles = [
            'config/config.php' => 'Configuration file',
            'config/database.php' => 'Database connection',
            'includes/auth.php' => 'Authentication system',
            'api/login.php' => 'Login API endpoint',
            'login.php' => 'Login page'
        ];
        
        foreach ($requiredFiles as $file => $description) {
            $fullPath = __DIR__ . '/' . $file;
            if (file_exists($fullPath)) {
                echo "<div class='success'>✅ $description ($file)</div>";
            } else {
                echo "<div class='error'>❌ Missing: $description ($file)</div>";
            }
        }
        
        echo "<h2>5. Test Login Function</h2>";
        
        try {
            require_once 'includes/auth.php';
            echo "<div class='success'>✅ Auth class loaded successfully</div>";
            
            // Test with known user
            $stmt = $campusPdo->query("SELECT username FROM users WHERE status = 'active' LIMIT 1");
            $testUser = $stmt->fetch();
            
            if ($testUser) {
                echo "<div class='info'>Test user available: <strong>{$testUser['username']}</strong></div>";
                
                // Test password verification for hilaos user specifically
                $stmt = $campusPdo->prepare("SELECT password_hash FROM users WHERE username = 'hilaos'");
                $stmt->execute();
                $user = $stmt->fetch();
                
                if ($user) {
                    $testPassword = 'hilaos123';
                    $isValid = password_verify($testPassword, $user['password_hash']);
                    
                    if ($isValid) {
                        echo "<div class='success'>✅ Password verification works for hilaos/hilaos123</div>";
                    } else {
                        echo "<div class='error'>❌ Password verification failed for hilaos/hilaos123</div>";
                        echo "<div class='warning'>Password hash might be corrupted. Need to recreate users.</div>";
                    }
                } else {
                    echo "<div class='warning'>⚠️ User 'hilaos' not found</div>";
                }
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Auth system error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        
        echo "<h2>6. Session Check</h2>";
        
        if (session_status() === PHP_SESSION_ACTIVE) {
            echo "<div class='success'>✅ PHP sessions are working</div>";
            
            if (isset($_SESSION['user_id'])) {
                echo "<div class='info'>Current session: User ID {$_SESSION['user_id']} ({$_SESSION['username']})</div>";
                echo "<a href='dashboard.php' class='btn'>Go to Dashboard</a>";
            } else {
                echo "<div class='info'>No active user session</div>";
            }
        } else {
            echo "<div class='error'>❌ PHP sessions not working</div>";
        }
        
        echo "<h2>7. Quick Login Test</h2>";
        ?>
        
        <form method="POST" style="background: #f8f9fa; padding: 20px; border-radius: 5px;">
            <h3>Test Login Here:</h3>
            <div style="margin: 10px 0;">
                <label>Username/Student ID/Email:</label><br>
                <input type="text" name="test_login" value="hilaos" style="width: 200px; padding: 8px;">
            </div>
            <div style="margin: 10px 0;">
                <label>Password:</label><br>
                <input type="password" name="test_password" value="hilaos123" style="width: 200px; padding: 8px;">
            </div>
            <button type="submit" name="do_test_login" class="btn">Test Login Now</button>
        </form>
        
        <?php
        if (isset($_POST['do_test_login'])) {
            echo "<h3>Login Test Result:</h3>";
            
            try {
                $auth = new Auth();
                $result = $auth->login($_POST['test_login'], $_POST['test_password']);
                
                if ($result['success']) {
                    echo "<div class='success'>✅ LOGIN SUCCESSFUL!</div>";
                    echo "<div class='info'>
                        User: {$_SESSION['first_name']} {$_SESSION['last_name']}<br>
                        Username: {$_SESSION['username']}<br>
                        Student ID: {$_SESSION['student_id']}
                    </div>";
                    echo "<a href='dashboard.php' class='btn'>Go to Dashboard</a>";
                } else {
                    echo "<div class='error'>❌ Login failed: " . htmlspecialchars($result['message']) . "</div>";
                }
                
            } catch (Exception $e) {
                echo "<div class='error'>❌ Login test error: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
        ?>
        
        <h2>8. Recommended Actions</h2>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 5px;">
            <h3>If login still doesn't work:</h3>
            <ol>
                <li><strong>Run Database Fix:</strong> <a href="fix_database.php" class="btn">Fix Database</a></li>
                <li><strong>Clear Browser Cache:</strong> Press Ctrl+F5 to refresh</li>
                <li><strong>Check XAMPP:</strong> Ensure Apache and MySQL are running</li>
                <li><strong>Try Different Browser:</strong> Test in incognito/private mode</li>
            </ol>
            
            <h3>Test Accounts:</h3>
            <ul>
                <li><strong>hilaos</strong> / hilaos123</li>
                <li><strong>sapuay</strong> / sapuay123</li>
                <li><strong>legaspi</strong> / legaspi123</li>
            </ul>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="login.php" class="btn">Go to Login Page</a>
            <a href="fix_database.php" class="btn btn-danger">Run Database Fix</a>
            <a href="index.php" class="btn">Homepage</a>
        </div>
    </div>
</body>
</html>
