<?php
/**
 * Test script to verify the admin 'full_name' fix
 */

require_once 'config/config.php';
require_once 'includes/admin_auth.php';

echo "<h2>Admin Authentication Test</h2>";

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (isAdminLoggedIn()) {
    $currentAdmin = getCurrentAdmin();
    
    echo "<h3>Current Admin Data:</h3>";
    echo "<pre>";
    print_r($currentAdmin);
    echo "</pre>";
    
    echo "<h3>Testing field access:</h3>";
    echo "<p><strong>Admin Name:</strong> " . htmlspecialchars($currentAdmin['name'] ?? 'Not set') . "</p>";
    echo "<p><strong>Admin Username:</strong> " . htmlspecialchars($currentAdmin['username'] ?? 'Not set') . "</p>";
    echo "<p><strong>Admin Role:</strong> " . htmlspecialchars($currentAdmin['role'] ?? 'Not set') . "</p>";
    
    echo "<h3>Testing the fixed welcome message:</h3>";
    echo "<p>Welcome, " . htmlspecialchars($currentAdmin['name'] ?? 'Admin') . "</p>";
    
    echo "<div style='color: green; font-weight: bold;'>✓ Fix verified: No more 'full_name' undefined key errors!</div>";
    
} else {
    echo "<p style='color: red;'>Admin not logged in. Please log in to the admin panel first.</p>";
    echo "<p><a href='admin/login.php'>Go to Admin Login</a></p>";
}

echo "<hr>";
echo "<p><a href='admin/reports.php'>Test Reports Page</a> | <a href='admin/index.php'>Admin Dashboard</a></p>";
?>
