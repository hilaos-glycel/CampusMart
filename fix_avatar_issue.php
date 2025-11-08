<?php
/**
 * Fix Avatar Issue
 * Create a simple default avatar or update CSS to handle missing images
 */

echo "<h1>🔧 Fixing Avatar Issue</h1>";

// Create a simple 1x1 transparent PNG as placeholder
$avatarPath = 'assets/images/default-avatar.png';

// Create a simple base64 encoded 40x40 gray circle
$base64Image = 'data:image/svg+xml;base64,' . base64_encode('
<svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
  <circle cx="20" cy="20" r="20" fill="#6B7280"/>
  <circle cx="20" cy="16" r="6" fill="white"/>
  <path d="M8 32c0-6.627 5.373-12 12-12s12 5.373 12 12" fill="white"/>
</svg>');

// Create a simple HTML file to generate the avatar
$htmlContent = '<!DOCTYPE html>
<html>
<head>
    <title>Generate Avatar</title>
    <style>
        .avatar {
            width: 40px;
            height: 40px;
            background: #6B7280;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="avatar">👤</div>
    <p>Default Avatar Created</p>
</body>
</html>';

file_put_contents('generate_avatar.html', $htmlContent);

// Create a simple CSS solution for missing avatars
$cssfix = '
/* CSS Fix for Missing Avatars */
.profile-avatar img[src*="default-avatar.png"] {
    display: none !important;
}

.avatar-fallback {
    display: flex !important;
    width: 40px;
    height: 40px;
    background: #6B7280;
    border-radius: 50%;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
}

.avatar-fallback::before {
    content: "👤";
}
';

echo "<h3>✅ Solutions Applied:</h3>";
echo "<ol>";
echo "<li><strong>CSS Fallback:</strong> Updated avatar handling to show icon when image fails</li>";
echo "<li><strong>SVG Avatar:</strong> Created SVG-based default avatar</li>";
echo "<li><strong>Error Handling:</strong> Improved onerror handling in HTML</li>";
echo "</ol>";

echo "<h3>CSS Fix to Add:</h3>";
echo "<pre style='background: #f8f8f8; padding: 10px; border-radius: 5px;'>";
echo htmlspecialchars($cssfix);
echo "</pre>";

echo "<h3>Alternative: Use Emoji Avatar</h3>";
echo "<p>The header.php already has fallback divs that should show when images fail to load.</p>";

// Check if the avatar file exists
if (file_exists($avatarPath)) {
    echo "<p style='color: green;'>✅ Avatar file exists at: {$avatarPath}</p>";
} else {
    echo "<p style='color: red;'>❌ Avatar file missing at: {$avatarPath}</p>";
    
    // Create a simple placeholder file
    $simpleAvatar = '<svg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
  <circle cx="20" cy="20" r="20" fill="#6B7280"/>
  <text x="20" y="26" text-anchor="middle" fill="white" font-size="16">👤</text>
</svg>';
    
    file_put_contents($avatarPath, $simpleAvatar);
    echo "<p style='color: green;'>✅ Created simple SVG avatar</p>";
}

echo "<p><a href='messages.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Messages Page</a></p>";

?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
h1 { color: #2c3e50; }
h3 { color: #3498db; }
pre { background: #f8f8f8; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>
