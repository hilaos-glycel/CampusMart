<?php
/**
 * Fix Database Issues for Admin Panel
 * Resolves missing tables and columns
 */

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Fix Database Issues - CampusMart</title>
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
    <h1>🔧 Fix Database Issues</h1>";

$fixes = [];
$errors = [];

try {
    require_once 'config/config.php';
    $db = getDBConnection();
    echo "<div class='success'>✅ Database connection successful</div>";
    
    // Fix 1: Create system_settings table
    echo "<div class='section'>
            <h2>🗄️ Creating System Settings Table</h2>";
    
    $tableExists = $db->query("SHOW TABLES LIKE 'system_settings'")->fetch();
    if (!$tableExists) {
        echo "<div class='warning'>⚠️ system_settings table missing - creating...</div>";
        
        $createTable = "
        CREATE TABLE `system_settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `setting_key` varchar(100) NOT NULL,
            `setting_value` text,
            `description` text,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `setting_key` (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        if ($db->exec($createTable)) {
            echo "<div class='success'>✅ Created system_settings table</div>";
            $fixes[] = "Created system_settings table";
            
            // Insert default settings
            $defaultSettings = [
                ['site_name', 'CampusMart', 'Name of the website'],
                ['site_description', 'Student Marketplace for JH Cerilles State College', 'Website description'],
                ['contact_email', 'admin@campusmart.local', 'Contact email address'],
                ['maintenance_mode', '0', 'Maintenance mode (0=off, 1=on)'],
                ['max_file_size', '10', 'Maximum file upload size in MB'],
                ['max_image_size', '5', 'Maximum image upload size in MB'],
                ['max_video_size', '50', 'Maximum video upload size in MB'],
                ['listings_per_page', '20', 'Number of listings per page'],
                ['enable_registration', '1', 'Allow new user registration'],
                ['enable_messaging', '1', 'Enable messaging system'],
                ['enable_notifications', '1', 'Enable notifications'],
                ['require_approval', '0', 'Require admin approval for listings']
            ];
            
            $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
            foreach ($defaultSettings as $setting) {
                $stmt->execute($setting);
            }
            
            echo "<div class='success'>✅ Inserted default settings</div>";
            $fixes[] = "Inserted default system settings";
        } else {
            echo "<div class='error'>❌ Failed to create system_settings table</div>";
            $errors[] = "Failed to create system_settings table";
        }
    } else {
        echo "<div class='info'>ℹ️ system_settings table already exists</div>";
    }
    echo "</div>";
    
    // Fix 2: Add missing columns to categories table
    echo "<div class='section'>
            <h2>🏷️ Fixing Categories Table</h2>";
    
    // Check if is_active column exists
    $columns = $db->query("SHOW COLUMNS FROM categories LIKE 'is_active'")->fetch();
    if (!$columns) {
        echo "<div class='warning'>⚠️ is_active column missing from categories table - adding...</div>";
        
        if ($db->exec("ALTER TABLE categories ADD COLUMN is_active TINYINT(1) DEFAULT 1")) {
            echo "<div class='success'>✅ Added is_active column to categories table</div>";
            $fixes[] = "Added is_active column to categories";
        } else {
            echo "<div class='error'>❌ Failed to add is_active column</div>";
            $errors[] = "Failed to add is_active column";
        }
    } else {
        echo "<div class='info'>ℹ️ is_active column already exists in categories table</div>";
    }
    
    // Check if description column exists
    $descColumns = $db->query("SHOW COLUMNS FROM categories LIKE 'description'")->fetch();
    if (!$descColumns) {
        echo "<div class='warning'>⚠️ description column missing from categories table - adding...</div>";
        
        if ($db->exec("ALTER TABLE categories ADD COLUMN description TEXT")) {
            echo "<div class='success'>✅ Added description column to categories table</div>";
            $fixes[] = "Added description column to categories";
        } else {
            echo "<div class='error'>❌ Failed to add description column</div>";
            $errors[] = "Failed to add description column";
        }
    } else {
        echo "<div class='info'>ℹ️ description column already exists in categories table</div>";
    }
    
    echo "</div>";
    
    // Fix 3: Verify and update categories data
    echo "<div class='section'>
            <h2>📊 Verifying Categories Data</h2>";
    
    $categories = $db->query("SELECT id, name, slug, icon, is_active FROM categories")->fetchAll();
    echo "<div class='info'>📋 Current categories:</div>";
    echo "<table border='1' cellpadding='5' cellspacing='0' style='width: 100%; border-collapse: collapse;'>
            <tr style='background: #f7fafc;'>
                <th>ID</th>
                <th>Name</th>
                <th>Slug</th>
                <th>Icon</th>
                <th>Active</th>
            </tr>";
    
    foreach ($categories as $category) {
        $activeStatus = $category['is_active'] ? 'Yes' : 'No';
        echo "<tr>
                <td>{$category['id']}</td>
                <td>{$category['name']}</td>
                <td>{$category['slug']}</td>
                <td>{$category['icon']}</td>
                <td>{$activeStatus}</td>
              </tr>";
    }
    echo "</table>";
    
    echo "</div>";
    
    // Fix 4: Test the fixed queries
    echo "<div class='section'>
            <h2>🧪 Testing Fixed Queries</h2>";
    
    try {
        // Test categories query
        $categoryStats = $db->query("
            SELECT 
                COUNT(*) as total,
                COUNT(CASE WHEN is_active = 1 THEN 1 END) as active
            FROM categories
        ")->fetch();
        
        echo "<div class='success'>✅ Categories query test passed</div>";
        echo "<div class='info'>📊 Categories: {$categoryStats['total']} total, {$categoryStats['active']} active</div>";
        
        // Test system_settings query
        $settingsCount = $db->query("SELECT COUNT(*) as count FROM system_settings")->fetch()['count'];
        echo "<div class='success'>✅ System settings query test passed</div>";
        echo "<div class='info'>📊 System settings: {$settingsCount} records</div>";
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Query test failed: " . htmlspecialchars($e->getMessage()) . "</div>";
        $errors[] = "Query test failed: " . $e->getMessage();
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Critical error: " . htmlspecialchars($e->getMessage()) . "</div>";
    $errors[] = "Critical error: " . $e->getMessage();
}

// Summary
echo "<div class='section'>";
if (empty($errors)) {
    echo "<div class='success'>
            <h2>🎉 All Database Issues Fixed!</h2>
            <p>Your admin panel should now work without errors.</p>";
    if (!empty($fixes)) {
        echo "<h3>Applied Fixes:</h3><ul>";
        foreach ($fixes as $fix) {
            echo "<li>" . htmlspecialchars($fix) . "</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<div class='error'>
            <h2>❌ Some Issues Remain</h2>
            <p>The following issues could not be automatically fixed:</p>
            <ul>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul></div>";
    
    if (!empty($fixes)) {
        echo "<div class='info'>
                <h3>Successfully Applied:</h3><ul>";
        foreach ($fixes as $fix) {
            echo "<li>" . htmlspecialchars($fix) . "</li>";
        }
        echo "</ul></div>";
    }
}

echo "<h3>🚀 Next Steps</h3>";
echo "<a href='test_admin_panel.php' class='btn'>Run Admin Panel Test</a>";
echo "<a href='admin/login.php' class='btn'>Access Admin Panel</a>";
echo "<a href='admin_panel_diagnostic.php' class='btn'>Run Diagnostic</a>";

echo "</div>";

echo "</body></html>";
?>
