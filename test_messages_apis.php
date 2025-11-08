<?php
/**
 * Test Messages APIs
 * Test both get_conversations and get_messages APIs
 */

require_once 'config/config.php';

echo "<h1>🧪 Testing Messages APIs</h1>";

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

// Test get_conversations API
echo "<h3>1. Testing get_conversations.php:</h3>";

try {
    ob_start();
    include 'api/get_conversations.php';
    $conversationsOutput = ob_get_clean();
    
    $conversationsData = json_decode($conversationsOutput, true);
    
    if ($conversationsData && $conversationsData['success']) {
        echo "<p style='color: green;'>✅ Conversations API working</p>";
        echo "<p>Found {$conversationsData['total']} conversations</p>";
        
        if (!empty($conversationsData['conversations'])) {
            $testConversationId = $conversationsData['conversations'][0]['id'];
            echo "<p>Test conversation ID: <strong>{$testConversationId}</strong></p>";
            
            // Test get_messages API
            echo "<h3>2. Testing get_messages.php:</h3>";
            
            $_GET['conversation_id'] = $testConversationId;
            
            ob_start();
            include 'api/get_messages.php';
            $messagesOutput = ob_get_clean();
            
            echo "<h4>Messages API Response:</h4>";
            echo "<pre style='background: #f8f8f8; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
            echo htmlspecialchars($messagesOutput);
            echo "</pre>";
            
            $messagesData = json_decode($messagesOutput, true);
            
            if ($messagesData && $messagesData['success']) {
                echo "<p style='color: green;'>✅ Messages API working</p>";
                echo "<p>Found {$messagesData['pagination']['total']} messages</p>";
                
                if (!empty($messagesData['messages'])) {
                    echo "<h4>Sample Messages:</h4>";
                    echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
                    echo "<tr style='background: #f8f9fa;'><th>Sender</th><th>Message</th><th>Time</th><th>Read</th></tr>";
                    
                    foreach (array_slice($messagesData['messages'], 0, 3) as $msg) {
                        echo "<tr>";
                        echo "<td>{$msg['sender_name']}</td>";
                        echo "<td>" . substr($msg['message'], 0, 50) . "...</td>";
                        echo "<td>" . date('M j, g:i A', strtotime($msg['created_at'])) . "</td>";
                        echo "<td>" . ($msg['is_read'] ? 'Yes' : 'No') . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
                
                echo "<h2 style='color: green;'>🎉 All APIs Working!</h2>";
                echo "<p>Both conversations and messages APIs are functioning correctly.</p>";
                
            } else {
                echo "<p style='color: red;'>❌ Messages API failed</p>";
                if ($messagesData && isset($messagesData['message'])) {
                    echo "<p>Error: {$messagesData['message']}</p>";
                }
            }
            
        } else {
            echo "<p style='color: orange;'>⚠️ No conversations found to test messages API</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Conversations API failed</p>";
        if ($conversationsData && isset($conversationsData['message'])) {
            echo "<p>Error: {$conversationsData['message']}</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ API test error: " . $e->getMessage() . "</p>";
}

echo "<h3>Next Steps:</h3>";
echo "<ul>";
echo "<li><a href='messages.php' target='_blank'>Test Messages Page (Should Work Now)</a></li>";
echo "<li><a href='test_messages_complete.html' target='_blank'>View Complete Test Results</a></li>";
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
