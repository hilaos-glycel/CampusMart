<?php
/**
 * Quick test for admin settings page
 */

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test Admin Settings - CampusMart</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .success { color: #22543d; background: #f0fff4; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #742a2a; background: #fed7d7; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #2a4365; background: #ebf8ff; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .btn { padding: 8px 16px; margin: 5px; text-decoration: none; border-radius: 4px; display: inline-block; background: #3182ce; color: white; }
    </style>
</head>
<body>
    <h1>🔧 Admin Settings Test</h1>";

try {
    require_once 'config/config.php';
    require_once 'includes/admin_auth.php';
    
    echo "<div class='success'>✅ Config and AdminAuth loaded successfully</div>";
    
    $db = getDBConnection();
    echo "<div class='success'>✅ Database connection successful</div>";
    
    // Test the settings table function
    function getSettingsTableName($db) {
        $tableExists = $db->query("SHOW TABLES LIKE 'settings'")->fetch();
        if ($tableExists) {
            return 'settings';
        }
        
        $systemSettingsExists = $db->query("SHOW TABLES LIKE 'system_settings'")->fetch();
        if ($systemSettingsExists) {
            return 'system_settings';
        }
        
        // Create settings table if it doesn't exist
        $db->exec("
            CREATE TABLE `settings` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `setting_key` varchar(100) NOT NULL,
                `setting_value` text,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `setting_key` (`setting_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        
        return 'settings';
    }
    
    $settingsTable = getSettingsTableName($db);
    echo "<div class='info'>📊 Using settings table: <strong>{$settingsTable}</strong></div>";
    
    // Test loading settings
    $settingsData = [];
    $settingsQuery = $db->query("SELECT setting_key, setting_value FROM `{$settingsTable}`");
    while ($row = $settingsQuery->fetch()) {
        $settingsData[$row['setting_key']] = $row['setting_value'];
    }
    
    // Default values
    $settings = array_merge([
        'site_name' => 'CampusMart',
        'site_description' => 'Student Marketplace for JH Cerilles State College',
        'contact_email' => 'admin@campusmart.com',
        'maintenance_mode' => 0,
        'max_file_size' => 10,
        'max_image_size' => 5,
        'max_video_size' => 50,
        'listings_per_page' => 20,
        'enable_registration' => 1,
        'enable_messaging' => 1,
        'enable_notifications' => 1,
        'require_approval' => 0
    ], $settingsData);
    
    echo "<div class='success'>✅ Settings loaded successfully</div>";
    echo "<div class='info'>📋 Current settings:</div>";
    echo "<ul>";
    foreach ($settings as $key => $value) {
        echo "<li><strong>{$key}:</strong> " . htmlspecialchars($value) . "</li>";
    }
    echo "</ul>";
    
    // Test admin user check
    $adminCount = $db->query("SELECT COUNT(*) as count FROM admin_users WHERE is_active = 1")->fetch()['count'];
    if ($adminCount > 0) {
        echo "<div class='success'>✅ Found {$adminCount} active admin user(s)</div>";
    } else {
        echo "<div class='error'>❌ No active admin users found</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "<h3>🚀 Next Steps</h3>";
echo "<a href='admin/settings.php' class='btn'>Test Admin Settings Page</a>";
echo "<a href='admin/login.php' class='btn'>Admin Login</a>";
echo "<a href='fix_admin_panel.php' class='btn'>Run Fix Script</a>";

echo "</body></html>";
?>
