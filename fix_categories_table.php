<?php
require_once 'config/config.php';

echo "<h1>🔧 Fix Categories Table Schema</h1>";

try {
    $db = getDBConnection();
    echo "<p>✅ Database connection successful</p>";
    
    // Check categories table structure
    echo "<h2>📂 Checking Categories Table Structure</h2>";
    
    try {
        $stmt = $db->query("DESCRIBE categories");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Current categories table structure:</h3>";
        $hasSlug = false;
        $hasName = false;
        $hasDescription = false;
        
        foreach ($columns as $col) {
            echo "<p>- {$col['Field']} ({$col['Type']}) {$col['Null']} {$col['Key']}</p>";
            if ($col['Field'] === 'slug') $hasSlug = true;
            if ($col['Field'] === 'name') $hasName = true;
            if ($col['Field'] === 'description') $hasDescription = true;
        }
        
        // Add missing columns
        if (!$hasSlug) {
            echo "<p>❌ Missing 'slug' column</p>";
            echo "<p>🔧 Adding slug column...</p>";
            $db->exec("ALTER TABLE categories ADD COLUMN slug VARCHAR(50) UNIQUE NOT NULL DEFAULT '' AFTER id");
            echo "<p>✅ slug column added</p>";
        } else {
            echo "<p>✅ slug column exists</p>";
        }
        
        if (!$hasName) {
            echo "<p>❌ Missing 'name' column</p>";
            echo "<p>🔧 Adding name column...</p>";
            $db->exec("ALTER TABLE categories ADD COLUMN name VARCHAR(100) NOT NULL DEFAULT '' AFTER slug");
            echo "<p>✅ name column added</p>";
        } else {
            echo "<p>✅ name column exists</p>";
        }
        
        if (!$hasDescription) {
            echo "<p>❌ Missing 'description' column</p>";
            echo "<p>🔧 Adding description column...</p>";
            $db->exec("ALTER TABLE categories ADD COLUMN description TEXT AFTER name");
            echo "<p>✅ description column added</p>";
        } else {
            echo "<p>✅ description column exists</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Categories table doesn't exist or has issues: " . $e->getMessage() . "</p>";
        
        // Create categories table with proper structure
        echo "<p>🔧 Creating categories table with proper structure...</p>";
        
        $createCategoriesSQL = "
            CREATE TABLE IF NOT EXISTS categories (
                id INT PRIMARY KEY AUTO_INCREMENT,
                slug VARCHAR(50) UNIQUE NOT NULL,
                name VARCHAR(100) NOT NULL,
                description TEXT,
                icon VARCHAR(50),
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ";
        
        $db->exec($createCategoriesSQL);
        echo "<p>✅ Categories table created with proper structure</p>";
    }
    
    // Check if categories have data and add default categories
    echo "<h2>📋 Checking Categories Data</h2>";
    
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM categories");
        $count = $stmt->fetchColumn();
        echo "<p>Current categories count: {$count}</p>";
        
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
                try {
                    $stmt = $db->prepare("INSERT INTO categories (slug, name, description, icon, created_at) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->execute($cat);
                    echo "<p>✅ Added category: {$cat[1]} ({$cat[0]})</p>";
                } catch (Exception $e) {
                    echo "<p>⚠️ Error adding category {$cat[1]}: " . $e->getMessage() . "</p>";
                }
            }
            
        } else {
            // Check if existing categories have slugs
            echo "<p>🔧 Checking existing categories for missing slugs...</p>";
            
            $stmt = $db->query("SELECT id, name, slug FROM categories WHERE slug = '' OR slug IS NULL");
            $categoriesWithoutSlugs = $stmt->fetchAll();
            
            foreach ($categoriesWithoutSlugs as $cat) {
                $slug = strtolower(str_replace(' ', '', $cat['name']));
                $updateStmt = $db->prepare("UPDATE categories SET slug = ? WHERE id = ?");
                $updateStmt->execute([$slug, $cat['id']]);
                echo "<p>✅ Updated category '{$cat['name']}' with slug '{$slug}'</p>";
            }
            
            // Display current categories
            echo "<h3>Current categories:</h3>";
            $stmt = $db->query("SELECT id, slug, name FROM categories");
            $allCategories = $stmt->fetchAll();
            
            foreach ($allCategories as $cat) {
                echo "<p>- ID: {$cat['id']}, Slug: '{$cat['slug']}', Name: '{$cat['name']}'</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Error working with categories data: " . $e->getMessage() . "</p>";
    }
    
    // Test the category lookup that was failing
    echo "<h2>🧪 Testing Category Lookup</h2>";
    
    try {
        $testSlug = 'books';
        $stmt = $db->prepare("SELECT id, name, slug FROM categories WHERE slug = ?");
        $stmt->execute([$testSlug]);
        $category = $stmt->fetch();
        
        if ($category) {
            echo "<p>✅ Successfully found category with slug '{$testSlug}': {$category['name']} (ID: {$category['id']})</p>";
        } else {
            echo "<p>⚠️ No category found with slug '{$testSlug}'</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Error testing category lookup: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>🎉 Categories Table Fix Complete!</h2>";
    echo "<p>The categories table now has the proper structure with slug column.</p>";
    echo "<p><a href='post-item.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Post Item</a></p>";
    echo "<p><a href='marketplace.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>View Marketplace</a></p>";
    
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
