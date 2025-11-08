<?php
/**
 * Fix Listings Table Structure
 * Adds missing columns to the listings table
 */

require_once 'config/config.php';

echo "Fixing listings table structure...\n";

try {
    $db = getDBConnection();
    echo "✅ Database connection successful\n";
    
    // Check if listings table exists
    $stmt = $db->query("SHOW TABLES LIKE 'listings'");
    if (!$stmt->fetch()) {
        echo "❌ Listings table doesn't exist. Creating it...\n";
        
        $createTableSQL = "
            CREATE TABLE listings (
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
        
        $db->exec($createTableSQL);
        echo "✅ Listings table created\n";
        
    } else {
        echo "Listings table exists, checking structure...\n";
        
        // Get current table structure
        $stmt = $db->query("DESCRIBE listings");
        $columns = $stmt->fetchAll();
        
        $existingColumns = [];
        foreach ($columns as $col) {
            $existingColumns[] = $col['Field'];
        }
        
        echo "Current columns: " . implode(', ', $existingColumns) . "\n";
        
        // Define required columns and their definitions
        $requiredColumns = [
            'type' => "ENUM('sale', 'rent') NOT NULL DEFAULT 'sale'",
            'condition_item' => "ENUM('New', 'Like New', 'Good', 'Fair', 'Poor') NOT NULL DEFAULT 'Good'",
            'location' => "VARCHAR(100)",
            'rental_period' => "VARCHAR(50)",
            'images' => "JSON",
            'status' => "ENUM('active', 'sold', 'rented', 'inactive') DEFAULT 'active'",
            'views' => "INT DEFAULT 0",
            'featured' => "BOOLEAN DEFAULT FALSE",
            'updated_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
        ];
        
        // Add missing columns
        foreach ($requiredColumns as $columnName => $definition) {
            if (!in_array($columnName, $existingColumns)) {
                echo "Adding missing column: {$columnName}\n";
                
                // Determine where to add the column
                $afterColumn = '';
                switch ($columnName) {
                    case 'type':
                        $afterColumn = 'AFTER price';
                        break;
                    case 'condition_item':
                        $afterColumn = 'AFTER type';
                        break;
                    case 'location':
                        $afterColumn = 'AFTER condition_item';
                        break;
                    case 'rental_period':
                        $afterColumn = 'AFTER location';
                        break;
                    case 'images':
                        $afterColumn = 'AFTER rental_period';
                        break;
                    case 'status':
                        $afterColumn = 'AFTER images';
                        break;
                    case 'views':
                        $afterColumn = 'AFTER status';
                        break;
                    case 'featured':
                        $afterColumn = 'AFTER views';
                        break;
                    case 'updated_at':
                        $afterColumn = 'AFTER created_at';
                        break;
                }
                
                $alterSQL = "ALTER TABLE listings ADD COLUMN {$columnName} {$definition} {$afterColumn}";
                $db->exec($alterSQL);
                echo "✅ Added column: {$columnName}\n";
            } else {
                echo "✅ Column exists: {$columnName}\n";
            }
        }
    }
    
    // Test the listings table
    echo "Testing listings table...\n";
    $stmt = $db->query("SELECT COUNT(*) as count FROM listings");
    $result = $stmt->fetch();
    echo "✅ Listings table accessible - found {$result['count']} listings\n";
    
    // Test the API query that was failing
    echo "Testing API query...\n";
    $sql = "
        SELECT 
            l.id,
            l.title,
            l.description,
            l.price,
            l.type,
            l.condition_item,
            l.location,
            l.rental_period,
            l.images,
            l.views,
            l.created_at,
            c.name as category_name,
            c.slug as category_slug
        FROM listings l
        LEFT JOIN categories c ON l.category_id = c.id
        WHERE l.status = 'active' AND c.slug = ?
        ORDER BY l.created_at DESC
        LIMIT 5
    ";
    
    $stmt = $db->prepare($sql);
    $stmt->execute(['books']);
    $listings = $stmt->fetchAll();
    
    echo "✅ API query executed successfully\n";
    echo "Found " . count($listings) . " listings for 'books' category\n";
    
    echo "\n🎉 Listings table fix completed successfully!\n";
    echo "Your API endpoints should now work properly.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "Solution: Make sure MySQL/MariaDB is running in XAMPP.\n";
    } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "Solution: Check your database credentials.\n";
    } elseif (strpos($e->getMessage(), 'Unknown column') !== false) {
        echo "Solution: Some columns are still missing. Check the error details above.\n";
    }
}
?>
