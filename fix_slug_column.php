<?php
/**
 * Database Schema Fix - Add Missing Slug Column
 * Fixes the "Unknown column 'slug' in 'where clause'" error
 */

require_once 'config/config.php';

echo "<h1>🔧 CampusMart Database Schema Fix</h1>";
echo "<p>Fixing missing 'slug' column in categories table...</p>";

try {
    $db = getDBConnection();
    echo "<p>✅ Database connection successful</p>";
    
    // Check if categories table exists
    $stmt = $db->query("SHOW TABLES LIKE 'categories'");
    if (!$stmt->fetch()) {
        echo "<p>❌ Categories table doesn't exist. Creating it...</p>";
        
        // Create categories table with proper structure
        $createTableSQL = "
            CREATE TABLE categories (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(50) NOT NULL,
                slug VARCHAR(50) UNIQUE NOT NULL,
                description TEXT,
                icon VARCHAR(50),
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ";
        
        $db->exec($createTableSQL);
        echo "<p>✅ Categories table created</p>";
        
        // Insert default categories
        $categories = [
            ['School Supplies', 'school-supplies', 'Textbooks, stationery, and study materials', 'fas fa-book'],
            ['Electronics', 'electronics', 'Gadgets, laptops, and tech accessories', 'fas fa-laptop'],
            ['Clothing', 'clothing', 'Fashion items and accessories', 'fas fa-tshirt'],
            ['Furniture', 'furniture', 'Dorm furniture and home items', 'fas fa-couch'],
            ['Books', 'books', 'Academic and reference books', 'fas fa-book-open'],
            ['Sports & Recreation', 'sports', 'Sports equipment and recreational items', 'fas fa-football-ball'],
            ['Other', 'other', 'Miscellaneous items', 'fas fa-box']
        ];
        
        foreach ($categories as $cat) {
            $stmt = $db->prepare("INSERT INTO categories (name, slug, description, icon, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute($cat);
            echo "<p>✅ Added category: {$cat[0]} ({$cat[1]})</p>";
        }
        
    } else {
        echo "<p>✅ Categories table exists</p>";
        
        // Check table structure
        echo "<h3>Checking table structure...</h3>";
        $stmt = $db->query("DESCRIBE categories");
        $columns = $stmt->fetchAll();
        
        $hasSlug = false;
        $hasName = false;
        $hasIcon = false;
        
        echo "<p><strong>Current columns:</strong></p>";
        foreach ($columns as $col) {
            echo "<p>- {$col['Field']} ({$col['Type']}) {$col['Null']} {$col['Key']}</p>";
            if ($col['Field'] === 'slug') $hasSlug = true;
            if ($col['Field'] === 'name') $hasName = true;
            if ($col['Field'] === 'icon') $hasIcon = true;
        }
        
        // Add missing columns
        if (!$hasSlug) {
            echo "<p>❌ Missing 'slug' column - Adding it...</p>";
            $db->exec("ALTER TABLE categories ADD COLUMN slug VARCHAR(50) UNIQUE NOT NULL DEFAULT '' AFTER name");
            echo "<p>✅ slug column added</p>";
        } else {
            echo "<p>✅ slug column exists</p>";
        }
        
        if (!$hasIcon) {
            echo "<p>❌ Missing 'icon' column - Adding it...</p>";
            $db->exec("ALTER TABLE categories ADD COLUMN icon VARCHAR(50) AFTER description");
            echo "<p>✅ icon column added</p>";
        } else {
            echo "<p>✅ icon column exists</p>";
        }
        
        // Check if categories have slugs
        echo "<h3>Checking category data...</h3>";
        $stmt = $db->query("SELECT id, name, slug FROM categories");
        $categories = $stmt->fetchAll();
        
        if (empty($categories)) {
            echo "<p>❌ No categories found - Adding default categories...</p>";
            
            $defaultCategories = [
                ['School Supplies', 'school-supplies', 'Textbooks, stationery, and study materials', 'fas fa-book'],
                ['Electronics', 'electronics', 'Gadgets, laptops, and tech accessories', 'fas fa-laptop'],
                ['Clothing', 'clothing', 'Fashion items and accessories', 'fas fa-tshirt'],
                ['Furniture', 'furniture', 'Dorm furniture and home items', 'fas fa-couch'],
                ['Books', 'books', 'Academic and reference books', 'fas fa-book-open'],
                ['Sports & Recreation', 'sports', 'Sports equipment and recreational items', 'fas fa-football-ball'],
                ['Other', 'other', 'Miscellaneous items', 'fas fa-box']
            ];
            
            foreach ($defaultCategories as $cat) {
                $stmt = $db->prepare("INSERT INTO categories (name, slug, description, icon, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute($cat);
                echo "<p>✅ Added category: {$cat[0]} ({$cat[1]})</p>";
            }
            
        } else {
            echo "<p>✅ Found " . count($categories) . " categories</p>";
            
            // Check for missing slugs and fix them
            $needsSlugUpdate = false;
            foreach ($categories as $cat) {
                if (empty($cat['slug'])) {
                    $needsSlugUpdate = true;
                    break;
                }
            }
            
            if ($needsSlugUpdate) {
                echo "<p>🔧 Updating missing slugs...</p>";
                
                $slugMap = [
                    'School Supplies' => 'school-supplies',
                    'Electronics' => 'electronics',
                    'Clothing' => 'clothing',
                    'Furniture' => 'furniture',
                    'Books' => 'books',
                    'Sports & Recreation' => 'sports',
                    'Other' => 'other'
                ];
                
                foreach ($categories as $cat) {
                    if (empty($cat['slug'])) {
                        $slug = $slugMap[$cat['name']] ?? strtolower(str_replace([' ', '&'], ['-', ''], $cat['name']));
                        
                        $updateStmt = $db->prepare("UPDATE categories SET slug = ? WHERE id = ?");
                        $updateStmt->execute([$slug, $cat['id']]);
                        echo "<p>✅ Updated '{$cat['name']}' with slug '{$slug}'</p>";
                    }
                }
            }
            
            // Display current categories
            echo "<h3>Current categories:</h3>";
            $stmt = $db->query("SELECT id, name, slug, icon FROM categories ORDER BY name");
            $allCategories = $stmt->fetchAll();
            
            foreach ($allCategories as $cat) {
                echo "<p>- <strong>{$cat['name']}</strong> (slug: '{$cat['slug']}', icon: {$cat['icon']})</p>";
            }
        }
    }
    
    // Test the fix
    echo "<h3>🧪 Testing the fix...</h3>";
    
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
        echo "<p>❌ Test failed: " . $e->getMessage() . "</p>";
    }
    
    echo "<h2>🎉 Database Schema Fix Complete!</h2>";
    echo "<p>The categories table now has the proper structure with slug column.</p>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ul>";
    echo "<li>Test your marketplace and listing creation functionality</li>";
    echo "<li>The API endpoints should now work properly</li>";
    echo "<li>If you still see errors, check the browser console for more details</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<p>Please check your database connection and try again.</p>";
    
    // Provide specific guidance based on error type
    if (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "<p><strong>Solution:</strong> Make sure MySQL/MariaDB is running in XAMPP.</p>";
    } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "<p><strong>Solution:</strong> Check your database credentials in config/database.php</p>";
    } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "<p><strong>Solution:</strong> Create the 'campusmart' database first or run setup.php</p>";
    }
}
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
h1 { color: #2c3e50; }
h2 { color: #27ae60; }
h3 { color: #3498db; }
p { margin: 10px 0; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
</style>
