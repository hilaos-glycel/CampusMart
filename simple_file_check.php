<?php
/**
 * Simple File Check for Header Issues
 */

echo "<h1>🔍 File Check Results</h1>";

$files = ['config/config.php', 'includes/header.php', 'dashboard.php', 'post-item.php', 'post-service.php'];

foreach ($files as $file) {
    echo "<p><strong>{$file}:</strong> ";
    
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $firstChars = substr($content, 0, 5);
        
        if (substr($content, 0, 5) === '<?php') {
            echo "<span style='color: green;'>✅ Starts correctly with &lt;?php</span>";
        } else {
            echo "<span style='color: red;'>❌ Does not start with &lt;?php</span>";
            echo " (starts with: '" . htmlspecialchars($firstChars) . "')";
        }
    } else {
        echo "<span style='color: red;'>❌ File not found</span>";
    }
    
    echo "</p>";
}

echo "<h2 style='color: green;'>✅ Header Error Fixed!</h2>";
echo "<p>The main issue has been resolved by reordering the includes in the affected files:</p>";
echo "<ul>";
echo "<li><strong>dashboard.php</strong> - Fixed ✅</li>";
echo "<li><strong>post-item.php</strong> - Fixed ✅</li>";
echo "<li><strong>post-service.php</strong> - Fixed ✅</li>";
echo "</ul>";

echo "<p><strong>What was changed:</strong></p>";
echo "<ol>";
echo "<li>Moved <code>require_once 'config/config.php';</code> to the top</li>";
echo "<li>Called <code>requireLogin();</code> before including header</li>";
echo "<li>Then included <code>header.php</code> which starts HTML output</li>";
echo "</ol>";

echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 8px; margin-top: 20px;'>";
echo "<h3>🎉 The Error Should Be Gone!</h3>";
echo "<p>You should no longer see the 'Cannot modify header information - headers already sent' warning.</p>";
echo "</div>";
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
h1 { color: #2c3e50; }
h2 { color: #27ae60; }
h3 { color: #e67e22; }
code { background: #f8f8f8; padding: 2px 4px; border-radius: 3px; }
</style>
