<?php
/**
 * Instant Database Fix for CampusMart
 * This will create the database and fix the "Database error occurred" issue
 */

set_time_limit(300); // 5 minutes max execution time

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instant Fix - CampusMart Database</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; background: #f8f9fa; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #dc3545; text-align: center; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .progress { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .btn { display: inline-block; padding: 12px 24px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; font-weight: bold; }
        .btn:hover { background: #218838; }
        .btn-primary { background: #667eea; }
        .btn-primary:hover { background: #5a67d8; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 14px; }
        .step { margin: 20px 0; padding: 15px; border-left: 4px solid #28a745; background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚨 Instant Database Fix</h1>
        <div class="info">
            <strong>Fixing "Database error occurred" issue...</strong><br>
            This will create the CampusMart database and all required tables.
        </div>

        <?php
        echo "<div class='progress'>⏳ Starting database setup...</div>";
        flush();

        try {
            // Step 1: Connect to MySQL
            echo "<div class='step'><strong>Step 1:</strong> Connecting to MySQL...</div>";
            flush();
            
            $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<div class='success'>✅ MySQL connection successful!</div>";
            flush();

            // Step 2: Create database
            echo "<div class='step'><strong>Step 2:</strong> Creating campusmart database...</div>";
            flush();
            
            $pdo->exec("DROP DATABASE IF EXISTS campusmart");
            $pdo->exec("CREATE DATABASE campusmart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE campusmart");
            echo "<div class='success'>✅ Database 'campusmart' created successfully!</div>";
            flush();

            // Step 3: Create tables
            echo "<div class='step'><strong>Step 3:</strong> Creating database tables...</div>";
            flush();

            // Users table
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
                    email_verified BOOLEAN DEFAULT FALSE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");

            // Categories table
            $pdo->exec("
                CREATE TABLE categories (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    description TEXT,
                    icon VARCHAR(50),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");

            // Listings table
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

            // Services table
            $pdo->exec("
                CREATE TABLE services (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    title VARCHAR(200) NOT NULL,
                    description TEXT NOT NULL,
                    category VARCHAR(100) NOT NULL,
                    price_type ENUM('hourly', 'fixed', 'negotiable') NOT NULL,
                    price DECIMAL(10,2),
                    availability TEXT,
                    status ENUM('active', 'inactive') DEFAULT 'active',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ");

            // Failed login attempts table
            $pdo->exec("
                CREATE TABLE failed_login_attempts (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    login_id VARCHAR(100) NOT NULL,
                    ip_address VARCHAR(45) NOT NULL,
                    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_login_id_time (login_id, attempt_time)
                )
            ");

            echo "<div class='success'>✅ All database tables created successfully!</div>";
            flush();

            // Step 4: Insert categories
            echo "<div class='step'><strong>Step 4:</strong> Adding categories...</div>";
            flush();

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

            $stmt = $pdo->prepare("INSERT INTO categories (name, description, icon) VALUES (?, ?, ?)");
            foreach ($categories as $category) {
                $stmt->execute($category);
            }

            echo "<div class='success'>✅ Categories added successfully!</div>";
            flush();

            // Step 5: Create test users
            echo "<div class='step'><strong>Step 5:</strong> Creating test user accounts...</div>";
            flush();

            $testUsers = [
                [
                    'student_id' => 'JH2024101',
                    'username' => 'hilaos',
                    'email' => 'maria.hilaos@jh.edu',
                    'password' => 'hilaos123',
                    'first_name' => 'Maria',
                    'last_name' => 'Hilaos',
                    'course' => 'Computer Science',
                    'year_level' => '3rd Year',
                    'bio' => 'Computer Science student passionate about programming and technology.'
                ],
                [
                    'student_id' => 'JH2024102',
                    'username' => 'sapuay',
                    'email' => 'john.sapuay@jh.edu',
                    'password' => 'sapuay123',
                    'first_name' => 'John',
                    'last_name' => 'Sapuay',
                    'course' => 'Business Administration',
                    'year_level' => '2nd Year',
                    'bio' => 'Business Administration student with interests in entrepreneurship.'
                ],
                [
                    'student_id' => 'JH2024103',
                    'username' => 'legaspi',
                    'email' => 'anna.legaspi@jh.edu',
                    'password' => 'legaspi123',
                    'first_name' => 'Anna',
                    'last_name' => 'Legaspi',
                    'course' => 'Engineering',
                    'year_level' => '4th Year',
                    'bio' => 'Engineering student specializing in mathematics and physics.'
                ]
            ];

            $stmt = $pdo->prepare("
                INSERT INTO users (student_id, username, email, password_hash, first_name, last_name, course, year_level, bio, email_verified) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
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
                    $user['year_level'],
                    $user['bio']
                ]);
            }

            echo "<div class='success'>✅ Test user accounts created successfully!</div>";
            flush();

            // Step 6: Add sample listings
            echo "<div class='step'><strong>Step 6:</strong> Adding sample marketplace listings...</div>";
            flush();

            $sampleListings = [
                [1, 1, 'Programming Textbook - Java Complete Reference', 'Comprehensive Java programming textbook in excellent condition. Perfect for CS students.', 850.00, 'good'],
                [1, 2, 'Gaming Mouse - Logitech G502', 'High-performance gaming mouse with customizable buttons. Barely used.', 2500.00, 'like_new'],
                [2, 1, 'Business Management Textbook', 'Essential business management textbook for BA students. Well-maintained.', 750.00, 'good'],
                [3, 1, 'Engineering Mathematics Book', 'Advanced engineering mathematics reference book. Great for engineering students.', 950.00, 'good'],
                [3, 6, 'Mountain Bike', 'Reliable mountain bike perfect for campus transportation. Recently serviced.', 8500.00, 'good']
            ];

            $stmt = $pdo->prepare("
                INSERT INTO listings (user_id, category_id, title, description, price, condition_type, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'available')
            ");

            foreach ($sampleListings as $listing) {
                $stmt->execute($listing);
            }

            echo "<div class='success'>✅ Sample listings added successfully!</div>";
            flush();

            // Step 7: Add sample services
            echo "<div class='step'><strong>Step 7:</strong> Adding sample services...</div>";
            flush();

            $sampleServices = [
                [1, 'Programming Tutoring (Java & Python)', 'Experienced tutor offering programming lessons in Java and Python. Perfect for beginners and intermediate students.', 'Tutoring', 'hourly', 300.00, 'Weekdays 6-9 PM, Weekends flexible'],
                [2, 'Business Plan Writing Service', 'Professional business plan writing and consultation services for student entrepreneurs.', 'Writing', 'fixed', 1500.00, 'Available on weekends'],
                [3, 'Mathematics & Physics Tutoring', 'Expert tutoring in advanced mathematics and physics for engineering students.', 'Tutoring', 'hourly', 350.00, 'Evenings and weekends available']
            ];

            $stmt = $pdo->prepare("
                INSERT INTO services (user_id, title, description, category, price_type, price, availability) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($sampleServices as $service) {
                $stmt->execute($service);
            }

            echo "<div class='success'>✅ Sample services added successfully!</div>";
            flush();

            // Final verification
            echo "<div class='step'><strong>Final Step:</strong> Verifying database setup...</div>";
            flush();

            $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
            $userCount = $stmt->fetch()['count'];

            $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
            $categoryCount = $stmt->fetch()['count'];

            $stmt = $pdo->query("SELECT COUNT(*) as count FROM listings");
            $listingCount = $stmt->fetch()['count'];

            echo "<div class='success'>
                ✅ Database setup completed successfully!<br><br>
                <strong>Summary:</strong><br>
                • Users: $userCount<br>
                • Categories: $categoryCount<br>
                • Listings: $listingCount<br>
                • Services: 3
            </div>";

            echo "<div class='info'>
                <strong>🎉 SUCCESS! Your CampusMart database is now ready!</strong><br><br>
                <strong>Test Accounts Created:</strong><br>
                • Username: <strong>hilaos</strong> | Password: <strong>hilaos123</strong><br>
                • Username: <strong>sapuay</strong> | Password: <strong>sapuay123</strong><br>
                • Username: <strong>legaspi</strong> | Password: <strong>legaspi123</strong>
            </div>";

        } catch (PDOException $e) {
            echo "<div class='error'>❌ Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
            echo "<div class='info'>
                <strong>Troubleshooting:</strong><br>
                1. Make sure XAMPP MySQL service is running<br>
                2. Check if MySQL is on port 3306<br>
                3. Verify MySQL credentials (root with no password)<br>
                4. Try restarting XAMPP services
            </div>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        ?>

        <div style="text-align: center; margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px;">
            <h2>🚀 Ready to Use CampusMart!</h2>
            <p>Your database is now set up. You can start using the system:</p>
            
            <a href="login.php" class="btn btn-primary">🔐 Go to Login Page</a>
            <a href="simple_login_test.php" class="btn">🧪 Test Login</a>
            <a href="index.php" class="btn">🏠 Homepage</a>
            
            <div style="margin-top: 20px; font-size: 14px; color: #666;">
                <strong>Quick Test:</strong> Use username <code>hilaos</code> and password <code>hilaos123</code>
            </div>
        </div>
    </div>
</body>
</html>
