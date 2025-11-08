<?php
/**
 * Debug Status Column Issues
 */

require_once 'config/config.php';

echo "<h1>🔍 Debugging Status Column</h1>";

try {
    $db = getDBConnection();
    echo "<p>✅ Database connection successful</p>";
    
    // Check the exact structure of the status column
    echo "<h3>Listings Table Structure:</h3>";
    $stmt = $db->query("DESCRIBE listings");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $col) {
        if ($col['Field'] === 'status') {
            echo "<p><strong>Status Column Details:</strong></p>";
            echo "<p>- Field: {$col['Field']}</p>";
            echo "<p>- Type: {$col['Type']}</p>";
            echo "<p>- Null: {$col['Null']}</p>";
            echo "<p>- Key: {$col['Key']}</p>";
            echo "<p>- Default: {$col['Default']}</p>";
            echo "<p>- Extra: {$col['Extra']}</p>";
        }
    }
    
    // Check raw status values
    echo "<h3>Raw Status Values:</h3>";
    $stmt = $db->query("SELECT id, title, status, LENGTH(status) as status_length FROM listings ORDER BY id");
    $listings = $stmt->fetchAll();
    
    foreach ($listings as $listing) {
        $statusDisplay = $listing['status'] === '' ? '(empty string)' : 
                        ($listing['status'] === null ? '(NULL)' : "'{$listing['status']}'");
        echo "<p>ID {$listing['id']}: {$listing['title']} - Status: {$statusDisplay} (Length: {$listing['status_length']})</p>";
    }
    
    // Try to manually set status to 'active'
    echo "<h3>Manually Setting Status to 'active':</h3>";
    
    foreach ($listings as $listing) {
        $stmt = $db->prepare("UPDATE listings SET status = ? WHERE id = ?");
        $result = $stmt->execute(['active', $listing['id']]);
        
        if ($result) {
            echo "<p>✅ Updated listing ID {$listing['id']} to 'active'</p>";
        } else {
            echo "<p>❌ Failed to update listing ID {$listing['id']}</p>";
        }
    }
    
    // Verify the updates
    echo "<h3>Verification - Updated Status Values:</h3>";
    $stmt = $db->query("SELECT id, title, status FROM listings ORDER BY id");
    $updatedListings = $stmt->fetchAll();
    
    foreach ($updatedListings as $listing) {
        $color = $listing['status'] === 'active' ? 'green' : 'red';
        echo "<p style='color: {$color};'>ID {$listing['id']}: {$listing['title']} - Status: '{$listing['status']}'</p>";
    }
    
    // Count active listings
    $stmt = $db->query("SELECT COUNT(*) as count FROM listings WHERE status = 'active'");
    $result = $stmt->fetch();
    echo "<h3 style='color: green;'>Active Listings Count: {$result['count']}</h3>";
    
    if ($result['count'] > 0) {
        echo "<p style='color: green;'><strong>🎉 Success! Your listings should now appear in the marketplace!</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ Error:</strong> " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
h1 { color: #2c3e50; }
h3 { color: #3498db; }
p { margin: 8px 0; }
</style>
