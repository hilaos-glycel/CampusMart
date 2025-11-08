<?php
/**
 * Add Sample Listings for Dashboard Demo
 */

require_once 'config/config.php';

try {
    $db = getDBConnection();
    
    // Get user IDs
    $stmt = $db->query("SELECT id, username, first_name FROM users");
    $users = $stmt->fetchAll();
    
    echo "<h2>Adding Sample Listings</h2>";
    
    // Sample listings data
    $sampleListings = [
        [
            'title' => 'Programming Textbook - Java Complete Reference',
            'description' => 'Comprehensive Java programming book in excellent condition. Perfect for Computer Science students.',
            'price' => 850.00,
            'category' => 'Books',
            'condition' => 'Like New',
            'username' => 'hilaos'
        ],
        [
            'title' => 'Gaming Mouse - Logitech G502',
            'description' => 'High-performance gaming mouse with customizable buttons. Great for gaming and programming.',
            'price' => 2500.00,
            'category' => 'Electronics',
            'condition' => 'Good',
            'username' => 'hilaos'
        ],
        [
            'title' => 'Laptop Stand - Adjustable Aluminum',
            'description' => 'Ergonomic laptop stand to improve posture during long study sessions.',
            'price' => 1200.00,
            'category' => 'Accessories',
            'condition' => 'Excellent',
            'username' => 'hilaos'
        ],
        [
            'title' => 'Business Management Textbook',
            'description' => 'Essential business management principles book for BA students.',
            'price' => 750.00,
            'category' => 'Books',
            'condition' => 'Good',
            'username' => 'sapuay'
        ],
        [
            'title' => 'Engineering Calculator - Casio FX-991EX',
            'description' => 'Scientific calculator perfect for engineering calculations.',
            'price' => 1800.00,
            'category' => 'Electronics',
            'condition' => 'Like New',
            'username' => 'legaspi'
        ],
        [
            'title' => 'Mountain Bike - Trek 26"',
            'description' => 'Reliable mountain bike for campus transportation and weekend adventures.',
            'price' => 15000.00,
            'category' => 'Sports',
            'condition' => 'Good',
            'username' => 'legaspi'
        ]
    ];
    
    // Create categories first
    $categories = ['Books', 'Electronics', 'Accessories', 'Sports'];
    foreach ($categories as $category) {
        $stmt = $db->prepare("INSERT IGNORE INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute([$category, $category . ' category']);
    }
    
    // Insert listings
    foreach ($sampleListings as $listing) {
        // Get user ID
        $userStmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $userStmt->execute([$listing['username']]);
        $user = $userStmt->fetch();
        
        if ($user) {
            // Get category ID
            $catStmt = $db->prepare("SELECT id FROM categories WHERE name = ?");
            $catStmt->execute([$listing['category']]);
            $category = $catStmt->fetch();
            
            if ($category) {
                $stmt = $db->prepare("
                    INSERT INTO listings (user_id, title, description, price, category_id, condition_item, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())
                ");
                
                $result = $stmt->execute([
                    $user['id'],
                    $listing['title'],
                    $listing['description'],
                    $listing['price'],
                    $category['id'],
                    $listing['condition']
                ]);
                
                if ($result) {
                    echo "✅ Added listing: " . $listing['title'] . " for " . $listing['username'] . "<br>";
                } else {
                    echo "❌ Failed to add: " . $listing['title'] . "<br>";
                }
            }
        }
    }
    
    // Show current stats
    echo "<h3>Current Statistics:</h3>";
    foreach ($users as $user) {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM listings WHERE user_id = ?");
        $stmt->execute([$user['id']]);
        $count = $stmt->fetch();
        echo $user['first_name'] . " (" . $user['username'] . "): " . $count['count'] . " listings<br>";
    }
    
    echo "<br><a href='dashboard.php'>Go to Dashboard</a> | <a href='marketplace.php'>View Marketplace</a>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
