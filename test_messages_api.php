<?php
/**
 * Test Messages API
 * Login as a user and test the conversations API
 */

require_once 'config/config.php';

echo "<h1>🧪 Testing Messages API</h1>";

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

// Now test the API
echo "<h3>Testing get_conversations.php API:</h3>";

try {
    // Capture API output
    ob_start();
    include 'api/get_conversations.php';
    $apiOutput = ob_get_clean();
    
    echo "<h4>Raw API Response:</h4>";
    echo "<pre style='background: #f8f8f8; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
    echo htmlspecialchars($apiOutput);
    echo "</pre>";
    
    // Try to decode JSON
    $data = json_decode($apiOutput, true);
    
    if ($data) {
        echo "<p style='color: green;'>✅ Valid JSON response</p>";
        echo "<p><strong>Success:</strong> " . ($data['success'] ? 'true' : 'false') . "</p>";
        
        if ($data['success']) {
            echo "<p><strong>Total conversations:</strong> " . count($data['conversations']) . "</p>";
            
            if (!empty($data['conversations'])) {
                echo "<h4>Conversations:</h4>";
                echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr style='background: #f8f9fa;'><th>ID</th><th>Other User</th><th>Last Message</th><th>Unread</th></tr>";
                
                foreach ($data['conversations'] as $conv) {
                    $lastMsg = $conv['last_message'] ? substr($conv['last_message']['text'], 0, 50) . '...' : 'No messages';
                    echo "<tr>";
                    echo "<td>{$conv['id']}</td>";
                    echo "<td>{$conv['other_user']['name']} (@{$conv['other_user']['username']})</td>";
                    echo "<td>{$lastMsg}</td>";
                    echo "<td>{$conv['unread_count']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
                
                echo "<h2 style='color: green;'>🎉 Messages API Working!</h2>";
                echo "<p>The conversations are loading correctly. The messages page should now work.</p>";
                
            } else {
                echo "<p style='color: orange;'>⚠️ No conversations found, but API is working</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ API returned error: {$data['message']}</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Invalid JSON response</p>";
        echo "<p>JSON Error: " . json_last_error_msg() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ API test error: " . $e->getMessage() . "</p>";
}

echo "<h3>Next Steps:</h3>";
echo "<ul>";
echo "<li><a href='messages.php' target='_blank'>Test Messages Page</a></li>";
echo "<li><a href='dashboard.php' target='_blank'>Go to Dashboard</a></li>";
echo "<li><a href='marketplace.php' target='_blank'>Browse Marketplace</a></li>";
echo "</ul>";

?>

<style>
body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
h1 { color: #2c3e50; }
h2 { color: #27ae60; }
h3 { color: #3498db; }
h4 { color: #e67e22; }
table { width: 100%; margin: 10px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f8f9fa; font-weight: bold; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
