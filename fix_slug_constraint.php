<?php
/**
 * Fix Slug Column Constraint Issues
 * Handles duplicate empty slug values and constraint violations
 */

require_once 'config/config.php';

echo "Fixing slug column constraint issues...\n";

try {
    $db = getDBConnection();
    echo "✅ Database connection successful\n";
    
    // First, check current table structure
    $stmt = $db->query("DESCRIBE categories");
    $columns = $stmt->fetchAll();
    
    $hasSlug = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'slug') {
            $hasSlug = true;
            break;
        }
    }
    
    if ($hasSlug) {
        echo "Slug column exists, checking for constraint issues...\n";
        
        // Check for empty or null slugs
        $stmt = $db->query("SELECT COUNT(*) as count FROM categories WHERE slug IS NULL OR slug = ''");
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            echo "Found {$result['count']} categories with empty slugs, fixing...\n";
            
            // Get categories with empty slugs
            $stmt = $db->query("SELECT id, name FROM categories WHERE slug IS NULL OR slug = ''");
            $categories = $stmt->fetchAll();
            
            foreach ($categories as $cat) {
                $slug = strtolower(str_replace([' ', '&', '/'], ['-', 'and', '-'], $cat['name']));
                $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
                $slug = preg_replace('/-+/', '-', $slug);
                $slug = trim($slug, '-');
                
                // Make sure slug is unique
                $counter = 1;
                $originalSlug = $slug;
                while (true) {
                    $checkStmt = $db->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
                    $checkStmt->execute([$slug, $cat['id']]);
                    if (!$checkStmt->fetch()) {
                        break;
                    }
                    $slug = $originalSlug . '-' . $counter;
                    $counter++;
                }
                
                $updateStmt = $db->prepare("UPDATE categories SET slug = ? WHERE id = ?");
                $updateStmt->execute([$slug, $cat['id']]);
                echo "✅ Updated '{$cat['name']}' with slug '{$slug}'\n";
            }
        }
        
    } else {
        echo "Adding slug column...\n";
        
        // First, ensure all existing categories have unique names for slug generation
        $stmt = $db->query("SELECT id, name FROM categories");
        $categories = $stmt->fetchAll();
        
        // Add slug column without UNIQUE constraint first
        $db->exec("ALTER TABLE categories ADD COLUMN slug VARCHAR(50) DEFAULT '' AFTER name");
        echo "✅ Slug column added (without unique constraint)\n";
        
        // Generate slugs for existing categories
        foreach ($categories as $cat) {
            $slug = strtolower(str_replace([' ', '&', '/'], ['-', 'and', '-'], $cat['name']));
            $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
            $slug = preg_replace('/-+/', '-', $slug);
            $slug = trim($slug, '-');
            
            // Make sure slug is unique
            $counter = 1;
            $originalSlug = $slug;
            while (true) {
                $checkStmt = $db->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
                $checkStmt->execute([$slug, $cat['id']]);
                if (!$checkStmt->fetch()) {
                    break;
                }
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            $updateStmt = $db->prepare("UPDATE categories SET slug = ? WHERE id = ?");
            $updateStmt->execute([$slug, $cat['id']]);
            echo "✅ Generated slug '{$slug}' for '{$cat['name']}'\n";
        }
        
        // Now add the UNIQUE constraint
        $db->exec("ALTER TABLE categories MODIFY COLUMN slug VARCHAR(50) UNIQUE NOT NULL");
        echo "✅ Added UNIQUE constraint to slug column\n";
    }
    
    // Check if we have the standard categories
    $stmt = $db->query("SELECT COUNT(*) as count FROM categories");
    $result = $stmt->fetch();
    
    if ($result['count'] < 5) {
        echo "Adding missing default categories...\n";
        
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
            // Check if category already exists
            $checkStmt = $db->prepare("SELECT id FROM categories WHERE slug = ? OR name = ?");
            $checkStmt->execute([$cat[1], $cat[0]]);
            
            if (!$checkStmt->fetch()) {
                $insertStmt = $db->prepare("INSERT INTO categories (name, slug, description, icon, created_at) VALUES (?, ?, ?, ?, NOW())");
                $insertStmt->execute($cat);
                echo "✅ Added category: {$cat[0]} ({$cat[1]})\n";
            }
        }
    }
    
    // Final test
    echo "Testing the fix...\n";
    $stmt = $db->prepare("SELECT id, name, slug FROM categories WHERE slug = ?");
    $stmt->execute(['books']);
    $category = $stmt->fetch();
    
    if ($category) {
        echo "✅ Test successful - found category: {$category['name']} (slug: {$category['slug']})\n";
    } else {
        echo "⚠️ 'books' category not found, checking all categories...\n";
        $stmt = $db->query("SELECT id, name, slug FROM categories LIMIT 5");
        $allCategories = $stmt->fetchAll();
        foreach ($allCategories as $cat) {
            echo "- {$cat['name']} (slug: {$cat['slug']})\n";
        }
    }
    
    echo "\n🎉 Slug column fix completed successfully!\n";
    echo "Run test_slug_fix.php to verify everything is working.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    
    // Provide specific solutions
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        echo "Solution: There are duplicate slug values. This script should handle that.\n";
    } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "Solution: Make sure MySQL/MariaDB is running in XAMPP.\n";
    } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "Solution: Check your database credentials.\n";
    }
}
?>
