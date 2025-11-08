<?php
/**
 * Test Product Details Page
 * Check if the product details page works correctly
 */

require_once 'config/config.php';

echo "<h1>🧪 Testing Product Details Page</h1>";

try {
    $db = getDBConnection();
    
    // Get a sample product ID
    $stmt = $db->query("SELECT id, title FROM listings WHERE status = 'active' LIMIT 1");
    $product = $stmt->fetch();
    
    if ($product) {
        echo "<p>✅ Found sample product: <strong>{$product['title']}</strong> (ID: {$product['id']})</p>";
        echo "<p>🔗 <a href='product-details.php?id={$product['id']}' target='_blank'>Test Product Details Page</a></p>";
        
        // Test the URL
        $testUrl = "http://localhost/CampusMart/product-details.php?id={$product['id']}";
        echo "<p>📝 Full URL: <a href='{$testUrl}' target='_blank'>{$testUrl}</a></p>";
        
        echo "<h3>All Available Products for Testing:</h3>";
        $stmt = $db->query("SELECT id, title, price, condition_item FROM listings WHERE status = 'active' ORDER BY id");
        $allProducts = $stmt->fetchAll();
        
        echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f8f9fa;'><th>ID</th><th>Title</th><th>Price</th><th>Condition</th><th>Test Link</th></tr>";
        
        foreach ($allProducts as $prod) {
            echo "<tr>";
            echo "<td>{$prod['id']}</td>";
            echo "<td>{$prod['title']}</td>";
            echo "<td>₱" . number_format($prod['price'], 2) . "</td>";
            echo "<td>{$prod['condition_item']}</td>";
            echo "<td><a href='product-details.php?id={$prod['id']}' target='_blank'>View Details</a></td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p style='color: red;'>❌ No active products found for testing</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

?>

<style>
body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
h1 { color: #2c3e50; }
h3 { color: #3498db; }
table { width: 100%; margin: 15px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f8f9fa; font-weight: bold; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
