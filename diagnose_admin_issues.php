<?php
/**
 * Quick diagnostic script to identify admin panel issues
 */

require_once 'config/config.php';

echo "<h2>🔍 CampusMart Admin Panel Diagnostic</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
    .success { color: #22543d; background: #f0fff4; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: #742a2a; background: #fed7d7; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: #744210; background: #fefcbf; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: #2a4365; background: #ebf8ff; padding: 10px; border-radius: 5px; margin: 10px 0; }
</style>";

$issues = [];
$fixes = [];

// Test 1: Database Connection
echo "<h3>1. Database Connection Test</h3>";
try {
    $db = getDBConnection();
    echo "<div class='success'>✅ Database connection successful</div>";
    
    $dbName = $db->query("SELECT DATABASE() as db_name")->fetch()['db_name'];
    echo "<div class='info'>📊 Connected to database: <strong>{$dbName}</strong></div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</div>";
    $issues[] = "Database connection failed";
    $fixes[] = "Check XAMPP MySQL service is running and database 'campusmart' exists";
}

// Test 2: Critical Tables
echo "<h3>2. Critical Tables Check</h3>";
$criticalTables = ['admin_users', 'users', 'listings', 'categories'];

foreach ($criticalTables as $table) {
    try {
        $result = $db->query("SHOW TABLES LIKE '$table'")->fetch();
        if ($result) {
            echo "<div class='success'>✅ Table '$table' exists</div>";
            
            // Check if table has data
            $count = $db->query("SELECT COUNT(*) as count FROM `$table`")->fetch()['count'];
            if ($count > 0) {
                echo "<div class='info'>📈 {$count} records in {$table}</div>";
            } else {
                echo "<div class='warning'>⚠️ Table '{$table}' is empty</div>";
                if ($table === 'admin_users') {
                    $issues[] = "No admin users found";
                    $fixes[] = "Run setup script to create default admin user";
                }
            }
        } else {
            echo "<div class='error'>❌ Table '$table' missing</div>";
            $issues[] = "Missing table: $table";
            $fixes[] = "Run database setup script to create missing tables";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error checking table '$table': " . htmlspecialchars($e->getMessage()) . "</div>";
        $issues[] = "Error accessing table: $table";
    }
}

// Test 3: Admin Users
echo "<h3>3. Admin Users Check</h3>";
try {
    $admins = $db->query("SELECT * FROM admin_users WHERE is_active = 1")->fetchAll();
    if ($admins) {
        echo "<div class='success'>✅ Found " . count($admins) . " active admin users</div>";
        foreach ($admins as $admin) {
            echo "<div class='info'>👤 Admin: {$admin['username']} ({$admin['role']})</div>";
        }
    } else {
        echo "<div class='error'>❌ No active admin users found</div>";
        $issues[] = "No active admin users";
        $fixes[] = "Create admin user account";
    }
} catch (Exception $e) {
    echo "<div class='error'>❌ Error checking admin users: " . htmlspecialchars($e->getMessage()) . "</div>";
    $issues[] = "Cannot access admin_users table";
}

// Test 4: File Permissions
echo "<h3>4. File Permissions Check</h3>";
$uploadDirs = ['uploads', 'uploads/listings', 'uploads/profiles'];
foreach ($uploadDirs as $dir) {
    if (!is_dir($dir)) {
        echo "<div class='warning'>⚠️ Directory '$dir' doesn't exist</div>";
        $issues[] = "Missing upload directory: $dir";
        $fixes[] = "Create directory: $dir";
    } elseif (!is_writable($dir)) {
        echo "<div class='error'>❌ Directory '$dir' not writable</div>";
        $issues[] = "Directory not writable: $dir";
        $fixes[] = "Set write permissions for: $dir";
    } else {
        echo "<div class='success'>✅ Directory '$dir' exists and writable</div>";
    }
}

// Test 5: Required Files
echo "<h3>5. Required Files Check</h3>";
$requiredFiles = [
    'admin/login.php',
    'admin/index.php', 
    'includes/admin_auth.php',
    'css/admin.css'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "<div class='success'>✅ File '$file' exists</div>";
    } else {
        echo "<div class='error'>❌ Missing file: '$file'</div>";
        $issues[] = "Missing file: $file";
        $fixes[] = "Restore missing file: $file";
    }
}

// Summary
echo "<h3>📋 Summary</h3>";
if (empty($issues)) {
    echo "<div class='success'>
            <h4>🎉 No Issues Found!</h4>
            <p>Your admin panel should be working correctly.</p>
            <p><a href='admin/login.php'>Go to Admin Login</a></p>
          </div>";
} else {
    echo "<div class='error'>
            <h4>❌ Issues Found (" . count($issues) . ")</h4>
            <ul>";
    foreach ($issues as $issue) {
        echo "<li>$issue</li>";
    }
    echo "</ul></div>";
    
    echo "<div class='info'>
            <h4>🔧 Recommended Fixes</h4>
            <ol>";
    foreach ($fixes as $fix) {
        echo "<li>$fix</li>";
    }
    echo "</ol></div>";
    
    echo "<div class='warning'>
            <h4>🚀 Quick Fix Options</h4>
            <p><a href='setup_admin_system.php'>Run Admin Setup Script</a></p>
            <p><a href='campusmart_schema.sql'>Download Database Schema</a></p>
          </div>";
}

echo "<hr><p><a href='test_admin_panel.php'>Run Full Test Suite</a> | <a href='index.php'>Back to Main Site</a></p>";
?>
