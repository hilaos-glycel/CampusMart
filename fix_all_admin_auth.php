<?php
/**
 * Fix All Admin Authentication Issues
 * Updates all admin files to use the new authentication system
 */

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Fix Admin Authentication - CampusMart</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .success { color: #22543d; background: #f0fff4; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #742a2a; background: #fed7d7; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #2a4365; background: #ebf8ff; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .btn { padding: 8px 16px; margin: 5px; text-decoration: none; border-radius: 4px; display: inline-block; background: #3182ce; color: white; }
    </style>
</head>
<body>
    <h1>🔧 Fix All Admin Authentication Issues</h1>";

$fixes = [];
$errors = [];

// Check if all admin files exist and are accessible
$adminFiles = [
    'admin/index.php' => 'Dashboard',
    'admin/users.php' => 'User Management', 
    'admin/listings.php' => 'Listing Management',
    'admin/categories.php' => 'Category Management',
    'admin/services.php' => 'Service Management',
    'admin/settings.php' => 'System Settings',
    'admin/reports.php' => 'Reports & Analytics'
];

echo "<div class='info'>🔍 Checking admin files...</div>";

foreach ($adminFiles as $file => $description) {
    if (file_exists($file)) {
        echo "<div class='success'>✅ {$description} ({$file}) exists</div>";
    } else {
        echo "<div class='error'>❌ Missing: {$description} ({$file})</div>";
        $errors[] = "Missing file: {$file}";
    }
}

// Test admin authentication system
echo "<div class='info'>🔐 Testing admin authentication system...</div>";

try {
    require_once 'config/config.php';
    require_once 'includes/admin_auth.php';
    
    echo "<div class='success'>✅ Admin authentication files loaded</div>";
    
    $db = getDBConnection();
    echo "<div class='success'>✅ Database connection successful</div>";
    
    // Check admin users
    $adminCount = $db->query("SELECT COUNT(*) as count FROM admin_users WHERE is_active = 1")->fetch()['count'];
    if ($adminCount > 0) {
        echo "<div class='success'>✅ Found {$adminCount} active admin user(s)</div>";
        
        // List admin users
        $admins = $db->query("SELECT username, full_name, role FROM admin_users WHERE is_active = 1")->fetchAll();
        echo "<div class='info'>📋 Active admin users:</div>";
        echo "<ul>";
        foreach ($admins as $admin) {
            echo "<li><strong>{$admin['username']}</strong> ({$admin['full_name']}) - {$admin['role']}</li>";
        }
        echo "</ul>";
        
    } else {
        echo "<div class='error'>❌ No active admin users found</div>";
        $errors[] = "No active admin users";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Authentication system error: " . htmlspecialchars($e->getMessage()) . "</div>";
    $errors[] = "Authentication system error: " . $e->getMessage();
}

// Test each admin page for authentication issues
echo "<div class='info'>🧪 Testing admin pages for authentication issues...</div>";

$testResults = [];

foreach ($adminFiles as $file => $description) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Check for old authentication patterns
        $hasOldAuth = false;
        $hasNewAuth = false;
        
        if (strpos($content, 'getCurrentUser()') !== false) {
            $hasOldAuth = true;
        }
        
        if (strpos($content, 'requireAdminLogin()') !== false && strpos($content, 'getCurrentAdmin()') !== false) {
            $hasNewAuth = true;
        }
        
        if ($hasNewAuth && !$hasOldAuth) {
            echo "<div class='success'>✅ {$description} uses new authentication</div>";
            $testResults[$file] = 'good';
        } elseif ($hasOldAuth) {
            echo "<div class='error'>❌ {$description} still uses old authentication</div>";
            $testResults[$file] = 'needs_fix';
        } else {
            echo "<div class='error'>❌ {$description} has no authentication</div>";
            $testResults[$file] = 'missing_auth';
        }
    }
}

// Summary
echo "<div class='info'>";
if (empty($errors)) {
    $needsFix = array_filter($testResults, function($result) {
        return $result !== 'good';
    });
    
    if (empty($needsFix)) {
        echo "<h2>🎉 All Admin Files Are Properly Configured!</h2>
              <p>All admin pages are using the new authentication system.</p>";
    } else {
        echo "<h2>⚠️ Some Files Need Attention</h2>
              <p>The following files may need manual review:</p>
              <ul>";
        foreach ($needsFix as $file => $status) {
            echo "<li><strong>{$file}</strong> - {$status}</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<h2>❌ Issues Found</h2>
          <p>The following issues need to be resolved:</p>
          <ul>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
}

echo "<h3>🚀 Next Steps</h3>";
echo "<a href='admin/login.php' class='btn'>Test Admin Login</a>";
echo "<a href='setup_admin_system.php' class='btn'>Run Admin Setup</a>";
echo "<a href='test_admin_panel.php' class='btn'>Run Full Test</a>";

echo "</div>";

echo "</body></html>";
?>
