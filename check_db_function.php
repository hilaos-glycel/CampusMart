<?php
/**
 * Check if getDBConnection function works
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Function Test</h2>";

// Test 1: Include config
echo "<h3>Test 1: Include Config</h3>";
try {
    require_once 'config/config.php';
    echo "✅ Config loaded<br>";
} catch (Exception $e) {
    echo "❌ Config error: " . $e->getMessage() . "<br>";
    exit();
}

// Test 2: Check if function exists
echo "<h3>Test 2: Function Exists</h3>";
if (function_exists('getDBConnection')) {
    echo "✅ getDBConnection function exists<br>";
} else {
    echo "❌ getDBConnection function not found<br>";
    exit();
}

// Test 3: Call function
echo "<h3>Test 3: Call Function</h3>";
try {
    $db = getDBConnection();
    echo "✅ getDBConnection() successful<br>";
    echo "Connection type: " . get_class($db) . "<br>";
} catch (Exception $e) {
    echo "❌ getDBConnection() error: " . $e->getMessage() . "<br>";
    echo "Error file: " . $e->getFile() . "<br>";
    echo "Error line: " . $e->getLine() . "<br>";
}

// Test 4: Direct Database class
echo "<h3>Test 4: Direct Database Class</h3>";
try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    echo "✅ Direct Database class works<br>";
} catch (Exception $e) {
    echo "❌ Direct Database class error: " . $e->getMessage() . "<br>";
}

// Test 5: Simple query
echo "<h3>Test 5: Simple Query</h3>";
try {
    if (isset($db)) {
        $stmt = $db->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        echo "✅ Query successful - Users count: " . $result['count'] . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Query error: " . $e->getMessage() . "<br>";
}

echo "<br><a href='minimal_login_test.php'>Try Minimal Login Test</a>";
?>
