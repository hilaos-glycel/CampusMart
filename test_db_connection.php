<?php
/**
 * Test Database Connection
 * This script will help diagnose database connection issues
 */

echo "<h2>CampusMart Database Connection Test</h2>";

// Test basic MySQL connection first
try {
    echo "<h3>1. Testing MySQL Connection...</h3>";
    $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ MySQL connection successful!<br>";
    
    // Check if campusmart database exists
    echo "<h3>2. Checking if 'campusmart' database exists...</h3>";
    $stmt = $pdo->query("SHOW DATABASES LIKE 'campusmart'");
    $dbExists = $stmt->fetch();
    
    if ($dbExists) {
        echo "✅ Database 'campusmart' exists!<br>";
        
        // Test connection to campusmart database
        echo "<h3>3. Testing connection to campusmart database...</h3>";
        $campusPdo = new PDO("mysql:host=localhost;dbname=campusmart;charset=utf8mb4", 'root', '');
        $campusPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✅ Connection to campusmart database successful!<br>";
        
        // Check if tables exist
        echo "<h3>4. Checking database tables...</h3>";
        $stmt = $campusPdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "✅ Found " . count($tables) . " tables:<br>";
            foreach ($tables as $table) {
                echo "- $table<br>";
            }
            
            // Check if users table has data
            if (in_array('users', $tables)) {
                echo "<h3>5. Checking users table...</h3>";
                $stmt = $campusPdo->query("SELECT COUNT(*) as count FROM users");
                $userCount = $stmt->fetch()['count'];
                echo "✅ Users table has $userCount users<br>";
                
                if ($userCount > 0) {
                    echo "<h3>6. Sample users:</h3>";
                    $stmt = $campusPdo->query("SELECT username, first_name, last_name FROM users LIMIT 5");
                    $users = $stmt->fetchAll();
                    foreach ($users as $user) {
                        echo "- {$user['username']} ({$user['first_name']} {$user['last_name']})<br>";
                    }
                }
            }
        } else {
            echo "❌ No tables found in database. Database needs to be set up.<br>";
            echo "<strong>Action needed:</strong> Run the setup script.<br>";
        }
        
    } else {
        echo "❌ Database 'campusmart' does not exist.<br>";
        echo "<strong>Action needed:</strong> Run the setup script to create the database.<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    echo "<strong>Possible issues:</strong><br>";
    echo "- MySQL service is not running in XAMPP<br>";
    echo "- MySQL credentials are incorrect<br>";
    echo "- MySQL port is not 3306<br>";
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<p>If the database doesn't exist or has no tables, you need to run the setup script:</p>";
echo "<p><strong><a href='setup.php' style='color: #667eea; font-size: 1.2em;'>Click here to run the setup script</a></strong></p>";
echo "<p>Or manually navigate to: <code>http://localhost/CampusMart/setup.php</code></p>";

echo "<hr>";
echo "<p><a href='index.php'>← Back to CampusMart</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
h2 { color: #28a745; }
h3 { color: #667eea; }
code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
a { color: #667eea; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
