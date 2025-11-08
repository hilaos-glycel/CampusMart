<?php
/**
 * Test API Response
 * Check what the get_listings.php API is actually returning
 */

require_once 'config/config.php';

echo "<h1>🧪 Testing API Response</h1>";

// Test the API endpoint directly
echo "<h3>Testing api/get_listings.php</h3>";

// Simulate the API call
$_GET = []; // Clear any existing GET parameters

// Include the API file and capture its output
ob_start();
include 'api/get_listings.php';
$apiResponse = ob_get_clean();

echo "<h4>Raw API Response:</h4>";
echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto;'>";
echo htmlspecialchars($apiResponse);
echo "</pre>";

// Try to decode the JSON
$data = json_decode($apiResponse, true);

if ($data) {
    echo "<h4>Parsed JSON Response:</h4>";
    echo "<p><strong>Success:</strong> " . ($data['success'] ? 'true' : 'false') . "</p>";
    
    if (isset($data['listings'])) {
        echo "<p><strong>Number of listings:</strong> " . count($data['listings']) . "</p>";
        
        if (count($data['listings']) > 0) {
            echo "<h4>First Listing Details:</h4>";
            $firstListing = $data['listings'][0];
            
            echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>";
            foreach ($firstListing as $key => $value) {
                echo "<tr><td><strong>{$key}</strong></td><td>" . htmlspecialchars($value) . "</td></tr>";
            }
            echo "</table>";
            
            // Check for the condition field specifically
            if (isset($firstListing['condition_item'])) {
                echo "<p style='color: green;'>✅ Found 'condition_item' field: {$firstListing['condition_item']}</p>";
            } else {
                echo "<p style='color: red;'>❌ Missing 'condition_item' field</p>";
            }
            
            if (isset($firstListing['condition_type'])) {
                echo "<p style='color: green;'>✅ Found 'condition_type' field: {$firstListing['condition_type']}</p>";
            } else {
                echo "<p style='color: red;'>❌ Missing 'condition_type' field</p>";
            }
        }
    }
    
    if (isset($data['message'])) {
        echo "<p><strong>Message:</strong> {$data['message']}</p>";
    }
    
} else {
    echo "<p style='color: red;'><strong>❌ Failed to parse JSON response</strong></p>";
    echo "<p>JSON Error: " . json_last_error_msg() . "</p>";
}

// Also test a direct database query to see what we have
echo "<h3>Direct Database Query Test:</h3>";

try {
    $db = getDBConnection();
    
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
            l.status,
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
        LIMIT 3
    ";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $listings = $stmt->fetchAll();
    
    echo "<p><strong>Direct query found:</strong> " . count($listings) . " active listings</p>";
    
    if (count($listings) > 0) {
        echo "<h4>Sample Listing from Database:</h4>";
        $sample = $listings[0];
        
        echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>";
        foreach ($sample as $key => $value) {
            echo "<tr><td><strong>{$key}</strong></td><td>" . htmlspecialchars($value) . "</td></tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Database Error:</strong> " . $e->getMessage() . "</p>";
}

?>

<style>
body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
h1 { color: #2c3e50; }
h3 { color: #3498db; }
h4 { color: #e67e22; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f8f9fa; }
</style>
