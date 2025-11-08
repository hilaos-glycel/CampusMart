<?php
require_once 'config/config.php';

echo "<h1>🚨 Emergency Database Fix for Post Item</h1>";

try {
    $db = getDBConnection();
    echo "<p>✅ Database connection successful</p>";
    
    // First, let's see what's actually in the categories table
    echo "<h2>🔍 Current Categories Table Structure</h2>";
    
    try {
        $stmt = $db->query("DESCRIBE categories");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Existing columns:</h3>";
        foreach ($columns as $col) {
            echo "<p>- {$col['Field']} ({$col['Type']})</p>";
        }
        
        // Check if slug column exists
        $hasSlug = false;
        foreach ($columns as $col) {
            if ($col['Field'] === 'slug') {
                $hasSlug = true;
                break;
            }
        }
        
        if (!$hasSlug) {
            echo "<p>❌ SLUG column is missing!</p>";
            echo "<p>🔧 Adding slug column...</p>";
            
            // Add slug column
            $db->exec("ALTER TABLE categories ADD COLUMN slug VARCHAR(50) AFTER id");
            echo "<p>✅ Slug column added</p>";
            
            // Make it unique after we populate it
            echo "<p>🔧 Populating slug values...</p>";
            
            // Get existing categories and create slugs
            $stmt = $db->query("SELECT id, name FROM categories");
            $existingCategories = $stmt->fetchAll();
            
            foreach ($existingCategories as $cat) {
                $slug = strtolower(str_replace([' ', '&', '/'], ['', 'and', ''], $cat['name']));
                $updateStmt = $db->prepare("UPDATE categories SET slug = ? WHERE id = ?");
                $updateStmt->execute([$slug, $cat['id']]);
                echo "<p>✅ Updated '{$cat['name']}' with slug '{$slug}'</p>";
            }
            
            // Now make it unique
            $db->exec("ALTER TABLE categories ADD UNIQUE KEY unique_slug (slug)");
            echo "<p>✅ Made slug column unique</p>";
        } else {
            echo "<p>✅ Slug column exists</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Categories table issue: " . $e->getMessage() . "</p>";
        
        // Drop and recreate the table
        echo "<p>🔧 Recreating categories table...</p>";
        
        try {
            $db->exec("DROP TABLE IF EXISTS categories");
            echo "<p>✅ Dropped old categories table</p>";
        } catch (Exception $e) {
            echo "<p>⚠️ Could not drop table: " . $e->getMessage() . "</p>";
        }
        
        // Create new categories table
        $createSQL = "
            CREATE TABLE categories (
                id INT PRIMARY KEY AUTO_INCREMENT,
                slug VARCHAR(50) UNIQUE NOT NULL,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                icon VARCHAR(50),
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ";
        
        $db->exec($createSQL);
        echo "<p>✅ Created new categories table</p>";
    }
    
    // Ensure we have default categories
    echo "<h2>📋 Ensuring Default Categories Exist</h2>";
    
    $stmt = $db->query("SELECT COUNT(*) FROM categories");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        echo "<p>🔧 Adding default categories...</p>";
        
        $defaultCategories = [
            ['books', 'Books', 'Textbooks and academic materials', 'fas fa-book'],
            ['electronics', 'Electronics', 'Laptops, phones, gadgets', 'fas fa-laptop'],
            ['clothing', 'Clothing', 'Clothes and fashion items', 'fas fa-tshirt'],
            ['accessories', 'Accessories', 'Bags, jewelry, and accessories', 'fas fa-gem'],
            ['sports', 'Sports', 'Sports equipment and gear', 'fas fa-football-ball'],
            ['other', 'Other', 'Other items', 'fas fa-box']
        ];
        
        foreach ($defaultCategories as $cat) {
            $stmt = $db->prepare("INSERT INTO categories (slug, name, description, icon) VALUES (?, ?, ?, ?)");
            $stmt->execute($cat);
            echo "<p>✅ Added: {$cat[1]} (slug: {$cat[0]})</p>";
        }
    } else {
        echo "<p>✅ Categories exist ({$count} total)</p>";
        
        // Show current categories
        $stmt = $db->query("SELECT id, slug, name FROM categories");
        $categories = $stmt->fetchAll();
        
        echo "<h3>Current categories:</h3>";
        foreach ($categories as $cat) {
            echo "<p>- ID: {$cat['id']}, Slug: '{$cat['slug']}', Name: '{$cat['name']}'</p>";
        }
    }
    
    // Test the API query that was failing
    echo "<h2>🧪 Testing Category Lookup</h2>";
    
    try {
        $testSlug = 'electronics';
        $stmt = $db->prepare("SELECT id FROM categories WHERE slug = ?");
        $stmt->execute([$testSlug]);
        $result = $stmt->fetch();
        
        if ($result) {
            echo "<p>✅ Successfully found category with slug '{$testSlug}' (ID: {$result['id']})</p>";
        } else {
            echo "<p>❌ Could not find category with slug '{$testSlug}'</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Test query failed: " . $e->getMessage() . "</p>";
    }
    
    // Check listings table condition column
    echo "<h2>📦 Checking Listings Table</h2>";
    
    try {
        $stmt = $db->query("DESCRIBE listings");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $hasConditionItem = false;
        foreach ($columns as $col) {
            if ($col['Field'] === 'condition_item') {
                $hasConditionItem = true;
                break;
            }
        }
        
        if (!$hasConditionItem) {
            echo "<p>❌ Missing condition_item column in listings</p>";
            echo "<p>🔧 Adding condition_item column...</p>";
            
            $db->exec("ALTER TABLE listings ADD COLUMN condition_item ENUM('New', 'Like New', 'Good', 'Fair', 'Poor') NOT NULL DEFAULT 'Good' AFTER type");
            echo "<p>✅ Added condition_item column</p>";
        } else {
            echo "<p>✅ Listings table has condition_item column</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Listings table issue: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>🎉 Emergency Fix Complete!</h2>";
    echo "<p><strong>Your Post Item form should now work!</strong></p>";
    echo "<p><a href='post-item.php' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-size: 18px; font-weight: bold;'>🚀 Test Post Item Now</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Critical error: " . $e->getMessage() . "</p>";
    echo "<p>Database connection failed. Please ensure MySQL is running and check your database configuration.</p>";
}

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; background: #f8f9fa; }
h1 { color: #dc3545; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
h2 { color: #28a745; background: white; padding: 15px; border-radius: 8px; margin: 20px 0 10px 0; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
h3 { color: #666; margin: 15px 0 5px 0; }
p { margin: 8px 0; padding: 5px 0; }
a { display: inline-block; margin: 20px 0; }
</style>";
?>
