<?php
/**
 * Fix Listing Status Values
 * Updates all listings to have proper 'active' status
 */

require_once 'config/config.php';

echo "<h1>🔧 Fixing Listing Status Values</h1>";

try {
    $db = getDBConnection();
    echo "<p>✅ Database connection successful</p>";
    
    // Check current status values
    echo "<h3>Current Status Values:</h3>";
    $stmt = $db->query("SELECT status, COUNT(*) as count FROM listings GROUP BY status");
    $statusCounts = $stmt->fetchAll();
    
    foreach ($statusCounts as $status) {
        $statusValue = $status['status'] ?: '(empty)';
        echo "<p>- Status: '<strong>{$statusValue}</strong>' - Count: {$status['count']}</p>";
    }
    
    // Update all non-active listings to 'active' status
    echo "<h3>Updating Listing Statuses:</h3>";
    
    // Update empty status to 'active'
    $stmt = $db->prepare("UPDATE listings SET status = 'active' WHERE status = '' OR status IS NULL");
    $result1 = $stmt->execute();
    $affected1 = $stmt->rowCount();
    echo "<p>✅ Updated {$affected1} listings with empty status to 'active'</p>";
    
    // Update 'available' status to 'active'
    $stmt = $db->prepare("UPDATE listings SET status = 'active' WHERE status = 'available'");
    $result2 = $stmt->execute();
    $affected2 = $stmt->rowCount();
    echo "<p>✅ Updated {$affected2} listings with 'available' status to 'active'</p>";
    
    // Check updated status values
    echo "<h3>Updated Status Values:</h3>";
    $stmt = $db->query("SELECT status, COUNT(*) as count FROM listings GROUP BY status");
    $newStatusCounts = $stmt->fetchAll();
    
    foreach ($newStatusCounts as $status) {
        $statusValue = $status['status'] ?: '(empty)';
        $color = $status['status'] === 'active' ? 'green' : 'red';
        echo "<p style='color: {$color};'>- Status: '<strong>{$statusValue}</strong>' - Count: {$status['count']}</p>";
    }
    
    // Show active listings
    echo "<h3>Active Listings Now Available:</h3>";
    $stmt = $db->query("
        SELECT 
            l.id,
            l.title,
            l.price,
            l.status,
            c.name as category_name
        FROM listings l
        LEFT JOIN categories c ON l.category_id = c.id
        WHERE l.status = 'active'
        ORDER BY l.created_at DESC
    ");
    
    $activeListings = $stmt->fetchAll();
    
    if (count($activeListings) > 0) {
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
        echo "<tr style='background-color: #f8f9fa;'><th>ID</th><th>Title</th><th>Price</th><th>Category</th><th>Status</th></tr>";
        
        foreach ($activeListings as $listing) {
            echo "<tr>";
            echo "<td>{$listing['id']}</td>";
            echo "<td><strong>{$listing['title']}</strong></td>";
            echo "<td>₱" . number_format($listing['price'], 2) . "</td>";
            echo "<td>{$listing['category_name']}</td>";
            echo "<td style='color: green;'><strong>{$listing['status']}</strong></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h2 style='color: green;'>🎉 Success!</h2>";
        echo "<p><strong>" . count($activeListings) . " listings are now active and should appear in your marketplace!</strong></p>";
        
    } else {
        echo "<p style='color: red;'>❌ Still no active listings found.</p>";
    }
    
    echo "<h3>Next Steps:</h3>";
    echo "<ul>";
    echo "<li>Visit your <a href='marketplace.php'>marketplace.php</a> page</li>";
    echo "<li>Your listings should now be visible</li>";
    echo "<li>If they still don't show, check the browser console for JavaScript errors</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ Error:</strong> " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
h1 { color: #2c3e50; }
h2 { color: #27ae60; }
h3 { color: #3498db; }
table { margin: 10px 0; }
th { background-color: #f8f9fa; font-weight: bold; }
td, th { padding: 8px; text-align: left; border: 1px solid #ddd; }
a { color: #3498db; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
