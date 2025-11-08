<?php
/**
 * Comprehensive Admin Panel Test Script for CampusMart
 * Tests all admin functionality and displays system status
 */

require_once 'config/config.php';
require_once 'includes/admin_auth.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Admin Panel Test - CampusMart</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
        .success { color: #22543d; background: #f0fff4; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #742a2a; background: #fed7d7; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #744210; background: #fefcbf; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #2a4365; background: #ebf8ff; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .test-section { border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .test-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f7fafc; }
        .btn { padding: 8px 16px; margin: 5px; text-decoration: none; border-radius: 4px; display: inline-block; }
        .btn-primary { background: #3182ce; color: white; }
        .btn-success { background: #38a169; color: white; }
        .btn-danger { background: #e53e3e; color: white; }
    </style>
</head>
<body>
    <h1>🛡️ CampusMart Admin Panel Test Suite</h1>";

$testResults = [];
$overallStatus = true;

try {
    // Test database connection with detailed error reporting
    echo "<div class='info'>🔄 Testing database connection...</div>";
    $db = getDBConnection();
    echo "<div class='success'>✅ Database connection successful</div>";
    
    // Test if campusmart database exists
    $dbName = $db->query("SELECT DATABASE() as db_name")->fetch()['db_name'];
    echo "<div class='info'>📊 Connected to database: <strong>{$dbName}</strong></div>";
    
    // Test 1: Database Tables
    echo "<div class='test-section'>
            <h2>📊 Database Tables Test</h2>";
    
    $requiredTables = [
        'admin_users' => 'Admin Users',
        'users' => 'Regular Users',
        'listings' => 'Product Listings',
        'categories' => 'Categories',
        'services' => 'Services',
        'messages' => 'Messages',
        'conversations' => 'Conversations',
        'activity_logs' => 'Activity Logs',
        'system_settings' => 'System Settings'
    ];
    
    foreach ($requiredTables as $table => $description) {
        try {
            $result = $db->query("SHOW TABLES LIKE '$table'")->fetch();
            if ($result) {
                echo "<div class='success'>✅ {$description} table exists</div>";
                
                // Get row count with error handling
                try {
                    $count = $db->query("SELECT COUNT(*) as count FROM `$table`")->fetch()['count'];
                    echo "<div class='info'>📈 {$count} records in {$table}</div>";
                } catch (Exception $e) {
                    echo "<div class='warning'>⚠️ Could not count records in {$table}: " . htmlspecialchars($e->getMessage()) . "</div>";
                }
            } else {
                echo "<div class='error'>❌ {$description} table missing</div>";
                $overallStatus = false;
            }
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error checking {$description} table: " . htmlspecialchars($e->getMessage()) . "</div>";
            $overallStatus = false;
        }
    }
    echo "</div>";
    
    // Test 2: Admin Authentication System
    echo "<div class='test-section'>
            <h2>🔐 Admin Authentication Test</h2>";
    
    try {
        $adminAuth = new AdminAuth();
        echo "<div class='success'>✅ AdminAuth class instantiated</div>";
        
        // Test admin users
        $admins = $db->query("SELECT * FROM admin_users WHERE is_active = 1")->fetchAll();
        if ($admins) {
            echo "<div class='success'>✅ Found " . count($admins) . " active admin users</div>";
            
            echo "<table>
                    <tr><th>Username</th><th>Role</th><th>Email</th><th>Last Login</th></tr>";
            foreach ($admins as $admin) {
                $lastLogin = $admin['last_login'] ? date('M j, Y H:i', strtotime($admin['last_login'])) : 'Never';
                echo "<tr>
                        <td>{$admin['username']}</td>
                        <td>{$admin['role']}</td>
                        <td>{$admin['email']}</td>
                        <td>{$lastLogin}</td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='error'>❌ No active admin users found</div>";
            $overallStatus = false;
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ AdminAuth error: " . htmlspecialchars($e->getMessage()) . "</div>";
        $overallStatus = false;
    }
    echo "</div>";
    
    // Test 3: Admin Panel Files
    echo "<div class='test-section'>
            <h2>📁 Admin Panel Files Test</h2>";
    
    $adminFiles = [
        'admin/login.php' => 'Login Page',
        'admin/logout.php' => 'Logout Handler',
        'admin/index.php' => 'Dashboard',
        'admin/users.php' => 'User Management',
        'admin/listings.php' => 'Listing Management',
        'admin/categories.php' => 'Category Management',
        'admin/services.php' => 'Service Management',
        'admin/settings.php' => 'System Settings',
        'admin/reports.php' => 'Reports & Analytics',
        'includes/admin_auth.php' => 'Admin Authentication',
        'css/admin.css' => 'Admin Styles'
    ];
    
    foreach ($adminFiles as $file => $description) {
        if (file_exists($file)) {
            $size = filesize($file);
            echo "<div class='success'>✅ {$description} ({$file}) - " . number_format($size) . " bytes</div>";
        } else {
            echo "<div class='error'>❌ Missing: {$description} ({$file})</div>";
            $overallStatus = false;
        }
    }
    echo "</div>";
    
    // Test 4: System Statistics
    echo "<div class='test-section'>
            <h2>📈 System Statistics</h2>
            <div class='test-grid'>";
    
    // User statistics with error handling
    try {
        $userStats = $db->query("
            SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending,
                COUNT(CASE WHEN status = 'suspended' THEN 1 END) as suspended,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_30d
            FROM `users`
        ")->fetch();
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error getting user statistics: " . htmlspecialchars($e->getMessage()) . "</div>";
        $userStats = ['total' => 0, 'active' => 0, 'pending' => 0, 'suspended' => 0, 'new_30d' => 0];
    }
    
    echo "<div>
            <h3>👥 Users</h3>
            <p><strong>Total:</strong> {$userStats['total']}</p>
            <p><strong>Active:</strong> {$userStats['active']}</p>
            <p><strong>Pending:</strong> {$userStats['pending']}</p>
            <p><strong>Suspended:</strong> {$userStats['suspended']}</p>
            <p><strong>New (30d):</strong> {$userStats['new_30d']}</p>
          </div>";
    
    // Listing statistics with error handling
    try {
        $listingStats = $db->query("
            SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active,
                COUNT(CASE WHEN status = 'sold' THEN 1 END) as sold,
                COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_30d,
                AVG(price) as avg_price
            FROM `listings`
        ")->fetch();
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error getting listing statistics: " . htmlspecialchars($e->getMessage()) . "</div>";
        $listingStats = ['total' => 0, 'active' => 0, 'sold' => 0, 'new_30d' => 0, 'avg_price' => 0];
    }
    
    echo "<div>
            <h3>🛍️ Listings</h3>
            <p><strong>Total:</strong> {$listingStats['total']}</p>
            <p><strong>Active:</strong> {$listingStats['active']}</p>
            <p><strong>Sold:</strong> {$listingStats['sold']}</p>
            <p><strong>New (30d):</strong> {$listingStats['new_30d']}</p>
            <p><strong>Avg Price:</strong> ₱" . number_format($listingStats['avg_price'], 2) . "</p>
          </div>";
    
    // Message statistics
    $messageStats = $db->query("
        SELECT 
            COUNT(*) as total,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 END) as last_24h,
            COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as last_7d
        FROM messages
    ")->fetch();
    
    echo "<div>
            <h3>💬 Messages</h3>
            <p><strong>Total:</strong> {$messageStats['total']}</p>
            <p><strong>Last 24h:</strong> {$messageStats['last_24h']}</p>
            <p><strong>Last 7d:</strong> {$messageStats['last_7d']}</p>
          </div>";
    
    // Category statistics with error handling
    try {
        // Check if is_active column exists
        $hasIsActive = $db->query("SHOW COLUMNS FROM categories LIKE 'is_active'")->fetch();
        
        if ($hasIsActive) {
            $categoryStats = $db->query("
                SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active
                FROM categories
            ")->fetch();
        } else {
            $categoryStats = $db->query("
                SELECT 
                    COUNT(*) as total,
                    COUNT(*) as active
                FROM categories
            ")->fetch();
            echo "<div class='warning'>⚠️ Categories table missing is_active column</div>";
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error getting category statistics: " . htmlspecialchars($e->getMessage()) . "</div>";
        $categoryStats = ['total' => 0, 'active' => 0];
    }
    
    echo "<div>
            <h3>🏷️ Categories</h3>
            <p><strong>Total:</strong> {$categoryStats['total']}</p>
            <p><strong>Active:</strong> {$categoryStats['active']}</p>
          </div>";
    
    echo "</div></div>";
    
    // Test 5: Recent Activity
    echo "<div class='test-section'>
            <h2>📋 Recent Activity</h2>";
    
    $recentActivity = $db->query("
        SELECT action, table_name, created_at, ip_address
        FROM activity_logs 
        ORDER BY created_at DESC 
        LIMIT 10
    ")->fetchAll();
    
    if ($recentActivity) {
        echo "<table>
                <tr><th>Action</th><th>Table</th><th>Time</th><th>IP Address</th></tr>";
        foreach ($recentActivity as $activity) {
            echo "<tr>
                    <td>{$activity['action']}</td>
                    <td>{$activity['table_name']}</td>
                    <td>" . date('M j, Y H:i:s', strtotime($activity['created_at'])) . "</td>
                    <td>{$activity['ip_address']}</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='info'>No recent activity found</div>";
    }
    echo "</div>";
    
    // Test 6: System Health
    echo "<div class='test-section'>
            <h2>🏥 System Health Check</h2>";
    
    // Check PHP version
    $phpVersion = phpversion();
    if (version_compare($phpVersion, '7.4.0', '>=')) {
        echo "<div class='success'>✅ PHP Version: {$phpVersion}</div>";
    } else {
        echo "<div class='warning'>⚠️ PHP Version: {$phpVersion} (Recommended: 7.4+)</div>";
    }
    
    // Check required extensions
    $requiredExtensions = ['pdo', 'pdo_mysql', 'gd', 'mbstring', 'json'];
    foreach ($requiredExtensions as $ext) {
        if (extension_loaded($ext)) {
            echo "<div class='success'>✅ {$ext} extension loaded</div>";
        } else {
            echo "<div class='error'>❌ {$ext} extension missing</div>";
            $overallStatus = false;
        }
    }
    
    // Check file permissions
    $writableDirs = ['uploads/', 'uploads/listings/', 'uploads/profiles/'];
    foreach ($writableDirs as $dir) {
        if (is_dir($dir) && is_writable($dir)) {
            echo "<div class='success'>✅ {$dir} is writable</div>";
        } else {
            echo "<div class='warning'>⚠️ {$dir} not writable or doesn't exist</div>";
        }
    }
    
    echo "</div>";
    
    // Final Status
    echo "<div class='test-section'>";
    if ($overallStatus) {
        echo "<div class='success'>
                <h2>🎉 All Tests Passed!</h2>
                <p>Your CampusMart admin panel is ready to use.</p>
              </div>";
    } else {
        echo "<div class='error'>
                <h2>❌ Some Tests Failed</h2>
                <p>Please address the issues above before using the admin panel.</p>
              </div>";
    }
    
    echo "<h3>🚀 Quick Actions</h3>
          <a href='admin/login.php' class='btn btn-primary'>Login to Admin Panel</a>
          <a href='setup_admin_system.php' class='btn btn-success'>Run Admin Setup</a>
          <a href='index.php' class='btn btn-success'>Back to Main Site</a>";
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Critical Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<div class='info'>Please check your database connection and configuration.</div>";
}

echo "</body></html>";
?>
