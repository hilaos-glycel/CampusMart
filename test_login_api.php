<?php
/**
 * Test Login API directly
 */

require_once 'config/config.php';

echo "<h2>Testing Login API</h2>";

// Test credentials
$testCredentials = [
    ['loginId' => 'hilaos', 'password' => 'hilaos123'],
    ['loginId' => 'sapuay', 'password' => 'sapuay123'],
    ['loginId' => 'legaspi', 'password' => 'legaspi123']
];

foreach ($testCredentials as $cred) {
    echo "<h3>Testing: {$cred['loginId']}</h3>";
    
    try {
        require_once 'includes/auth.php';
        $auth = new Auth();
        $result = $auth->login($cred['loginId'], $cred['password']);
        
        if ($result['success']) {
            echo "✅ Login successful for {$cred['loginId']}<br>";
            echo "User ID: " . $_SESSION['user_id'] . "<br>";
            echo "Name: " . $_SESSION['first_name'] . " " . $_SESSION['last_name'] . "<br>";
        } else {
            echo "❌ Login failed for {$cred['loginId']}: " . $result['message'] . "<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Error testing {$cred['loginId']}: " . $e->getMessage() . "<br>";
    }
    
    echo "<hr>";
}

echo "<br><a href='login.php'>Go to Login Page</a>";
?>
