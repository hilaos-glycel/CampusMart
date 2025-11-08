<?php
/**
 * Test Script for Slug Column Fix
 * Verifies that the database schema fix resolved the slug column issue
 */

require_once 'config/config.php';

echo "<h1>🧪 Testing Slug Column Fix</h1>";

try {
    $db = getDBConnection();
    echo "<p>✅ Database connection successful</p>";
    
    // Test 1: Check if slug column exists
    echo "<h3>Test 1: Checking slug column existence</h3>";
    $stmt = $db->query("DESCRIBE categories");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('slug', $columns)) {
        echo "<p>✅ slug column exists in categories table</p>";
    } else {
        echo "<p>❌ slug column is missing - run fix_slug_column.php first</p>";
        exit();
    }
    
    // Test 2: Check if categories have slugs
    echo "<h3>Test 2: Checking category data</h3>";
    $stmt = $db->query("SELECT COUNT(*) as total FROM categories WHERE slug IS NOT NULL AND slug != ''");
    $result = $stmt->fetch();
    
    if ($result['total'] > 0) {
        echo "<p>✅ Found {$result['total']} categories with valid slugs</p>";
    } else {
        echo "<p>❌ No categories with slugs found - run fix_slug_column.php first</p>";
        exit();
    }
    
    // Test 3: Test the API query that was failing
    echo "<h3>Test 3: Testing API query with slug filter</h3>";
    $testSlug = 'books';
    
    // This is the exact query from get_listings.php that was failing
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
            c.slug as category_slug,
            CONCAT(u.first_name, ' ', u.last_name) as seller_name,
            u.student_id as seller_student_id,
            u.rating as seller_rating
        FROM listings l
        LEFT JOIN categories c ON l.category_id = c.id
        LEFT JOIN users u ON l.user_id = u.id
        WHERE l.status = 'active' AND c.slug = ?
        ORDER BY l.created_at DESC
        LIMIT 10
    ";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$testSlug]);
    $listings = $stmt->fetchAll();
    
    echo "<p>✅ API query executed successfully</p>";
    echo "<p>Found " . count($listings) . " listings for category '{$testSlug}'</p>";
    
    // Test 4: Test create listing category lookup
    echo "<h3>Test 4: Testing category lookup for create listing</h3>";
    $stmt = $db->prepare("SELECT id FROM categories WHERE slug = ?");
    $stmt->execute([$testSlug]);
    $category = $stmt->fetch();
    
    if ($category) {
        echo "<p>✅ Category lookup by slug successful - ID: {$category['id']}</p>";
    } else {
        echo "<p>⚠️ No category found with slug '{$testSlug}'</p>";
    }
    
    // Test 5: Display all available categories
    echo "<h3>Test 5: Available categories</h3>";
    $stmt = $db->query("SELECT id, name, slug FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Name</th><th>Slug</th></tr>";
    foreach ($categories as $cat) {
        echo "<tr><td>{$cat['id']}</td><td>{$cat['name']}</td><td>{$cat['slug']}</td></tr>";
    }
    echo "</table>";
    
    echo "<h2>🎉 All Tests Passed!</h2>";
    echo "<p>The database schema fix was successful. Your API endpoints should now work properly.</p>";
    
    echo "<h3>What to test next:</h3>";
    echo "<ul>";
    echo "<li>Visit <a href='marketplace.php'>marketplace.php</a> and try filtering by category</li>";
    echo "<li>Try creating a new listing via <a href='post-item.php'>post-item.php</a></li>";
    echo "<li>Check the browser console for any remaining JavaScript errors</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Test Failed:</strong> " . $e->getMessage() . "</p>";
    
    if (strpos($e->getMessage(), 'Unknown column') !== false && strpos($e->getMessage(), 'slug') !== false) {
        echo "<p><strong>Solution:</strong> Run <a href='fix_slug_column.php'>fix_slug_column.php</a> to add the missing slug column.</p>";
    } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "<p><strong>Solution:</strong> Make sure MySQL/MariaDB is running in XAMPP.</p>";
    } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "<p><strong>Solution:</strong> Check your database credentials in config/database.php</p>";
    }
}
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
h1 { color: #2c3e50; }
h2 { color: #27ae60; }
h3 { color: #3498db; }
p { margin: 10px 0; }
table { margin: 10px 0; border-collapse: collapse; }
th { background-color: #f8f9fa; font-weight: bold; }
td, th { padding: 8px; text-align: left; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
a { color: #3498db; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
