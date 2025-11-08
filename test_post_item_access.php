<?php
/**
 * Test Post Item Page Access
 */

session_start();

echo "<h2>Testing Post Item Page Access</h2>";

// Test 1: Check if user is logged in
if (isset($_SESSION['user_id'])) {
    echo "✅ User is logged in (ID: " . $_SESSION['user_id'] . ")<br>";
    echo "✅ Username: " . ($_SESSION['username'] ?? 'Not set') . "<br>";
    echo "✅ Name: " . ($_SESSION['first_name'] ?? 'Not set') . " " . ($_SESSION['last_name'] ?? 'Not set') . "<br>";
    
    echo "<br><strong>Testing post-item.php access:</strong><br>";
    echo "<a href='post-item.php' target='_blank'>Open Post Item Page</a><br>";
    
} else {
    echo "❌ User is not logged in<br>";
    echo "<a href='login.php'>Please log in first</a><br>";
}

echo "<br><hr><br>";
echo "<a href='dashboard.php'>Dashboard</a> | ";
echo "<a href='marketplace.php'>Marketplace</a> | ";
echo "<a href='login.php'>Login</a>";
?>
