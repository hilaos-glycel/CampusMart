<?php
/**
 * Test Header Fix
 * Verify that the header already sent error is resolved
 */

echo "<h1>🧪 Testing Header Fix</h1>";

echo "<h3>Testing Pages That Previously Had Header Issues:</h3>";

$testPages = [
    'dashboard.php' => 'Dashboard (requires login)',
    'post-item.php' => 'Post Item (requires login)',
    'post-service.php' => 'Post Service (requires login)'
];

foreach ($testPages as $page => $description) {
    echo "<p><strong>{$description}:</strong> ";
    
    if (file_exists($page)) {
        // Check if the file structure is correct
        $content = file_get_contents($page);
        
        // Check if requireLogin() comes before header.php include
        $configPos = strpos($content, "require_once 'config/config.php'");
        $requireLoginPos = strpos($content, 'requireLogin()');
        $headerPos = strpos($content, "require_once 'includes/header.php'");
        
        if ($configPos !== false && $requireLoginPos !== false && $headerPos !== false) {
            if ($configPos < $requireLoginPos && $requireLoginPos < $headerPos) {
                echo "<span style='color: green;'>✅ Fixed - Correct order</span>";
            } else {
                echo "<span style='color: red;'>❌ Wrong order</span>";
            }
        } else {
            echo "<span style='color: orange;'>⚠️ Structure unclear</span>";
        }
        
        echo " - <a href='{$page}' target='_blank'>Test Page</a>";
    } else {
        echo "<span style='color: red;'>❌ File not found</span>";
    }
    
    echo "</p>";
}

echo "<h3>Explanation of the Fix:</h3>";
echo "<p><strong>Problem:</strong> Pages were including <code>header.php</code> (which outputs HTML) before calling <code>requireLogin()</code> (which sends redirect headers).</p>";
echo "<p><strong>Solution:</strong> Moved <code>requireLogin()</code> to be called BEFORE including <code>header.php</code>.</p>";

echo "<h3>Correct Order:</h3>";
echo "<ol>";
echo "<li>Include <code>config/config.php</code></li>";
echo "<li>Call <code>requireLogin()</code> (can send headers)</li>";
echo "<li>Include <code>header.php</code> (starts HTML output)</li>";
echo "<li>Continue with page content</li>";
echo "</ol>";

echo "<h3>Test Results:</h3>";
echo "<p>If the fix worked correctly:</p>";
echo "<ul>";
echo "<li>✅ No 'headers already sent' warnings</li>";
echo "<li>✅ Login redirects work properly</li>";
echo "<li>✅ Protected pages require authentication</li>";
echo "</ul>";

echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 8px; margin-top: 20px;'>";
echo "<h4>🎯 How to Test:</h4>";
echo "<ol>";
echo "<li><strong>Logout</strong> if you're currently logged in</li>";
echo "<li><strong>Try to access</strong> dashboard.php, post-item.php, or post-service.php</li>";
echo "<li><strong>Should redirect</strong> to login.php without any warnings</li>";
echo "<li><strong>Login</strong> and try accessing the pages again</li>";
echo "<li><strong>Should work</strong> without any header warnings</li>";
echo "</ol>";
echo "</div>";

?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
h1 { color: #2c3e50; }
h3 { color: #3498db; }
h4 { color: #e67e22; }
code { background: #f8f8f8; padding: 2px 4px; border-radius: 3px; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
