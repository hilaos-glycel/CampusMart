<?php
/**
 * Direct Database Fix Execution
 * Runs the slug column fix directly without web interface
 */

require_once 'config/config.php';

echo "Starting database fix...\n";

try {
    $db = getDBConnection();
    echo "✅ Database connection successful\n";
    
    // Check if categories table exists
    $stmt = $db->query("SHOW TABLES LIKE 'categories'");
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "Creating categories table...\n";
        
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
        echo "✅ Categories table created\n";
        
    } else {
        echo "Categories table exists, checking structure...\n";
        
        // Check if slug column exists
        $stmt = $db->query("DESCRIBE categories");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array('slug', $columns)) {
            echo "Adding slug column...\n";
            $db->exec("ALTER TABLE categories ADD COLUMN slug VARCHAR(50) UNIQUE NOT NULL DEFAULT '' AFTER name");
            echo "✅ Slug column added\n";
        } else {
            echo "✅ Slug column already exists\n";
        }
        
        if (!in_array('icon', $columns)) {
            echo "Adding icon column...\n";
            $db->exec("ALTER TABLE categories ADD COLUMN icon VARCHAR(50) AFTER description");
            echo "✅ Icon column added\n";
        }
    }
    
    // Check if we have categories with slugs
    $stmt = $db->query("SELECT COUNT(*) as count FROM categories WHERE slug IS NOT NULL AND slug != ''");
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        echo "Adding default categories...\n";
        
        // Clear existing categories first
        $db->exec("DELETE FROM categories");
        
        $categories = [
            ['School Supplies', 'school-supplies', 'Textbooks, stationery, and study materials', 'fas fa-book'],
            ['Electronics', 'electronics', 'Gadgets, laptops, and tech accessories', 'fas fa-laptop'],
            ['Clothing', 'clothing', 'Fashion items and accessories', 'fas fa-tshirt'],
            ['Furniture', 'furniture', 'Dorm furniture and home items', 'fas fa-couch'],
            ['Books', 'books', 'Academic and reference books', 'fas fa-book-open'],
            ['Sports & Recreation', 'sports', 'Sports equipment and recreational items', 'fas fa-football-ball'],
            ['Other', 'other', 'Miscellaneous items', 'fas fa-box']
        ];
        
        $stmt = $db->prepare("INSERT INTO categories (name, slug, description, icon, created_at) VALUES (?, ?, ?, ?, NOW())");
        
        foreach ($categories as $cat) {
            $stmt->execute($cat);
            echo "✅ Added category: {$cat[0]} ({$cat[1]})\n";
        }
    } else {
        echo "✅ Categories with slugs already exist\n";
    }
    
    // Test the fix
    echo "Testing the fix...\n";
    $stmt = $db->prepare("SELECT id, name, slug FROM categories WHERE slug = ?");
    $stmt->execute(['books']);
    $category = $stmt->fetch();
    
    if ($category) {
        echo "✅ Test successful - found category: {$category['name']} (slug: {$category['slug']})\n";
    } else {
        echo "❌ Test failed - no category found with slug 'books'\n";
    }
    
    echo "\n🎉 Database fix completed successfully!\n";
    echo "You can now test your marketplace functionality.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "Solution: Make sure MySQL/MariaDB is running in XAMPP.\n";
    } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "Solution: Check your database credentials in config/database.php\n";
    } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "Solution: Create the 'campusmart' database first.\n";
    }
}
?>
