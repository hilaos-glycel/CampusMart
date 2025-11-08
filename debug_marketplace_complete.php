<?php
/**
 * Complete Marketplace Debug
 * Check all aspects of why listings aren't displaying
 */

require_once 'config/config.php';

echo "<h1>🔍 Complete Marketplace Debug</h1>";

// 1. Check session and authentication
echo "<h3>1. Session & Authentication Check</h3>";
session_start();
if (isset($_SESSION['user_id'])) {
    echo "<p style='color: green;'>✅ User logged in: ID {$_SESSION['user_id']}</p>";
    if (isset($_SESSION['username'])) {
        echo "<p>Username: {$_SESSION['username']}</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ No user logged in (this might be OK for viewing listings)</p>";
}

// 2. Database connection
echo "<h3>2. Database Connection</h3>";
try {
    $db = getDBConnection();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    exit();
}

// 3. Check listings table
echo "<h3>3. Listings Table Check</h3>";
$stmt = $db->query("SELECT COUNT(*) as total FROM listings");
$total = $stmt->fetch()['total'];
echo "<p>Total listings in database: <strong>{$total}</strong></p>";

$stmt = $db->query("SELECT COUNT(*) as active FROM listings WHERE status = 'active'");
$active = $stmt->fetch()['active'];
echo "<p>Active listings: <strong>{$active}</strong></p>";

if ($active == 0) {
    echo "<p style='color: red;'>❌ No active listings! This is why nothing shows up.</p>";
    
    // Show current statuses
    $stmt = $db->query("SELECT status, COUNT(*) as count FROM listings GROUP BY status");
    $statuses = $stmt->fetchAll();
    echo "<p><strong>Current status breakdown:</strong></p>";
    foreach ($statuses as $status) {
        echo "<p>- Status '{$status['status']}': {$status['count']} listings</p>";
    }
} else {
    echo "<p style='color: green;'>✅ Found {$active} active listings</p>";
}

// 4. Test the exact API query
echo "<h3>4. API Query Test</h3>";
try {
    // This is the exact query from get_listings.php
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
        WHERE l.status = 'active'
        ORDER BY l.created_at DESC
        LIMIT 12
    ";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $listings = $stmt->fetchAll();
    
    echo "<p>API query returned: <strong>" . count($listings) . "</strong> listings</p>";
    
    if (count($listings) > 0) {
        echo "<p style='color: green;'>✅ API query working correctly</p>";
        
        echo "<h4>Sample Listings:</h4>";
        echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f8f9fa;'><th>ID</th><th>Title</th><th>Price</th><th>Condition</th><th>Category</th><th>Seller</th></tr>";
        
        foreach (array_slice($listings, 0, 3) as $listing) {
            echo "<tr>";
            echo "<td>{$listing['id']}</td>";
            echo "<td>{$listing['title']}</td>";
            echo "<td>₱" . number_format($listing['price'], 2) . "</td>";
            echo "<td>{$listing['condition_item']}</td>";
            echo "<td>{$listing['category_name']}</td>";
            echo "<td>{$listing['seller_name']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    } else {
        echo "<p style='color: red;'>❌ API query returned no results</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ API query failed: " . $e->getMessage() . "</p>";
}

// 5. Test API endpoint directly
echo "<h3>5. API Endpoint Test</h3>";
echo "<p>Testing: <code>api/get_listings.php</code></p>";

// Capture API output
ob_start();
$_GET = []; // Clear GET params
include 'api/get_listings.php';
$apiOutput = ob_get_clean();

// Check if it's valid JSON
$apiData = json_decode($apiOutput, true);
if ($apiData) {
    echo "<p style='color: green;'>✅ API endpoint returns valid JSON</p>";
    echo "<p>Success: " . ($apiData['success'] ? 'true' : 'false') . "</p>";
    if (isset($apiData['listings'])) {
        echo "<p>Listings count: " . count($apiData['listings']) . "</p>";
    }
    if (isset($apiData['message'])) {
        echo "<p>Message: {$apiData['message']}</p>";
    }
} else {
    echo "<p style='color: red;'>❌ API endpoint returns invalid JSON</p>";
    echo "<p>JSON Error: " . json_last_error_msg() . "</p>";
    echo "<details><summary>Raw API Output</summary><pre>" . htmlspecialchars($apiOutput) . "</pre></details>";
}

// 6. Check for common issues
echo "<h3>6. Common Issues Check</h3>";

// Check if images column has proper JSON
$stmt = $db->query("SELECT id, title, images FROM listings WHERE images IS NOT NULL AND images != '' LIMIT 3");
$imageListings = $stmt->fetchAll();

foreach ($imageListings as $listing) {
    $images = json_decode($listing['images'], true);
    if ($images === null && $listing['images'] !== 'null') {
        echo "<p style='color: orange;'>⚠️ Listing ID {$listing['id']} has invalid JSON in images field</p>";
    } else {
        echo "<p style='color: green;'>✅ Listing ID {$listing['id']} has valid images JSON</p>";
    }
}

// Check category relationships
$stmt = $db->query("SELECT COUNT(*) as count FROM listings l LEFT JOIN categories c ON l.category_id = c.id WHERE c.id IS NULL");
$orphanedListings = $stmt->fetch()['count'];
if ($orphanedListings > 0) {
    echo "<p style='color: red;'>❌ Found {$orphanedListings} listings with invalid category references</p>";
} else {
    echo "<p style='color: green;'>✅ All listings have valid category references</p>";
}

// Check user relationships
$stmt = $db->query("SELECT COUNT(*) as count FROM listings l LEFT JOIN users u ON l.user_id = u.id WHERE u.id IS NULL");
$orphanedUsers = $stmt->fetch()['count'];
if ($orphanedUsers > 0) {
    echo "<p style='color: red;'>❌ Found {$orphanedUsers} listings with invalid user references</p>";
} else {
    echo "<p style='color: green;'>✅ All listings have valid user references</p>";
}

echo "<h3>7. Recommendations</h3>";
if ($active == 0) {
    echo "<p style='color: red;'><strong>MAIN ISSUE:</strong> No active listings in database</p>";
    echo "<p><strong>Solution:</strong> Run the status fix script again or manually set listing statuses to 'active'</p>";
} else if ($apiData && $apiData['success'] && count($apiData['listings']) > 0) {
    echo "<p style='color: green;'><strong>GOOD NEWS:</strong> API is working and returning listings</p>";
    echo "<p><strong>Issue might be:</strong> Frontend JavaScript or browser console errors</p>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ul>";
    echo "<li>Open marketplace.php in browser</li>";
    echo "<li>Open browser Developer Tools (F12)</li>";
    echo "<li>Check Console tab for JavaScript errors</li>";
    echo "<li>Check Network tab to see if API calls are being made</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'><strong>ISSUE:</strong> API is not returning listings properly</p>";
    echo "<p><strong>Check:</strong> API endpoint errors or database query issues</p>";
}

?>

<style>
body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
h1 { color: #2c3e50; }
h3 { color: #3498db; border-bottom: 2px solid #3498db; padding-bottom: 5px; }
h4 { color: #e67e22; }
table { width: 100%; margin: 10px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f8f9fa; font-weight: bold; }
code { background: #f8f8f8; padding: 2px 4px; border-radius: 3px; }
details { margin: 10px 0; }
pre { background: #f8f8f8; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>
