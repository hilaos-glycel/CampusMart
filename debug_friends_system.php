<?php
/**
 * Debug Friends System
 * Check what's causing the loading issues
 */

require_once 'config/config.php';

echo "<h1>🔍 Debugging Friends System</h1>";

// Login as hilaos for testing
if (!isLoggedIn()) {
    echo "<p>Logging in as hilaos for testing...</p>";
    
    try {
        require_once 'includes/auth.php';
        $auth = new Auth();
        $result = $auth->login('hilaos', 'hilaos123');
        
        if ($result['success']) {
            echo "<p style='color: green;'>✅ Logged in successfully as {$_SESSION['username']}</p>";
        } else {
            echo "<p style='color: red;'>❌ Login failed: {$result['message']}</p>";
            exit;
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Login error: {$e->getMessage()}</p>";
        exit;
    }
} else {
    echo "<p style='color: green;'>✅ Already logged in as {$_SESSION['username']}</p>";
}

try {
    $db = getDBConnection();
    echo "<p>✅ Database connection successful</p>";
    
    // Check if tables exist
    echo "<h3>1. Checking Tables:</h3>";
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredTables = ['user_status', 'friend_requests', 'friends'];
    
    foreach ($requiredTables as $table) {
        if (in_array($table, $tables)) {
            echo "<p style='color: green;'>✅ {$table} table exists</p>";
        } else {
            echo "<p style='color: red;'>❌ {$table} table missing</p>";
        }
    }
    
    // Check user_status data
    echo "<h3>2. Checking User Status Data:</h3>";
    $stmt = $db->query("SELECT u.username, us.is_online, us.last_seen FROM users u LEFT JOIN user_status us ON u.id = us.user_id");
    $users = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f8f9fa;'><th>Username</th><th>Online</th><th>Last Seen</th></tr>";
    foreach ($users as $user) {
        $onlineStatus = $user['is_online'] ? 'Yes' : 'No';
        $lastSeen = $user['last_seen'] ?: 'Never';
        echo "<tr><td>{$user['username']}</td><td>{$onlineStatus}</td><td>{$lastSeen}</td></tr>";
    }
    echo "</table>";
    
    // Test the get_online_users API
    echo "<h3>3. Testing get_online_users API:</h3>";
    
    ob_start();
    include 'api/get_online_users.php';
    $apiOutput = ob_get_clean();
    
    echo "<h4>API Response:</h4>";
    echo "<pre style='background: #f8f8f8; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
    echo htmlspecialchars($apiOutput);
    echo "</pre>";
    
    $data = json_decode($apiOutput, true);
    if ($data && $data['success']) {
        echo "<p style='color: green;'>✅ API working</p>";
        echo "<p>Online users: {$data['counts']['online']}</p>";
        echo "<p>Total users: {$data['counts']['total']}</p>";
    } else {
        echo "<p style='color: red;'>❌ API failed</p>";
        if ($data && isset($data['message'])) {
            echo "<p>Error: {$data['message']}</p>";
        }
    }
    
    // Set current user as online for testing
    echo "<h3>4. Setting Current User Online:</h3>";
    $currentUser = getCurrentUser();
    $userId = $currentUser['id'];
    
    $updateStmt = $db->prepare("
        INSERT INTO user_status (user_id, is_online, last_seen)
        VALUES (?, TRUE, NOW())
        ON DUPLICATE KEY UPDATE
        is_online = TRUE,
        last_seen = NOW()
    ");
    
    $result = $updateStmt->execute([$userId]);
    
    if ($result) {
        echo "<p style='color: green;'>✅ Set {$currentUser['username']} as online</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to set user online</p>";
    }
    
    // Set other users as online for testing
    echo "<h3>5. Setting Other Users Online for Testing:</h3>";
    $otherUsers = $db->query("SELECT id, username FROM users WHERE id != {$userId} LIMIT 2")->fetchAll();
    
    foreach ($otherUsers as $user) {
        $updateStmt->execute([$user['id']]);
        echo "<p style='color: green;'>✅ Set {$user['username']} as online</p>";
    }
    
    echo "<h3>6. Re-testing API:</h3>";
    ob_start();
    include 'api/get_online_users.php';
    $apiOutput2 = ob_get_clean();
    
    $data2 = json_decode($apiOutput2, true);
    if ($data2 && $data2['success']) {
        echo "<p style='color: green;'>✅ API now shows {$data2['counts']['online']} online users</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>Next Steps:</h3>";
echo "<ul>";
echo "<li><a href='messages.php' target='_blank'>Test Messages Page (Should Show Online Users Now)</a></li>";
echo "<li><a href='api/get_online_users.php' target='_blank'>Test API Directly</a></li>";
echo "</ul>";

?>

<style>
body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
h1 { color: #2c3e50; }
h3 { color: #3498db; }
h4 { color: #e67e22; }
table { width: 100%; margin: 10px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f8f9fa; font-weight: bold; }
</style>
