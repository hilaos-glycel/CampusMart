<?php
/**
 * Test Send Message API
 * Debug the 400 Bad Request error
 */

require_once 'config/config.php';

echo "<h1>🧪 Testing Send Message API</h1>";

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

// Test the send message API with different scenarios
echo "<h3>Testing Send Message API Scenarios:</h3>";

// Test 1: Missing conversation_id
echo "<h4>Test 1: Missing conversation_id</h4>";
$testData1 = json_encode(['message' => 'Test message']);
$response1 = testSendMessageAPI($testData1);
echo "<p>Response: " . htmlspecialchars($response1) . "</p>";

// Test 2: Missing message
echo "<h4>Test 2: Missing message</h4>";
$testData2 = json_encode(['conversation_id' => 1]);
$response2 = testSendMessageAPI($testData2);
echo "<p>Response: " . htmlspecialchars($response2) . "</p>";

// Test 3: Valid data
echo "<h4>Test 3: Valid data</h4>";
$testData3 = json_encode(['conversation_id' => 1, 'message' => 'Test message from API test']);
$response3 = testSendMessageAPI($testData3);
echo "<p>Response: " . htmlspecialchars($response3) . "</p>";

// Test 4: Check what the frontend is actually sending
echo "<h3>Frontend JavaScript Analysis:</h3>";
echo "<p>Check what data the frontend JavaScript is sending to the API.</p>";

function testSendMessageAPI($jsonData) {
    $url = 'http://localhost/CampusMart/api/send_message.php';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonData)
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return "HTTP {$httpCode}: " . $response;
}

echo "<h3>Debugging Steps:</h3>";
echo "<ol>";
echo "<li>Check if the API is receiving the correct JSON data</li>";
echo "<li>Verify the conversation ID exists and user has access</li>";
echo "<li>Check if the message content is valid</li>";
echo "<li>Look at the frontend JavaScript to see what it's sending</li>";
echo "</ol>";

echo "<h3>Common Causes of 400 Bad Request:</h3>";
echo "<ul>";
echo "<li><strong>Missing conversation_id:</strong> API requires conversation_id parameter</li>";
echo "<li><strong>Empty message:</strong> Message cannot be empty</li>";
echo "<li><strong>Invalid JSON:</strong> Malformed JSON data</li>";
echo "<li><strong>Wrong Content-Type:</strong> Must be application/json</li>";
echo "<li><strong>Access denied:</strong> User not participant in conversation</li>";
echo "</ul>";

?>

<style>
body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
h1 { color: #2c3e50; }
h3 { color: #3498db; }
h4 { color: #e67e22; }
</style>
