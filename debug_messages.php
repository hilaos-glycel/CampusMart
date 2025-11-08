<?php
/**
 * Debug Messages System
 * Check what's causing the messages error
 */

require_once 'config/config.php';

echo "<h1>🔍 Debugging Messages System</h1>";

try {
    $db = getDBConnection();
    echo "<p>✅ Database connection successful</p>";
    
    // Check if conversations table exists
    echo "<h3>1. Checking Tables:</h3>";
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $messagingTables = ['conversations', 'messages', 'message_reactions', 'message_attachments'];
    
    foreach ($messagingTables as $table) {
        if (in_array($table, $tables)) {
            echo "<p style='color: green;'>✅ {$table} table exists</p>";
        } else {
            echo "<p style='color: red;'>❌ {$table} table missing</p>";
        }
    }
    
    // If conversations table exists, check its structure
    if (in_array('conversations', $tables)) {
        echo "<h3>2. Conversations Table Structure:</h3>";
        $stmt = $db->query("DESCRIBE conversations");
        $columns = $stmt->fetchAll();
        
        echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f8f9fa;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        foreach ($columns as $col) {
            echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td><td>{$col['Null']}</td><td>{$col['Key']}</td></tr>";
        }
        echo "</table>";
        
        // Check conversation count
        $stmt = $db->query("SELECT COUNT(*) as count FROM conversations");
        $count = $stmt->fetch()['count'];
        echo "<p><strong>Total conversations:</strong> {$count}</p>";
        
        if ($count > 0) {
            echo "<h3>3. Sample Conversations:</h3>";
            $stmt = $db->query("SELECT * FROM conversations LIMIT 3");
            $conversations = $stmt->fetchAll();
            
            echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
            echo "<tr style='background: #f8f9fa;'><th>ID</th><th>Participant 1</th><th>Participant 2</th><th>Last Activity</th></tr>";
            foreach ($conversations as $conv) {
                echo "<tr><td>{$conv['id']}</td><td>{$conv['participant_1']}</td><td>{$conv['participant_2']}</td><td>{$conv['last_activity']}</td></tr>";
            }
            echo "</table>";
        }
    }
    
    // Test the API directly
    echo "<h3>4. Testing get_conversations.php API:</h3>";
    
    // Check if user is logged in
    if (isLoggedIn()) {
        echo "<p style='color: green;'>✅ User is logged in: {$_SESSION['username']}</p>";
        
        // Test API call
        ob_start();
        include 'api/get_conversations.php';
        $apiOutput = ob_get_clean();
        
        echo "<h4>API Response:</h4>";
        echo "<pre style='background: #f8f8f8; padding: 10px; border-radius: 5px; overflow-x: auto;'>";
        echo htmlspecialchars($apiOutput);
        echo "</pre>";
        
        // Try to decode JSON
        $data = json_decode($apiOutput, true);
        if ($data) {
            echo "<p style='color: green;'>✅ Valid JSON response</p>";
            echo "<p>Success: " . ($data['success'] ? 'true' : 'false') . "</p>";
            if (isset($data['conversations'])) {
                echo "<p>Conversations count: " . count($data['conversations']) . "</p>";
            }
            if (isset($data['message'])) {
                echo "<p>Message: {$data['message']}</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Invalid JSON response</p>";
            echo "<p>JSON Error: " . json_last_error_msg() . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ User not logged in</p>";
        echo "<p><a href='login.php'>Login first</a> to test the messages API</p>";
    }
    
    echo "<h3>5. Recommendations:</h3>";
    
    if (!in_array('conversations', $tables)) {
        echo "<p style='color: red;'><strong>Main Issue:</strong> Conversations table is missing!</p>";
        echo "<p><strong>Solution:</strong> Run the messaging setup script to create the required tables.</p>";
        echo "<p><a href='setup_messaging.php' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Setup Messaging Tables</a></p>";
    } else {
        echo "<p style='color: green;'>✅ Database tables exist</p>";
        echo "<p>The issue might be in the API code or JavaScript. Check browser console for errors.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

?>

<style>
body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
h1 { color: #2c3e50; }
h3 { color: #3498db; }
h4 { color: #e67e22; }
table { width: 100%; margin: 10px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f8f9fa; font-weight: bold; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
