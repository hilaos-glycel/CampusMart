<?php
/**
 * Check Listings in Database
 * Debug script to see what listings exist
 */

require_once 'config/config.php';

echo "<h1>🔍 Checking Listings in Database</h1>";

try {
    $db = getDBConnection();
    echo "<p>✅ Database connection successful</p>";
    
    // Check total number of listings
    $stmt = $db->query("SELECT COUNT(*) as total FROM listings");
    $result = $stmt->fetch();
    echo "<h3>Total Listings: {$result['total']}</h3>";
    
    if ($result['total'] > 0) {
        // Get all listings with details
        $stmt = $db->query("
            SELECT 
                l.id,
                l.title,
                l.description,
                l.price,
                l.type,
                l.condition_item,
                l.status,
                l.created_at,
                c.name as category_name,
                c.slug as category_slug,
                u.username,
                CONCAT(u.first_name, ' ', u.last_name) as seller_name
            FROM listings l
            LEFT JOIN categories c ON l.category_id = c.id
            LEFT JOIN users u ON l.user_id = u.id
            ORDER BY l.created_at DESC
        ");
        
        $listings = $stmt->fetchAll();
        
        echo "<h3>All Listings:</h3>";
        echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f8f9fa;'>";
        echo "<th>ID</th><th>Title</th><th>Price</th><th>Type</th><th>Condition</th><th>Status</th><th>Category</th><th>Seller</th><th>Created</th>";
        echo "</tr>";
        
        foreach ($listings as $listing) {
            $statusColor = $listing['status'] === 'active' ? 'green' : 'red';
            echo "<tr>";
            echo "<td>{$listing['id']}</td>";
            echo "<td><strong>{$listing['title']}</strong></td>";
            echo "<td>₱" . number_format($listing['price'], 2) . "</td>";
            echo "<td>" . ucfirst($listing['type']) . "</td>";
            echo "<td>{$listing['condition_item']}</td>";
            echo "<td style='color: {$statusColor};'><strong>{$listing['status']}</strong></td>";
            echo "<td>{$listing['category_name']} ({$listing['category_slug']})</td>";
            echo "<td>{$listing['seller_name']} (@{$listing['username']})</td>";
            echo "<td>" . date('M j, Y g:i A', strtotime($listing['created_at'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check active listings specifically
        $stmt = $db->query("SELECT COUNT(*) as active_count FROM listings WHERE status = 'active'");
        $activeResult = $stmt->fetch();
        echo "<h3>Active Listings: {$activeResult['active_count']}</h3>";
        
        if ($activeResult['active_count'] == 0) {
            echo "<p style='color: red;'><strong>⚠️ No active listings found!</strong></p>";
            echo "<p>This might be why nothing is showing in the marketplace.</p>";
        }
        
    } else {
        echo "<p style='color: red;'><strong>❌ No listings found in database!</strong></p>";
        echo "<p>This means the listings are not being saved when you post them.</p>";
    }
    
    // Check categories
    echo "<h3>Available Categories:</h3>";
    $stmt = $db->query("SELECT id, name, slug FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #f8f9fa;'><th>ID</th><th>Name</th><th>Slug</th></tr>";
    foreach ($categories as $cat) {
        echo "<tr><td>{$cat['id']}</td><td>{$cat['name']}</td><td>{$cat['slug']}</td></tr>";
    }
    echo "</table>";
    
    // Check users
    echo "<h3>Available Users:</h3>";
    $stmt = $db->query("SELECT id, username, first_name, last_name FROM users ORDER BY username");
    $users = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #f8f9fa;'><th>ID</th><th>Username</th><th>Name</th></tr>";
    foreach ($users as $user) {
        echo "<tr><td>{$user['id']}</td><td>{$user['username']}</td><td>{$user['first_name']} {$user['last_name']}</td></tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ Error:</strong> " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
h1 { color: #2c3e50; }
h3 { color: #3498db; }
table { margin: 10px 0; }
th { background-color: #f8f9fa; font-weight: bold; }
td, th { padding: 8px; text-align: left; border: 1px solid #ddd; }
</style>
