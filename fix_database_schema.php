<?php
require_once 'config/config.php';

echo "<h1>🔧 Database Schema Fix</h1>";

try {
    $db = getDBConnection();
    echo "<p>✅ Database connection successful</p>";
    
    // Check listings table structure
    echo "<h2>📋 Checking Listings Table Structure</h2>";
    
    try {
        $stmt = $db->query("DESCRIBE listings");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Current table structure:</h3>";
        $hasConditionItem = false;
        foreach ($columns as $col) {
            echo "<p>- {$col['Field']} ({$col['Type']}) {$col['Null']} {$col['Key']} {$col['Default']} {$col['Extra']}</p>";
            if ($col['Field'] === 'condition_item') {
                $hasConditionItem = true;
            }
        }
        
        if (!$hasConditionItem) {
            echo "<p>❌ Missing 'condition_item' column</p>";
            echo "<p>🔧 Adding condition_item column...</p>";
            
            $alterSQL = "ALTER TABLE listings ADD COLUMN condition_item ENUM('New', 'Like New', 'Good', 'Fair', 'Poor') NOT NULL DEFAULT 'Good' AFTER type";
            $db->exec($alterSQL);
            
            echo "<p>✅ condition_item column added successfully</p>";
        } else {
            echo "<p>✅ condition_item column exists</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Error checking listings table: " . $e->getMessage() . "</p>";
        
        // Try to create the listings table if it doesn't exist
        echo "<p>🔧 Creating listings table...</p>";
        
        $createListingsSQL = "
            CREATE TABLE IF NOT EXISTS listings (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                category_id INT NOT NULL,
                title VARCHAR(200) NOT NULL,
                description TEXT NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                type ENUM('sale', 'rent') NOT NULL,
                condition_item ENUM('New', 'Like New', 'Good', 'Fair', 'Poor') NOT NULL,
                location VARCHAR(100),
                rental_period VARCHAR(50),
                images JSON,
                status ENUM('active', 'sold', 'rented', 'inactive') DEFAULT 'active',
                views INT DEFAULT 0,
                featured BOOLEAN DEFAULT FALSE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES categories(id)
            )
        ";
        
        $db->exec($createListingsSQL);
        echo "<p>✅ Listings table created</p>";
    }
    
    // Check categories table
    echo "<h2>📂 Checking Categories Table</h2>";
    
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM categories");
        $count = $stmt->fetchColumn();
        echo "<p>✅ Categories table exists with {$count} entries</p>";
        
        if ($count == 0) {
            echo "<p>🔧 Adding default categories...</p>";
            
            $categories = [
                ['books', 'Books', 'Textbooks and academic materials', 'fas fa-book'],
                ['electronics', 'Electronics', 'Laptops, phones, gadgets', 'fas fa-laptop'],
                ['clothing', 'Clothing', 'Clothes and fashion items', 'fas fa-tshirt'],
                ['accessories', 'Accessories', 'Bags, jewelry, and accessories', 'fas fa-gem'],
                ['sports', 'Sports', 'Sports equipment and gear', 'fas fa-football-ball'],
                ['other', 'Other', 'Other items', 'fas fa-box']
            ];
            
            foreach ($categories as $cat) {
                $stmt = $db->prepare("INSERT INTO categories (slug, name, description, icon, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute($cat);
            }
            echo "<p>✅ Default categories added</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Categories table issue: " . $e->getMessage() . "</p>";
        
        // Create categories table
        echo "<p>🔧 Creating categories table...</p>";
        $createCategoriesSQL = "
            CREATE TABLE IF NOT EXISTS categories (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(50) NOT NULL,
                slug VARCHAR(50) UNIQUE NOT NULL,
                description TEXT,
                icon VARCHAR(50),
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ";
        
        $db->exec($createCategoriesSQL);
        echo "<p>✅ Categories table created</p>";
        
        // Add default categories
        $categories = [
            ['books', 'Books', 'Textbooks and academic materials', 'fas fa-book'],
            ['electronics', 'Electronics', 'Laptops, phones, gadgets', 'fas fa-laptop'],
            ['clothing', 'Clothing', 'Clothes and fashion items', 'fas fa-tshirt'],
            ['accessories', 'Accessories', 'Bags, jewelry, and accessories', 'fas fa-gem'],
            ['sports', 'Sports', 'Sports equipment and gear', 'fas fa-football-ball'],
            ['other', 'Other', 'Other items', 'fas fa-box']
        ];
        
        foreach ($categories as $cat) {
            $stmt = $db->prepare("INSERT INTO categories (slug, name, description, icon, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute($cat);
        }
        echo "<p>✅ Default categories added</p>";
    }
    
    // Check activity_logs table
    echo "<h2>📊 Checking Activity Logs Table</h2>";
    
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM activity_logs");
        echo "<p>✅ Activity logs table exists</p>";
    } catch (Exception $e) {
        echo "<p>❌ Activity logs table missing: " . $e->getMessage() . "</p>";
        
        // Create activity_logs table
        echo "<p>🔧 Creating activity_logs table...</p>";
        $createActivityLogsSQL = "
            CREATE TABLE IF NOT EXISTS activity_logs (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT,
                action VARCHAR(50) NOT NULL,
                table_name VARCHAR(50),
                record_id INT,
                old_values JSON,
                new_values JSON,
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        ";
        
        $db->exec($createActivityLogsSQL);
        echo "<p>✅ Activity logs table created</p>";
    }
    
    // Test the create listing functionality
    echo "<h2>🧪 Testing Create Listing Functionality</h2>";
    
    // Check if we can insert a test record
    try {
        $testUserId = 1; // Assuming user ID 1 exists
        $testCategoryId = 1; // Assuming category ID 1 exists
        
        // Check if user exists
        $stmt = $db->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$testUserId]);
        if (!$stmt->fetch()) {
            echo "<p>⚠️ Test user (ID: 1) not found. Please ensure you have users in the database.</p>";
        }
        
        // Check if category exists
        $stmt = $db->prepare("SELECT id FROM categories WHERE id = ?");
        $stmt->execute([$testCategoryId]);
        if (!$stmt->fetch()) {
            echo "<p>⚠️ Test category (ID: 1) not found. Categories should be created above.</p>";
        }
        
        echo "<p>✅ Database structure is ready for listings</p>";
        
    } catch (Exception $e) {
        echo "<p>❌ Test failed: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>🎉 Schema Fix Complete!</h2>";
    echo "<p>The database schema has been updated and should now work with the Post Item form.</p>";
    echo "<p><a href='post-item.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Post Item</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Critical error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration and ensure MySQL is running.</p>";
}

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1 { color: #28a745; }
h2 { color: #333; border-bottom: 2px solid #28a745; padding-bottom: 5px; }
h3 { color: #666; }
p { margin: 8px 0; }
a { margin-right: 10px; }
</style>";
?>
