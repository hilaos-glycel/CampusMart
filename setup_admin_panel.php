<?php
require_once 'config/config.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Admin Panel Setup - CampusMart</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #c3e6cb; }
        .error { color: #dc3545; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #f5c6cb; }
        .info { color: #0c5460; background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #bee5eb; }
        .step { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }
        h1 { color: #333; text-align: center; margin-bottom: 30px; }
        h2 { color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .admin-info { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🛡️ CampusMart Admin Panel Setup</h1>";

try {
    $db = getDBConnection();
    
    echo "<div class='step'>
            <h2>📋 Step 1: Creating Settings Table</h2>";
    
    // Create settings table
    $settingsTable = "
        CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ";
    
    $db->exec($settingsTable);
    echo "<div class='success'>✅ Settings table created successfully!</div>";
    
    // Insert default settings
    $defaultSettings = [
        'site_name' => 'CampusMart',
        'site_description' => 'Student Marketplace for JH Cerilles State College',
        'contact_email' => 'admin@campusmart.com',
        'maintenance_mode' => '0',
        'max_file_size' => '10',
        'max_image_size' => '5',
        'max_video_size' => '50',
        'listings_per_page' => '20',
        'enable_registration' => '1',
        'enable_messaging' => '1',
        'enable_notifications' => '1',
        'require_approval' => '0'
    ];
    
    foreach ($defaultSettings as $key => $value) {
        $stmt = $db->prepare("
            INSERT INTO settings (setting_key, setting_value) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $stmt->execute([$key, $value]);
    }
    
    echo "<div class='success'>✅ Default settings inserted successfully!</div>";
    echo "</div>";
    
    echo "<div class='step'>
            <h2>👤 Step 2: Setting Up Admin Users</h2>";
    
    // Update existing users to have admin role
    $adminUsers = ['hilaos', 'legaspi', 'sapuay'];
    
    foreach ($adminUsers as $username) {
        $stmt = $db->prepare("UPDATE users SET role = 'admin' WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->rowCount() > 0) {
            echo "<div class='success'>✅ User '$username' promoted to admin</div>";
        } else {
            echo "<div class='info'>ℹ️ User '$username' not found or already admin</div>";
        }
    }
    
    echo "</div>";
    
    echo "<div class='step'>
            <h2>🗂️ Step 3: Checking Database Structure</h2>";
    
    // Check if role column exists in users table
    $roleCheck = $db->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch();
    
    if (!$roleCheck) {
        echo "<div class='info'>Adding 'role' column to users table...</div>";
        $db->exec("ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user'");
        echo "<div class='success'>✅ Role column added successfully!</div>";
        
        // Re-promote admin users
        foreach ($adminUsers as $username) {
            $stmt = $db->prepare("UPDATE users SET role = 'admin' WHERE username = ?");
            $stmt->execute([$username]);
        }
        echo "<div class='success'>✅ Admin users re-promoted!</div>";
    } else {
        echo "<div class='success'>✅ Role column already exists!</div>";
    }
    
    // Check if is_featured column exists in listings table
    $featuredCheck = $db->query("SHOW COLUMNS FROM listings LIKE 'is_featured'")->fetch();
    
    if (!$featuredCheck) {
        echo "<div class='info'>Adding 'is_featured' column to listings table...</div>";
        $db->exec("ALTER TABLE listings ADD COLUMN is_featured BOOLEAN DEFAULT FALSE");
        echo "<div class='success'>✅ Featured column added successfully!</div>";
    } else {
        echo "<div class='success'>✅ Featured column already exists!</div>";
    }
    
    echo "</div>";
    
    echo "<div class='step'>
            <h2>📊 Step 4: System Statistics</h2>";
    
    // Get system statistics
    $userCount = $db->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
    $adminCount = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch()['count'];
    $listingCount = $db->query("SELECT COUNT(*) as count FROM listings")->fetch()['count'];
    $messageCount = $db->query("SELECT COUNT(*) as count FROM messages")->fetch()['count'];
    
    echo "<div class='info'>
            <strong>📈 Current System Statistics:</strong><br>
            👥 Total Users: $userCount<br>
            🛡️ Admin Users: $adminCount<br>
            🛍️ Total Listings: $listingCount<br>
            💬 Total Messages: $messageCount
          </div>";
    
    echo "</div>";
    
    echo "<div class='admin-info'>
            <h2>🎉 Admin Panel Setup Complete!</h2>
            <p><strong>Your admin panel is now ready to use!</strong></p>
            
            <h3>🔐 Admin Accounts:</h3>
            <ul>
                <li><strong>Maria Hilaos:</strong> hilaos / hilaos123</li>
                <li><strong>Anna Legaspi:</strong> legaspi / legaspi123</li>
                <li><strong>John Sapuay:</strong> sapuay / sapuay123</li>
            </ul>
            
            <h3>🚀 Admin Panel Features:</h3>
            <ul>
                <li>📊 Comprehensive Dashboard with Analytics</li>
                <li>👥 Complete User Management</li>
                <li>🛍️ Listings Management & Approval</li>
                <li>🏷️ Categories Management</li>
                <li>📈 Reports & Analytics with Charts</li>
                <li>⚙️ System Settings & Configuration</li>
                <li>🛠️ Maintenance Tools</li>
            </ul>
          </div>";
    
    echo "<div style='text-align: center; margin: 30px 0;'>
            <a href='admin/index.php' class='btn btn-success' style='font-size: 18px; padding: 15px 30px;'>
                🛡️ Access Admin Panel
            </a>
            <a href='login.php' class='btn'>
                🔐 Login Page
            </a>
            <a href='index.php' class='btn'>
                🏠 Main Site
            </a>
          </div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    echo "<div class='info'>Please check your database connection and try again.</div>";
}

echo "    </div>
</body>
</html>";
?>
