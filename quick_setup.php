<?php
/**
 * QUICK SETUP - Fix Database Error in 30 seconds
 */

echo "<!DOCTYPE html>
<html><head><title>Quick Setup</title>
<style>
body{font-family:Arial;max-width:600px;margin:50px auto;padding:20px;background:#f5f5f5}
.box{background:white;padding:30px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.1)}
h1{color:#dc3545;text-align:center}
.success{background:#d4edda;color:#155724;padding:15px;border-radius:5px;margin:10px 0}
.error{background:#f8d7da;color:#721c24;padding:15px;border-radius:5px;margin:10px 0}
.btn{display:inline-block;padding:12px 24px;background:#28a745;color:white;text-decoration:none;border-radius:5px;margin:10px 5px}
</style></head><body><div class='box'>";

echo "<h1>🚨 Quick Database Setup</h1>";

try {
    // Connect to MySQL
    echo "<p>⏳ Connecting to MySQL...</p>";
    $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div class='success'>✅ MySQL connected!</div>";

    // Create database
    echo "<p>⏳ Creating campusmart database...</p>";
    $pdo->exec("DROP DATABASE IF EXISTS campusmart");
    $pdo->exec("CREATE DATABASE campusmart");
    $pdo->exec("USE campusmart");
    echo "<div class='success'>✅ Database created!</div>";

    // Create users table
    echo "<p>⏳ Creating users table...</p>";
    $pdo->exec("
        CREATE TABLE users (
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
            email_verified BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    echo "<div class='success'>✅ Users table created!</div>";

    // Create other essential tables
    echo "<p>⏳ Creating other tables...</p>";
    
    $pdo->exec("
        CREATE TABLE categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            icon VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE listings (
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
        )
    ");

    $pdo->exec("
        CREATE TABLE failed_login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            login_id VARCHAR(100) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_login_id_time (login_id, attempt_time)
        )
    ");

    echo "<div class='success'>✅ All tables created!</div>";

    // Insert test users
    echo "<p>⏳ Creating test users...</p>";
    
    $users = [
        ['JH2024101', 'hilaos', 'maria.hilaos@jh.edu', password_hash('hilaos123', PASSWORD_DEFAULT), 'Maria', 'Hilaos', 'Computer Science', '3rd Year'],
        ['JH2024102', 'sapuay', 'john.sapuay@jh.edu', password_hash('sapuay123', PASSWORD_DEFAULT), 'John', 'Sapuay', 'Business Administration', '2nd Year'],
        ['JH2024103', 'legaspi', 'anna.legaspi@jh.edu', password_hash('legaspi123', PASSWORD_DEFAULT), 'Anna', 'Legaspi', 'Engineering', '4th Year']
    ];

    $stmt = $pdo->prepare("INSERT INTO users (student_id, username, email, password_hash, first_name, last_name, course, year_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($users as $user) {
        $stmt->execute($user);
    }

    echo "<div class='success'>✅ Test users created!</div>";

    // Insert categories
    echo "<p>⏳ Adding categories...</p>";
    $categories = [
        ['Books & Academic Materials', 'Textbooks and study materials', 'book'],
        ['Electronics', 'Laptops, phones, gadgets', 'laptop'],
        ['Clothing & Accessories', 'Clothes, shoes, bags', 'shirt'],
        ['Other', 'Miscellaneous items', 'package']
    ];

    $stmt = $pdo->prepare("INSERT INTO categories (name, description, icon) VALUES (?, ?, ?)");
    foreach ($categories as $cat) {
        $stmt->execute($cat);
    }

    echo "<div class='success'>✅ Categories added!</div>";

    echo "<div class='success'>
        <h2>🎉 SETUP COMPLETE!</h2>
        <p><strong>Your database is now ready!</strong></p>
        <p><strong>Test Accounts:</strong></p>
        <ul>
            <li>Username: <strong>hilaos</strong> | Password: <strong>hilaos123</strong></li>
            <li>Username: <strong>sapuay</strong> | Password: <strong>sapuay123</strong></li>
            <li>Username: <strong>legaspi</strong> | Password: <strong>legaspi123</strong></li>
        </ul>
    </div>";

    echo "<div style='text-align:center;margin-top:30px'>
        <a href='login.php' class='btn'>🔐 Go to Login</a>
        <a href='index.php' class='btn'>🏠 Homepage</a>
    </div>";

} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<div class='error'>
        <strong>Make sure:</strong><br>
        1. XAMPP is running<br>
        2. MySQL service is started<br>
        3. MySQL is on port 3306
    </div>";
}

echo "</div></body></html>";
?>
