<?php
/**
 * Fix Status ENUM Values
 * Updates the status column to include 'active' or changes logic to use 'available'
 */

require_once 'config/config.php';

echo "<h1>🔧 Fixing Status ENUM Values</h1>";

try {
    $db = getDBConnection();
    echo "<p>✅ Database connection successful</p>";
    
    echo "<h3>Current Status ENUM:</h3>";
    $stmt = $db->query("SHOW COLUMNS FROM listings LIKE 'status'");
    $statusColumn = $stmt->fetch();
    echo "<p>Current Type: {$statusColumn['Type']}</p>";
    
    // Option 1: Modify the ENUM to include 'active'
    echo "<h3>Updating ENUM to include 'active':</h3>";
    
    $alterSQL = "ALTER TABLE listings MODIFY COLUMN status ENUM('active', 'available', 'sold', 'reserved', 'inactive') DEFAULT 'active'";
    $db->exec($alterSQL);
    echo "<p>✅ Updated status ENUM to include 'active'</p>";
    
    // Now set all listings to 'active'
    echo "<h3>Setting all listings to 'active' status:</h3>";
    $stmt = $db->prepare("UPDATE listings SET status = 'active'");
    $result = $stmt->execute();
    $affected = $stmt->rowCount();
    echo "<p>✅ Updated {$affected} listings to 'active' status</p>";
    
    // Verify the changes
    echo "<h3>Verification:</h3>";
    $stmt = $db->query("SHOW COLUMNS FROM listings LIKE 'status'");
    $newStatusColumn = $stmt->fetch();
    echo "<p>New Type: {$newStatusColumn['Type']}</p>";
    
    // Check listing statuses
    $stmt = $db->query("SELECT id, title, status FROM listings ORDER BY id");
    $listings = $stmt->fetchAll();
    
    foreach ($listings as $listing) {
        $color = $listing['status'] === 'active' ? 'green' : 'red';
        echo "<p style='color: {$color};'>ID {$listing['id']}: {$listing['title']} - Status: '{$listing['status']}'</p>";
    }
    
    // Count active listings
    $stmt = $db->query("SELECT COUNT(*) as count FROM listings WHERE status = 'active'");
    $result = $stmt->fetch();
    echo "<h2 style='color: green;'>🎉 Active Listings Count: {$result['count']}</h2>";
    
    if ($result['count'] > 0) {
        echo "<p style='color: green; font-size: 18px;'><strong>SUCCESS! Your {$result['count']} listings should now appear in the marketplace!</strong></p>";
        
        echo "<h3>Next Steps:</h3>";
        echo "<ul>";
        echo "<li>Visit <a href='marketplace.php' target='_blank'>marketplace.php</a></li>";
        echo "<li>Your listings should now be visible</li>";
        echo "<li>Try filtering by different categories</li>";
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ Error:</strong> " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
h1 { color: #2c3e50; }
h2 { color: #27ae60; }
h3 { color: #3498db; }
p { margin: 8px 0; }
a { color: #3498db; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
