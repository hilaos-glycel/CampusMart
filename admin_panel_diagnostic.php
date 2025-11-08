<?php
/**
 * Simple Admin Panel Diagnostic Script
 * Quick check for common issues with the admin panel
 */

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Admin Panel Diagnostic - CampusMart</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .success { color: #22543d; background: #f0fff4; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #742a2a; background: #fed7d7; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #744210; background: #fefcbf; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #2a4365; background: #ebf8ff; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .section { border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .btn { padding: 8px 16px; margin: 5px; text-decoration: none; border-radius: 4px; display: inline-block; background: #3182ce; color: white; }
    </style>
</head>
<body>
    <h1>🔧 CampusMart Admin Panel Diagnostic</h1>";

$issues = [];
$allGood = true;

// Test 1: Basic PHP Configuration
echo "<div class='section'>
        <h2>🐘 PHP Configuration</h2>";

echo "<div class='info'>PHP Version: " . phpversion() . "</div>";

$requiredExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'json'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<div class='success'>✅ {$ext} extension loaded</div>";
    } else {
        echo "<div class='error'>❌ {$ext} extension missing</div>";
        $issues[] = "Missing PHP extension: {$ext}";
        $allGood = false;
    }
}
echo "</div>";

// Test 2: File System Check
echo "<div class='section'>
        <h2>📁 File System Check</h2>";

$requiredFiles = [
    'config/config.php' => 'Main Configuration',
    'config/database.php' => 'Database Configuration',
    'includes/admin_auth.php' => 'Admin Authentication',
    'admin/login.php' => 'Admin Login Page',
    'admin/index.php' => 'Admin Dashboard'
];

foreach ($requiredFiles as $file => $description) {
    if (file_exists($file)) {
        echo "<div class='success'>✅ {$description} exists</div>";
    } else {
        echo "<div class='error'>❌ Missing: {$description} ({$file})</div>";
        $issues[] = "Missing file: {$file}";
        $allGood = false;
    }
}
echo "</div>";

// Test 3: Database Connection
echo "<div class='section'>
        <h2>🗄️ Database Connection</h2>";

try {
    require_once 'config/config.php';
    echo "<div class='success'>✅ Config file loaded</div>";
    
    $db = getDBConnection();
    echo "<div class='success'>✅ Database connection successful</div>";
    
    // Check database name
    $dbName = $db->query("SELECT DATABASE() as db_name")->fetch()['db_name'];
    echo "<div class='info'>Connected to database: <strong>{$dbName}</strong></div>";
    
    // Check critical tables
    $criticalTables = ['admin_users', 'users', 'listings', 'categories'];
    foreach ($criticalTables as $table) {
        try {
            $result = $db->query("SHOW TABLES LIKE '$table'")->fetch();
            if ($result) {
                echo "<div class='success'>✅ Table '{$table}' exists</div>";
            } else {
                echo "<div class='error'>❌ Table '{$table}' missing</div>";
                $issues[] = "Missing database table: {$table}";
                $allGood = false;
            }
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error checking table '{$table}': " . htmlspecialchars($e->getMessage()) . "</div>";
            $issues[] = "Database error checking table {$table}";
            $allGood = false;
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</div>";
    $issues[] = "Database connection failed: " . $e->getMessage();
    $allGood = false;
}
echo "</div>";

// Test 4: Admin Authentication
echo "<div class='section'>
        <h2>🔐 Admin Authentication</h2>";

try {
    require_once 'includes/admin_auth.php';
    echo "<div class='success'>✅ AdminAuth class loaded</div>";
    
    $adminAuth = new AdminAuth();
    echo "<div class='success'>✅ AdminAuth instantiated</div>";
    
    // Check for admin users
    if (isset($db)) {
        $adminCount = $db->query("SELECT COUNT(*) as count FROM admin_users WHERE is_active = 1")->fetch()['count'];
        if ($adminCount > 0) {
            echo "<div class='success'>✅ Found {$adminCount} active admin user(s)</div>";
        } else {
            echo "<div class='warning'>⚠️ No active admin users found</div>";
            $issues[] = "No active admin users - run setup_admin_system.php";
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Admin authentication error: " . htmlspecialchars($e->getMessage()) . "</div>";
    $issues[] = "Admin authentication error: " . $e->getMessage();
    $allGood = false;
}
echo "</div>";

// Test 5: Directory Permissions
echo "<div class='section'>
        <h2>📂 Directory Permissions</h2>";

$directories = ['uploads/', 'uploads/listings/', 'uploads/profiles/'];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        echo "<div class='warning'>⚠️ Directory '{$dir}' doesn't exist - will be created when needed</div>";
    } elseif (is_writable($dir)) {
        echo "<div class='success'>✅ Directory '{$dir}' is writable</div>";
    } else {
        echo "<div class='error'>❌ Directory '{$dir}' is not writable</div>";
        $issues[] = "Directory not writable: {$dir}";
    }
}
echo "</div>";

// Summary
echo "<div class='section'>";
if ($allGood && empty($issues)) {
    echo "<div class='success'>
            <h2>🎉 All Checks Passed!</h2>
            <p>Your admin panel should be working correctly.</p>
          </div>";
} else {
    echo "<div class='error'>
            <h2>❌ Issues Found</h2>
            <p>The following issues need to be resolved:</p>
            <ul>";
    foreach ($issues as $issue) {
        echo "<li>" . htmlspecialchars($issue) . "</li>";
    }
    echo "</ul></div>";
}

echo "<h3>🚀 Next Steps</h3>";
if (empty($issues)) {
    echo "<a href='admin/login.php' class='btn'>Access Admin Panel</a>";
} else {
    echo "<a href='setup_admin_system.php' class='btn'>Run Admin Setup</a>";
}
echo "<a href='test_admin_panel.php' class='btn'>Run Full Test</a>";
echo "<a href='index.php' class='btn'>Back to Main Site</a>";

echo "</div>";

echo "</body></html>";
?>
