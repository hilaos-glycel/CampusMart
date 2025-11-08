<?php
require_once 'config/config.php';

echo "<h1>🔧 CampusMart Post Item Fix</h1>";

try {
    $db = getDBConnection();
    echo "<p>✅ Database connection successful</p>";
    
    // Check and create categories table if missing
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM categories");
        $count = $stmt->fetchColumn();
        echo "<p>✅ Categories table exists with {$count} entries</p>";
        
        if ($count == 0) {
            echo "<p>⚠️ Adding default categories...</p>";
            
            $categories = [
                ['books', 'Books', 'Textbooks and academic materials'],
                ['electronics', 'Electronics', 'Laptops, phones, gadgets'],
                ['clothing', 'Clothing', 'Clothes and fashion items'],
                ['accessories', 'Accessories', 'Bags, jewelry, and accessories'],
                ['sports', 'Sports', 'Sports equipment and gear'],
                ['other', 'Other', 'Other items']
            ];
            
            foreach ($categories as $cat) {
                $stmt = $db->prepare("INSERT INTO categories (slug, name, description, created_at) VALUES (?, ?, ?, NOW())");
                $stmt->execute($cat);
            }
            echo "<p>✅ Default categories added</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Categories table issue: " . $e->getMessage() . "</p>";
        
        // Try to create categories table
        echo "<p>🔧 Creating categories table...</p>";
        $createCategoriesSQL = "
            CREATE TABLE IF NOT EXISTS categories (
                id INT PRIMARY KEY AUTO_INCREMENT,
                slug VARCHAR(50) UNIQUE NOT NULL,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ";
        
        $db->exec($createCategoriesSQL);
        echo "<p>✅ Categories table created</p>";
        
        // Add default categories
        $categories = [
            ['books', 'Books', 'Textbooks and academic materials'],
            ['electronics', 'Electronics', 'Laptops, phones, gadgets'],
            ['clothing', 'Clothing', 'Clothes and fashion items'],
            ['accessories', 'Accessories', 'Bags, jewelry, and accessories'],
            ['sports', 'Sports', 'Sports equipment and gear'],
            ['other', 'Other', 'Other items']
        ];
        
        foreach ($categories as $cat) {
            $stmt = $db->prepare("INSERT INTO categories (slug, name, description, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute($cat);
        }
        echo "<p>✅ Default categories added</p>";
    }
    
    // Check listings table
    try {
        $stmt = $db->query("DESCRIBE listings");
        echo "<p>✅ Listings table exists</p>";
    } catch (Exception $e) {
        echo "<p>❌ Listings table missing: " . $e->getMessage() . "</p>";
        echo "<p>🔧 Please run the main setup script to create all tables</p>";
    }
    
    // Check activity_logs table
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
    
    // Check and create uploads directory
    $uploadsDir = UPLOAD_PATH . 'listings/';
    if (!is_dir($uploadsDir)) {
        echo "<p>🔧 Creating uploads directory...</p>";
        if (mkdir($uploadsDir, 0755, true)) {
            echo "<p>✅ Uploads directory created: {$uploadsDir}</p>";
        } else {
            echo "<p>❌ Failed to create uploads directory</p>";
        }
    } else {
        echo "<p>✅ Uploads directory exists: {$uploadsDir}</p>";
    }
    
    // Test database connection function
    echo "<p>🧪 Testing getDBConnection() function...</p>";
    $testDb = getDBConnection();
    if ($testDb) {
        echo "<p>✅ getDBConnection() works correctly</p>";
    } else {
        echo "<p>❌ getDBConnection() failed</p>";
    }
    
    echo "<h2>🎉 Fix Complete!</h2>";
    echo "<p>The post item functionality should now work properly.</p>";
    echo "<p><a href='post-item.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Post Item</a></p>";
    echo "<p><a href='debug_post_item.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>Debug Info</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Critical error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration and ensure MySQL is running.</p>";
}

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
h1 { color: #28a745; }
h2 { color: #333; }
p { margin: 8px 0; }
a { margin-right: 10px; }
</style>";
?>
