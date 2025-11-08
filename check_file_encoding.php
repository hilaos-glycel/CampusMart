<?php
/**
 * Check File Encoding and Whitespace Issues
 * Look for potential causes of "headers already sent" errors
 */

echo "<h1>🔍 Checking for File Encoding Issues</h1>";

$criticalFiles = [
    'config/config.php',
    'includes/header.php',
    'includes/footer.php',
    'dashboard.php',
    'post-item.php',
    'post-service.php'
];

echo "<h3>Checking Critical Files:</h3>";

foreach ($criticalFiles as $file) {
    echo "<p><strong>{$file}:</strong> ";
    
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Check for BOM (Byte Order Mark)
        $hasBOM = (substr($content, 0, 3) === "\xEF\xBB\xBF");
        
        // Check for whitespace before <?php
        $firstPhpPos = strpos($content, '<?php');
        $hasWhitespaceBefore = ($firstPhpPos > 0);
        
        // Check for output after ?>
        $lastPhpClosePos = strrpos($content, '?>');
        $hasOutputAfter = false;
        if ($lastPhpClosePos !== false) {
            $afterClose = substr($content, $lastPhpClosePos + 2);
            $hasOutputAfter = (trim($afterClose) !== '');
        }
        
        $issues = [];
        if ($hasBOM) $issues[] = "BOM detected";
        if ($hasWhitespaceBefore) $issues[] = "Whitespace before <?php";
        if ($hasOutputAfter) $issues[] = "Output after ?>";
        
        if (empty($issues)) {
            echo "<span style='color: green;'>✅ Clean</span>";
        } else {
            echo "<span style='color: orange;'>⚠️ " . implode(', ', $issues) . "</span>";
        }
        
        // Show first few characters for debugging
        $firstChars = substr($content, 0, 10);
        $firstCharsDisplay = '';
        for ($i = 0; $i < strlen($firstChars); $i++) {
            $char = $firstChars[$i];
            if ($char === "\n") $firstCharsDisplay .= '\\n';
            elseif ($char === "\r") $firstCharsDisplay .= '\\r';
            elseif ($char === "\t") $firstCharsDisplay .= '\\t';
            elseif (ord($char) < 32) $firstCharsDisplay .= '\\x' . sprintf('%02X', ord($char));
            else $firstCharsDisplay .= $char;
        }
        echo " (starts: '{$firstCharsDisplay}')";
        
    } else {
        echo "<span style='color: red;'>❌ File not found</span>";
    }
    
    echo "</p>";
}

echo "<h3>Common Causes of 'Headers Already Sent' Error:</h3>";
echo "<ul>";
echo "<li><strong>Whitespace before &lt;?php:</strong> Any space, tab, or newline before the opening PHP tag</li>";
echo "<li><strong>BOM (Byte Order Mark):</strong> Invisible characters added by some text editors</li>";
echo "<li><strong>Output before headers:</strong> Any echo, print, or HTML output before header() calls</li>";
echo "<li><strong>Wrong include order:</strong> Including files that output HTML before sending headers</li>";
echo "</ul>";

echo "<h3>✅ Our Fix:</h3>";
echo "<p>We fixed the main issue by reordering the includes:</p>";
echo "<ol>";
echo "<li><code>require_once 'config/config.php';</code> - Load functions</li>";
echo "<li><code>requireLogin();</code> - Send redirect headers if needed</li>";
echo "<li><code>require_once 'includes/header.php';</code> - Start HTML output</li>";
echo "</ol>";

echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 8px; margin-top: 20px;'>";
echo "<h4>🎯 The Error Should Now Be Fixed!</h4>";
echo "<p>The 'Cannot modify header information - headers already sent' error should no longer appear when:</p>";
echo "<ul>";
echo "<li>Accessing protected pages while not logged in</li>";
echo "<li>Using the login redirect functionality</li>";
echo "<li>Navigating between authenticated pages</li>";
echo "</ul>";
echo "</div>";

?>

<style>
body { font-family: Arial, sans-serif; max-width: 900px; margin: 0 auto; padding: 20px; }
h1 { color: #2c3e50; }
h3 { color: #3498db; }
h4 { color: #e67e22; }
code { background: #f8f8f8; padding: 2px 4px; border-radius: 3px; font-family: monospace; }
ul, ol { margin: 10px 0; }
li { margin: 5px 0; }
</style>
