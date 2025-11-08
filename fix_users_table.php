<?php
/**
 * Fix Users Table Structure
 * Adds missing columns to the users table
 */

require_once 'config/config.php';

echo "Fixing users table structure...\n";

try {
    $db = getDBConnection();
    echo "✅ Database connection successful\n";
    
    // Check users table structure
    $stmt = $db->query("DESCRIBE users");
    $columns = $stmt->fetchAll();
    
    $existingColumns = [];
    foreach ($columns as $col) {
        $existingColumns[] = $col['Field'];
    }
    
    echo "Current columns: " . implode(', ', $existingColumns) . "\n";
    
    // Define required columns and their definitions
    $requiredColumns = [
        'bio' => "TEXT",
        'profile_image' => "VARCHAR(255)",
        'phone' => "VARCHAR(20)",
        'total_earnings' => "DECIMAL(10,2) DEFAULT 0.00",
        'rating' => "DECIMAL(3,2) DEFAULT 0.00",
        'total_reviews' => "INT DEFAULT 0"
    ];
    
    // Add missing columns
    foreach ($requiredColumns as $columnName => $definition) {
        if (!in_array($columnName, $existingColumns)) {
            echo "Adding missing column: {$columnName}\n";
            
            // Determine where to add the column
            $afterColumn = '';
            switch ($columnName) {
                case 'bio':
                    $afterColumn = 'AFTER year_level';
                    break;
                case 'profile_image':
                    $afterColumn = 'AFTER bio';
                    break;
                case 'phone':
                    $afterColumn = 'AFTER profile_image';
                    break;
                case 'total_earnings':
                    $afterColumn = 'AFTER email_verified';
                    break;
                case 'rating':
                    $afterColumn = 'AFTER total_earnings';
                    break;
                case 'total_reviews':
                    $afterColumn = 'AFTER rating';
                    break;
            }
            
            $alterSQL = "ALTER TABLE users ADD COLUMN {$columnName} {$definition} {$afterColumn}";
            $db->exec($alterSQL);
            echo "✅ Added column: {$columnName}\n";
        } else {
            echo "✅ Column exists: {$columnName}\n";
        }
    }
    
    // Test the users table
    echo "Testing users table...\n";
    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "✅ Users table accessible - found {$result['count']} users\n";
    
    // Test a query with the rating column
    echo "Testing rating column...\n";
    $stmt = $db->query("SELECT id, username, rating FROM users LIMIT 3");
    $users = $stmt->fetchAll();
    
    foreach ($users as $user) {
        echo "- User: {$user['username']}, Rating: " . ($user['rating'] ?: 'No rating') . "\n";
    }
    
    echo "\n🎉 Users table fix completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "Solution: Make sure MySQL/MariaDB is running in XAMPP.\n";
    } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "Solution: Check your database credentials.\n";
    }
}
?>
