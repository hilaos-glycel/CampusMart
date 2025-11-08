<?php
require_once 'config/config.php';

// Check database connection
try {
    $db = getDBConnection();
    echo "<h2>✅ Database Connection: Success</h2>";
} catch (Exception $e) {
    echo "<h2>❌ Database Connection: Failed</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    exit;
}

// Check if categories table exists and has data
try {
    $stmt = $db->prepare("SELECT * FROM categories LIMIT 5");
    $stmt->execute();
    $categories = $stmt->fetchAll();
    
    echo "<h2>✅ Categories Table: Found</h2>";
    echo "<p>Categories count: " . count($categories) . "</p>";
    
    if (empty($categories)) {
        echo "<h3>⚠️ No categories found. Adding default categories...</h3>";
        
        $defaultCategories = [
            ['name' => 'Books', 'slug' => 'books', 'description' => 'Textbooks and academic materials'],
            ['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Laptops, phones, gadgets'],
            ['name' => 'Clothing', 'slug' => 'clothing', 'description' => 'Clothes and fashion items'],
            ['name' => 'Accessories', 'slug' => 'accessories', 'description' => 'Bags, jewelry, and accessories'],
            ['name' => 'Sports', 'slug' => 'sports', 'description' => 'Sports equipment and gear'],
            ['name' => 'Other', 'slug' => 'other', 'description' => 'Other items']
        ];
        
        foreach ($defaultCategories as $cat) {
            $stmt = $db->prepare("INSERT INTO categories (name, slug, description, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$cat['name'], $cat['slug'], $cat['description']]);
        }
        
        echo "<p>✅ Default categories added!</p>";
    } else {
        echo "<h3>Categories found:</h3>";
        foreach ($categories as $cat) {
            echo "<p>- {$cat['name']} (slug: {$cat['slug']})</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Categories Table: Error</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

// Check if listings table exists
try {
    $stmt = $db->prepare("DESCRIBE listings");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    echo "<h2>✅ Listings Table: Found</h2>";
    echo "<p>Columns count: " . count($columns) . "</p>";
    
    echo "<h3>Table structure:</h3>";
    foreach ($columns as $col) {
        echo "<p>- {$col['Field']} ({$col['Type']})</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Listings Table: Error</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

// Check uploads directory
$uploadsDir = UPLOAD_PATH . 'listings/';
echo "<h2>Upload Directory Check</h2>";
echo "<p>Upload path: " . $uploadsDir . "</p>";

if (is_dir($uploadsDir)) {
    echo "<p>✅ Directory exists</p>";
    if (is_writable($uploadsDir)) {
        echo "<p>✅ Directory is writable</p>";
    } else {
        echo "<p>❌ Directory is not writable</p>";
    }
} else {
    echo "<p>❌ Directory does not exist</p>";
    echo "<p>Attempting to create directory...</p>";
    if (mkdir($uploadsDir, 0755, true)) {
        echo "<p>✅ Directory created successfully</p>";
    } else {
        echo "<p>❌ Failed to create directory</p>";
    }
}

// Test form submission
echo "<h2>Test Form Submission</h2>";
echo '<form method="POST" action="api/create_listing.php" enctype="multipart/form-data">';
echo '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
echo '<input type="text" name="title" value="Test Item" required>';
echo '<textarea name="description" required>Test description</textarea>';
echo '<select name="category" required>';
echo '<option value="books">Books</option>';
echo '</select>';
echo '<select name="condition" required>';
echo '<option value="good">Good</option>';
echo '</select>';
echo '<select name="type" required>';
echo '<option value="sell">For Sale</option>';
echo '</select>';
echo '<input type="number" name="price" value="100" step="0.01" required>';
echo '<input type="text" name="location" value="JH Campus">';
echo '<button type="submit">Test Submit</button>';
echo '</form>';

echo "<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2 { color: #333; border-bottom: 2px solid #28a745; padding-bottom: 5px; }
h3 { color: #666; }
p { margin: 5px 0; }
form { background: #f8f9fa; padding: 20px; border-radius: 5px; margin-top: 20px; }
form input, form textarea, form select, form button { 
    display: block; margin: 10px 0; padding: 8px; width: 300px; 
}
form button { background: #28a745; color: white; border: none; cursor: pointer; }
</style>";
?>
