<?php
/**
 * CampusMart Database Fix Script
 * This script will diagnose and fix database connection issues
 */

session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusMart Database Fix</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 0 auto; padding: 20px; background: #f8f9fa; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #28a745; text-align: center; }
        h2 { color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .btn { display: inline-block; padding: 12px 24px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #218838; }
        .btn-primary { background: #667eea; }
        .btn-primary:hover { background: #5a67d8; }
        code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .step { margin: 20px 0; padding: 20px; border-left: 4px solid #667eea; background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 CampusMart Database Fix</h1>
        
        <?php
        echo "<h2>Step 1: Testing MySQL Connection</h2>";
        
        try {
            // Test basic MySQL connection
            $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<div class='success'>✅ MySQL connection successful!</div>";
            
            // Check if campusmart database exists
            echo "<h2>Step 2: Checking CampusMart Database</h2>";
            $stmt = $pdo->query("SHOW DATABASES LIKE 'campusmart'");
            $dbExists = $stmt->fetch();
            
            if (!$dbExists) {
                echo "<div class='warning'>⚠️ Database 'campusmart' does not exist. Creating it now...</div>";
                
                // Create database
                $pdo->exec("CREATE DATABASE campusmart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                echo "<div class='success'>✅ Database 'campusmart' created successfully!</div>";
            } else {
                echo "<div class='success'>✅ Database 'campusmart' exists!</div>";
            }
            
            // Connect to campusmart database
            echo "<h2>Step 3: Connecting to CampusMart Database</h2>";
            $campusPdo = new PDO("mysql:host=localhost;dbname=campusmart;charset=utf8mb4", 'root', '');
            $campusPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<div class='success'>✅ Connected to campusmart database!</div>";
            
            // Check tables
            echo "<h2>Step 4: Checking Database Tables</h2>";
            $stmt = $campusPdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($tables) == 0) {
                echo "<div class='warning'>⚠️ No tables found. Setting up database schema...</div>";
                
                // Create tables
                $sql = "
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    student_id VARCHAR(20) UNIQUE NOT NULL,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    first_name VARCHAR(50) NOT NULL,
                    last_name VARCHAR(50) NOT NULL,
                    course VARCHAR(100) NOT NULL,
                    year_level VARCHAR(20) NOT NULL,
                    bio TEXT,
                    contact_number VARCHAR(20),
                    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
                    email_verified BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                );
                
                CREATE TABLE IF NOT EXISTS categories (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    description TEXT,
                    icon VARCHAR(50),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );
                
                CREATE TABLE IF NOT EXISTS listings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    category_id INT NOT NULL,
                    title VARCHAR(200) NOT NULL,
                    description TEXT NOT NULL,
                    price DECIMAL(10,2) NOT NULL,
                    condition_type ENUM('new', 'like_new', 'good', 'fair', 'poor') NOT NULL,
                    status ENUM('available', 'sold', 'reserved') DEFAULT 'available',
                    images JSON,
                    location VARCHAR(100),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (category_id) REFERENCES categories(id)
                );
                
                CREATE TABLE IF NOT EXISTS failed_login_attempts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    login_id VARCHAR(100) NOT NULL,
                    ip_address VARCHAR(45) NOT NULL,
                    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_login_id_time (login_id, attempt_time)
                );
                ";
                
                $campusPdo->exec($sql);
                echo "<div class='success'>✅ Database tables created successfully!</div>";
                
                // Insert sample categories
                $categories = [
                    ['Books & Academic Materials', 'Textbooks, reference books, and study materials', 'book'],
                    ['Electronics', 'Laptops, phones, gadgets, and accessories', 'laptop'],
                    ['Clothing & Accessories', 'Clothes, shoes, bags, and fashion items', 'shirt'],
                    ['Sports & Recreation', 'Sports equipment, gym gear, and recreational items', 'dumbbell'],
                    ['Furniture & Home', 'Dorm furniture, home decor, and appliances', 'home'],
                    ['Transportation', 'Bikes, scooters, and vehicle accessories', 'bike'],
                    ['Art & Supplies', 'Art materials, craft supplies, and creative tools', 'palette'],
                    ['Other', 'Miscellaneous items not covered in other categories', 'package']
                ];
                
                $stmt = $campusPdo->prepare("INSERT INTO categories (name, description, icon) VALUES (?, ?, ?)");
                foreach ($categories as $category) {
                    $stmt->execute($category);
                }
                echo "<div class='success'>✅ Sample categories inserted!</div>";
                
            } else {
                echo "<div class='success'>✅ Found " . count($tables) . " tables in database!</div>";
                echo "<div class='info'>Tables: " . implode(', ', $tables) . "</div>";
            }
            
            // Check for test users
            echo "<h2>Step 5: Checking Test Users</h2>";
            if (in_array('users', $tables)) {
                $stmt = $campusPdo->query("SELECT COUNT(*) as count FROM users");
                $userCount = $stmt->fetch()['count'];
                
                if ($userCount == 0) {
                    echo "<div class='warning'>⚠️ No users found. Creating test users...</div>";
                    
                    // Create test users
                    $testUsers = [
                        [
                            'student_id' => 'JH2024101',
                            'username' => 'hilaos',
                            'email' => 'maria.hilaos@jh.edu',
                            'password' => 'hilaos123',
                            'first_name' => 'Maria',
                            'last_name' => 'Hilaos',
                            'course' => 'Computer Science',
                            'year_level' => '3rd Year'
                        ],
                        [
                            'student_id' => 'JH2024102',
                            'username' => 'sapuay',
                            'email' => 'john.sapuay@jh.edu',
                            'password' => 'sapuay123',
                            'first_name' => 'John',
                            'last_name' => 'Sapuay',
                            'course' => 'Business Administration',
                            'year_level' => '2nd Year'
                        ],
                        [
                            'student_id' => 'JH2024103',
                            'username' => 'legaspi',
                            'email' => 'anna.legaspi@jh.edu',
                            'password' => 'legaspi123',
                            'first_name' => 'Anna',
                            'last_name' => 'Legaspi',
                            'course' => 'Engineering',
                            'year_level' => '4th Year'
                        ]
                    ];
                    
                    $stmt = $campusPdo->prepare("
                        INSERT INTO users (student_id, username, email, password_hash, first_name, last_name, course, year_level, email_verified) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
                    ");
                    
                    foreach ($testUsers as $user) {
                        $passwordHash = password_hash($user['password'], PASSWORD_DEFAULT);
                        $stmt->execute([
                            $user['student_id'],
                            $user['username'],
                            $user['email'],
                            $passwordHash,
                            $user['first_name'],
                            $user['last_name'],
                            $user['course'],
                            $user['year_level']
                        ]);
                    }
                    
                    echo "<div class='success'>✅ Test users created successfully!</div>";
                    echo "<div class='info'>
                        <strong>Test Accounts:</strong><br>
                        • Username: <code>hilaos</code> Password: <code>hilaos123</code><br>
                        • Username: <code>sapuay</code> Password: <code>sapuay123</code><br>
                        • Username: <code>legaspi</code> Password: <code>legaspi123</code>
                    </div>";
                    
                } else {
                    echo "<div class='success'>✅ Found $userCount users in database!</div>";
                    
                    // Show sample users
                    $stmt = $campusPdo->query("SELECT username, first_name, last_name FROM users LIMIT 5");
                    $users = $stmt->fetchAll();
                    echo "<div class='info'><strong>Sample users:</strong><br>";
                    foreach ($users as $user) {
                        echo "• {$user['username']} ({$user['first_name']} {$user['last_name']})<br>";
                    }
                    echo "</div>";
                }
            }
            
            echo "<h2>✅ Database Setup Complete!</h2>";
            echo "<div class='success'>
                <strong>Your CampusMart database is now ready!</strong><br><br>
                You can now:
                <ul>
                    <li>Login to the system using the test accounts</li>
                    <li>Access the dashboard and marketplace</li>
                    <li>Create new listings and services</li>
                </ul>
            </div>";
            
        } catch (PDOException $e) {
            echo "<div class='error'>❌ Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            echo "<div class='warning'>
                <strong>Possible Solutions:</strong>
                <ul>
                    <li>Make sure XAMPP MySQL service is running</li>
                    <li>Check if MySQL is running on port 3306</li>
                    <li>Verify MySQL credentials (username: root, password: empty)</li>
                    <li>Restart XAMPP services</li>
                </ul>
            </div>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        ?>
        
        <div class="step">
            <h2>🚀 Next Steps</h2>
            <p>Now that your database is set up, you can:</p>
            <a href="login.php" class="btn btn-primary">Go to Login Page</a>
            <a href="index.php" class="btn">Go to Homepage</a>
            <a href="dashboard.php" class="btn">Go to Dashboard</a>
        </div>
        
        <div class="step">
            <h2>🔍 Test Login</h2>
            <p>Try logging in with these test accounts:</p>
            <pre>Username: hilaos
Password: hilaos123

Username: sapuay  
Password: sapuay123

Username: legaspi
Password: legaspi123</pre>
        </div>
    </div>
</body>
</html>
