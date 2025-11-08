<?php
/**
 * CampusMart Setup Script
 * Initializes the database and creates sample data
 */

require_once 'config/config.php';

// Check if database exists and create if not
try {
    $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS campusmart");
    $pdo->exec("USE campusmart");
    
    echo "<h2>Database 'campusmart' created/verified successfully!</h2>";
    
    // Read and execute SQL schema
    $sqlFile = __DIR__ . '/database/campusmart_schema.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        
        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^--/', $statement)) {
                try {
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    // Ignore table already exists errors
                    if (strpos($e->getMessage(), 'already exists') === false) {
                        echo "<p style='color: red;'>Error executing: " . substr($statement, 0, 50) . "...</p>";
                        echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
                    }
                }
            }
        }
        
        echo "<h3>Database schema created successfully!</h3>";
    } else {
        echo "<p style='color: red;'>SQL schema file not found: $sqlFile</p>";
    }
    
    // Create sample users
    createSampleUsers($pdo);
    
    // Create sample listings
    createSampleListings($pdo);
    
    echo "<h2>Setup completed successfully!</h2>";
    echo "<h3>Test Accounts:</h3>";
    echo "<ul>";
    echo "<li><strong>Maria Hilaos:</strong> Username: hilaos, Password: hilaos123</li>";
    echo "<li><strong>John Sapuay:</strong> Username: sapuay, Password: sapuay123</li>";
    echo "<li><strong>Anna Legaspi:</strong> Username: legaspi, Password: legaspi123</li>";
    echo "</ul>";
    echo "<p><a href='index.php'>Go to CampusMart</a></p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Database Error:</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}

function createSampleUsers($pdo) {
    $users = [
        [
            'student_id' => 'JH2024101',
            'username' => 'hilaos',
            'email' => 'maria.hilaos@jh.edu',
            'password' => password_hash('hilaos123', PASSWORD_DEFAULT),
            'first_name' => 'Maria',
            'last_name' => 'Hilaos',
            'course' => 'Computer Science',
            'year_level' => '3rd Year',
            'bio' => 'Computer Science student passionate about programming and technology. Selling items I no longer need!'
        ],
        [
            'student_id' => 'JH2024102',
            'username' => 'sapuay',
            'email' => 'john.sapuay@jh.edu',
            'password' => password_hash('sapuay123', PASSWORD_DEFAULT),
            'first_name' => 'John',
            'last_name' => 'Sapuay',
            'course' => 'Business Administration',
            'year_level' => '2nd Year',
            'bio' => 'Business student interested in entrepreneurship and helping fellow students with business-related services.'
        ],
        [
            'student_id' => 'JH2024103',
            'username' => 'legaspi',
            'email' => 'anna.legaspi@jh.edu',
            'password' => password_hash('legaspi123', PASSWORD_DEFAULT),
            'first_name' => 'Anna',
            'last_name' => 'Legaspi',
            'course' => 'Engineering',
            'year_level' => '4th Year',
            'bio' => 'Engineering student specializing in mathematics and physics. Available for tutoring and selling engineering materials.'
        ]
    ];
    
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO users (student_id, username, email, password_hash, first_name, last_name, course, year_level, bio, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
    ");
    
    foreach ($users as $user) {
        try {
            $stmt->execute([
                $user['student_id'],
                $user['username'],
                $user['email'],
                $user['password'],
                $user['first_name'],
                $user['last_name'],
                $user['course'],
                $user['year_level'],
                $user['bio']
            ]);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                echo "<p style='color: orange;'>Warning: Could not create user {$user['username']}: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    echo "<h3>Sample users created!</h3>";
}

function createSampleListings($pdo) {
    // Get user IDs
    $stmt = $pdo->query("SELECT id, username FROM users WHERE username IN ('hilaos', 'sapuay', 'legaspi')");
    $users = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    if (empty($users)) {
        echo "<p style='color: orange;'>No users found for creating sample listings</p>";
        return;
    }
    
    // Get category IDs
    $stmt = $pdo->query("SELECT id, slug FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $listings = [
        [
            'user_id' => $users['hilaos'] ?? 1,
            'category_id' => array_search('books', $categories) ?: 1,
            'title' => 'Programming Textbook - Java Complete Reference',
            'description' => 'Comprehensive Java programming textbook in excellent condition. Perfect for Computer Science students.',
            'price' => 2500.00,
            'type' => 'sale',
            'condition_item' => 'Like New',
            'location' => 'IT Building'
        ],
        [
            'user_id' => $users['hilaos'] ?? 1,
            'category_id' => array_search('electronics', $categories) ?: 2,
            'title' => 'Gaming Mouse - Logitech G502',
            'description' => 'High-performance gaming mouse with programmable buttons. Great for gaming and productivity.',
            'price' => 1800.00,
            'type' => 'sale',
            'condition_item' => 'Good',
            'location' => 'Main Campus'
        ],
        [
            'user_id' => $users['sapuay'] ?? 1,
            'category_id' => array_search('books', $categories) ?: 1,
            'title' => 'Business Administration Textbook Set',
            'description' => 'Complete set of business textbooks for 2nd year students. Includes Marketing, Management, and Finance.',
            'price' => 4500.00,
            'type' => 'sale',
            'condition_item' => 'Good',
            'location' => 'Library Area'
        ],
        [
            'user_id' => $users['sapuay'] ?? 1,
            'category_id' => array_search('furniture', $categories) ?: 4,
            'title' => 'Study Desk with Drawers',
            'description' => 'Wooden study desk with multiple drawers for storage. Perfect for dorm room or study area.',
            'price' => 3200.00,
            'type' => 'sale',
            'condition_item' => 'Good',
            'location' => 'Near Dormitory'
        ],
        [
            'user_id' => $users['legaspi'] ?? 1,
            'category_id' => array_search('books', $categories) ?: 1,
            'title' => 'Engineering Mathematics Books',
            'description' => 'Advanced engineering mathematics textbooks. Essential for engineering students.',
            'price' => 3800.00,
            'type' => 'sale',
            'condition_item' => 'Good',
            'location' => 'Engineering Building'
        ],
        [
            'user_id' => $users['legaspi'] ?? 1,
            'category_id' => array_search('school-supplies', $categories) ?: 1,
            'title' => 'Scientific Calculator TI-89',
            'description' => 'Advanced scientific calculator perfect for engineering and mathematics courses.',
            'price' => 4200.00,
            'type' => 'sale',
            'condition_item' => 'Excellent',
            'location' => 'Engineering Building'
        ]
    ];
    
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO listings (user_id, category_id, title, description, price, type, condition_item, location, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
    ");
    
    foreach ($listings as $listing) {
        try {
            $stmt->execute([
                $listing['user_id'],
                $listing['category_id'],
                $listing['title'],
                $listing['description'],
                $listing['price'],
                $listing['type'],
                $listing['condition_item'],
                $listing['location']
            ]);
        } catch (PDOException $e) {
            echo "<p style='color: orange;'>Warning: Could not create listing '{$listing['title']}': " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h3>Sample listings created!</h3>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusMart Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h2 { color: #28a745; }
        h3 { color: #667eea; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        ul { background: #f8f9fa; padding: 20px; border-radius: 5px; }
        li { margin: 10px 0; }
    </style>
</head>
<body>
    <h1>CampusMart Setup</h1>
</body>
</html>
