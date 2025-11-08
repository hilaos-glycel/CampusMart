<?php
/**
 * Quick Fix Script for Admin Panel Issues
 * Automatically resolves common problems
 */

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Fix Admin Panel - CampusMart</title>
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
    <h1>🔧 Admin Panel Quick Fix</h1>";

$fixes = [];
$errors = [];

// Fix 1: Create required directories
echo "<div class='section'>
        <h2>📂 Creating Required Directories</h2>";

$directories = [
    'uploads/',
    'uploads/listings/',
    'uploads/profiles/',
    'uploads/admin/'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<div class='success'>✅ Created directory: {$dir}</div>";
            $fixes[] = "Created directory: {$dir}";
        } else {
            echo "<div class='error'>❌ Failed to create directory: {$dir}</div>";
            $errors[] = "Failed to create directory: {$dir}";
        }
    } else {
        echo "<div class='info'>ℹ️ Directory already exists: {$dir}</div>";
    }
}
echo "</div>";

// Fix 2: Database Connection and Setup
echo "<div class='section'>
        <h2>🗄️ Database Setup</h2>";

try {
    require_once 'config/config.php';
    $db = getDBConnection();
    echo "<div class='success'>✅ Database connection successful</div>";
    
    // Check and create admin_users table if missing
    $tableExists = $db->query("SHOW TABLES LIKE 'admin_users'")->fetch();
    if (!$tableExists) {
        echo "<div class='warning'>⚠️ admin_users table missing - attempting to create...</div>";
        
        $createAdminTable = "
        CREATE TABLE `admin_users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL,
            `email` varchar(100) NOT NULL,
            `password_hash` varchar(255) NOT NULL,
            `full_name` varchar(100) NOT NULL,
            `role` enum('super_admin','admin','moderator') DEFAULT 'admin',
            `is_active` tinyint(1) DEFAULT 1,
            `last_login` timestamp NULL DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `username` (`username`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        if ($db->exec($createAdminTable)) {
            echo "<div class='success'>✅ Created admin_users table</div>";
            $fixes[] = "Created admin_users table";
        } else {
            echo "<div class='error'>❌ Failed to create admin_users table</div>";
            $errors[] = "Failed to create admin_users table";
        }
    } else {
        echo "<div class='info'>ℹ️ admin_users table exists</div>";
    }
    
    // Check for default admin user
    $adminExists = $db->query("SELECT COUNT(*) as count FROM admin_users WHERE username = 'admin'")->fetch()['count'];
    if ($adminExists == 0) {
        echo "<div class='warning'>⚠️ Default admin user missing - creating...</div>";
        
        $adminPassword = 'admin123';
        $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("
            INSERT INTO admin_users (username, email, password_hash, full_name, role, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, 1, NOW())
        ");
        
        if ($stmt->execute(['admin', 'admin@campusmart.local', $hashedPassword, 'System Administrator', 'super_admin'])) {
            echo "<div class='success'>✅ Created default admin user</div>";
            echo "<div class='info'>📋 Login: admin / admin123</div>";
            $fixes[] = "Created default admin user";
        } else {
            echo "<div class='error'>❌ Failed to create default admin user</div>";
            $errors[] = "Failed to create default admin user";
        }
    } else {
        echo "<div class='info'>ℹ️ Default admin user exists</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
    $errors[] = "Database error: " . $e->getMessage();
}
echo "</div>";

// Fix 3: File Permissions
echo "<div class='section'>
        <h2>🔒 File Permissions</h2>";

$filesToCheck = [
    'config/config.php',
    'config/database.php',
    'includes/admin_auth.php'
];

foreach ($filesToCheck as $file) {
    if (file_exists($file)) {
        if (is_readable($file)) {
            echo "<div class='success'>✅ {$file} is readable</div>";
        } else {
            echo "<div class='error'>❌ {$file} is not readable</div>";
            $errors[] = "File not readable: {$file}";
        }
    } else {
        echo "<div class='error'>❌ Missing file: {$file}</div>";
        $errors[] = "Missing file: {$file}";
    }
}
echo "</div>";

// Fix 4: Clean up any file issues
echo "<div class='section'>
        <h2>🧹 File Cleanup</h2>";

// Check for any stray characters in database.php
$dbContent = file_get_contents('config/database.php');
if (strpos($dbContent, '7777') !== false) {
    echo "<div class='warning'>⚠️ Found stray characters in database.php - cleaning...</div>";
    $cleanContent = str_replace('7777', '', $dbContent);
    if (file_put_contents('config/database.php', $cleanContent)) {
        echo "<div class='success'>✅ Cleaned database.php file</div>";
        $fixes[] = "Cleaned database.php file";
    } else {
        echo "<div class='error'>❌ Failed to clean database.php file</div>";
        $errors[] = "Failed to clean database.php file";
    }
} else {
    echo "<div class='info'>ℹ️ database.php file is clean</div>";
}
echo "</div>";

// Summary
echo "<div class='section'>";
if (empty($errors)) {
    echo "<div class='success'>
            <h2>🎉 All Fixes Applied Successfully!</h2>
            <p>Your admin panel should now be working correctly.</p>";
    if (!empty($fixes)) {
        echo "<h3>Applied Fixes:</h3><ul>";
        foreach ($fixes as $fix) {
            echo "<li>" . htmlspecialchars($fix) . "</li>";
        }
        echo "</ul>";
    }
    echo "</div>";
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
echo "<a href='admin_panel_diagnostic.php' class='btn'>Run Diagnostic</a>";
echo "<a href='test_admin_panel.php' class='btn'>Run Full Test</a>";
echo "<a href='admin/login.php' class='btn'>Access Admin Panel</a>";

echo "</div>";

echo "</body></html>";
?>
