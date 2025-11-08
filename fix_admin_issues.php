<?php
/**
 * Comprehensive Admin Panel Fix Script for CampusMart
 * Addresses common issues that cause test failures
 */

require_once 'config/config.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Fix Admin Panel Issues - CampusMart</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .success { color: #22543d; background: #f0fff4; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #742a2a; background: #fed7d7; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #744210; background: #fefcbf; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #2a4365; background: #ebf8ff; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .btn { padding: 10px 20px; margin: 10px 5px; text-decoration: none; border-radius: 5px; display: inline-block; }
        .btn-primary { background: #3182ce; color: white; }
        .btn-success { background: #38a169; color: white; }
    </style>
</head>
<body>
    <h1>🔧 CampusMart Admin Panel Fix</h1>";

$fixesApplied = [];
$errors = [];

try {
    $db = getDBConnection();
    echo "<div class='success'>✅ Database connection successful</div>";
    
    // Fix 1: Create missing upload directories
    echo "<h3>1. Creating Upload Directories</h3>";
    $uploadDirs = ['uploads', 'uploads/listings', 'uploads/profiles', 'uploads/services'];
    
    foreach ($uploadDirs as $dir) {
        if (!is_dir($dir)) {
            if (mkdir($dir, 0755, true)) {
                echo "<div class='success'>✅ Created directory: $dir</div>";
                $fixesApplied[] = "Created directory: $dir";
            } else {
                echo "<div class='error'>❌ Failed to create directory: $dir</div>";
                $errors[] = "Failed to create directory: $dir";
            }
        } else {
            echo "<div class='info'>📁 Directory already exists: $dir</div>";
        }
        
        // Set permissions if directory exists
        if (is_dir($dir)) {
            chmod($dir, 0755);
            echo "<div class='info'>🔒 Set permissions for: $dir</div>";
        }
    }
    
    // Fix 2: Ensure admin_users table exists and has default admin
    echo "<h3>2. Checking Admin Users</h3>";
    
    // Check if admin_users table exists
    $tableCheck = $db->query("SHOW TABLES LIKE 'admin_users'")->fetch();
    if (!$tableCheck) {
        echo "<div class='warning'>⚠️ admin_users table not found. Creating it...</div>";
        
        // Create admin_users table
        $createAdminTable = "
        CREATE TABLE `admin_users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL,
            `email` varchar(100) NOT NULL,
            `password_hash` varchar(255) NOT NULL,
            `full_name` varchar(100) NOT NULL,
            `role` enum('super_admin','admin','moderator') NOT NULL DEFAULT 'admin',
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `last_login` datetime DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `username` (`username`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        if ($db->exec($createAdminTable)) {
            echo "<div class='success'>✅ Created admin_users table</div>";
            $fixesApplied[] = "Created admin_users table";
        } else {
            echo "<div class='error'>❌ Failed to create admin_users table</div>";
            $errors[] = "Failed to create admin_users table";
        }
    } else {
        echo "<div class='success'>✅ admin_users table exists</div>";
    }
    
    // Check for default admin user
    $adminCheck = $db->query("SELECT COUNT(*) as count FROM admin_users WHERE username = 'admin'")->fetch();
    
    if ($adminCheck['count'] == 0) {
        echo "<div class='warning'>⚠️ No default admin user found. Creating one...</div>";
        
        $adminPassword = 'admin123';
        $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("
            INSERT INTO admin_users (username, email, password_hash, full_name, role, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, 1, NOW())
        ");
        
        if ($stmt->execute(['admin', 'admin@campusmart.local', $hashedPassword, 'System Administrator', 'super_admin'])) {
            echo "<div class='success'>✅ Created default admin user</div>";
            echo "<div class='info'>
                    <strong>Default Admin Credentials:</strong><br>
                    Username: admin<br>
                    Password: admin123<br>
                    <em>Please change this password after first login!</em>
                  </div>";
            $fixesApplied[] = "Created default admin user (admin/admin123)";
        } else {
            echo "<div class='error'>❌ Failed to create default admin user</div>";
            $errors[] = "Failed to create default admin user";
        }
    } else {
        echo "<div class='success'>✅ Default admin user exists</div>";
    }
    
    // Fix 3: Check and create missing tables
    echo "<h3>3. Checking Required Tables</h3>";
    
    $requiredTables = [
        'users' => "CREATE TABLE `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL,
            `email` varchar(100) NOT NULL,
            `password_hash` varchar(255) NOT NULL,
            `student_id` varchar(20) NOT NULL,
            `first_name` varchar(50) NOT NULL,
            `last_name` varchar(50) NOT NULL,
            `year_level` varchar(20) NOT NULL,
            `course` varchar(100) DEFAULT NULL,
            `phone` varchar(20) DEFAULT NULL,
            `bio` text DEFAULT NULL,
            `profile_picture` varchar(255) DEFAULT NULL,
            `status` enum('active','pending','suspended') NOT NULL DEFAULT 'pending',
            `email_verified` tinyint(1) NOT NULL DEFAULT 0,
            `last_login` datetime DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `username` (`username`),
            UNIQUE KEY `email` (`email`),
            UNIQUE KEY `student_id` (`student_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        'categories' => "CREATE TABLE `categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `slug` varchar(100) NOT NULL,
            `description` text DEFAULT NULL,
            `icon` varchar(50) DEFAULT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `slug` (`slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        'activity_logs' => "CREATE TABLE `activity_logs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) DEFAULT NULL,
            `action` varchar(100) NOT NULL,
            `table_name` varchar(50) DEFAULT NULL,
            `record_id` int(11) DEFAULT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` text DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`),
            KEY `action` (`action`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    ];
    
    foreach ($requiredTables as $tableName => $createSQL) {
        $tableExists = $db->query("SHOW TABLES LIKE '$tableName'")->fetch();
        if (!$tableExists) {
            echo "<div class='warning'>⚠️ Table '$tableName' missing. Creating...</div>";
            if ($db->exec($createSQL)) {
                echo "<div class='success'>✅ Created table: $tableName</div>";
                $fixesApplied[] = "Created table: $tableName";
            } else {
                echo "<div class='error'>❌ Failed to create table: $tableName</div>";
                $errors[] = "Failed to create table: $tableName";
            }
        } else {
            echo "<div class='info'>📊 Table '$tableName' exists</div>";
        }
    }
    
    // Fix 4: Add default categories if categories table is empty
    echo "<h3>4. Checking Categories</h3>";
    
    $categoryCount = $db->query("SELECT COUNT(*) as count FROM categories")->fetch()['count'];
    if ($categoryCount == 0) {
        echo "<div class='warning'>⚠️ No categories found. Adding default categories...</div>";
        
        $defaultCategories = [
            ['School Supplies', 'school-supplies', 'Books, notebooks, pens, and other school materials', 'fas fa-book'],
            ['Electronics', 'electronics', 'Laptops, phones, gadgets, and electronic devices', 'fas fa-laptop'],
            ['Clothing', 'clothing', 'Shirts, uniforms, shoes, and fashion items', 'fas fa-tshirt'],
            ['Furniture', 'furniture', 'Desks, chairs, beds, and room furniture', 'fas fa-couch'],
            ['Books', 'books', 'Textbooks, novels, reference materials', 'fas fa-book-open'],
            ['Sports & Recreation', 'sports', 'Sports equipment, games, and recreational items', 'fas fa-football-ball'],
            ['Other', 'other', 'Miscellaneous items and general goods', 'fas fa-box']
        ];
        
        $stmt = $db->prepare("INSERT INTO categories (name, slug, description, icon) VALUES (?, ?, ?, ?)");
        
        foreach ($defaultCategories as $category) {
            if ($stmt->execute($category)) {
                echo "<div class='success'>✅ Added category: {$category[0]}</div>";
            }
        }
        
        $fixesApplied[] = "Added default categories";
    } else {
        echo "<div class='success'>✅ Categories exist ({$categoryCount} found)</div>";
    }
    
    // Fix 5: Create .htaccess for uploads security
    echo "<h3>5. Securing Upload Directories</h3>";
    
    $htaccessContent = "# Prevent direct access to uploaded files
Options -Indexes
<Files *.php>
    Deny from all
</Files>";
    
    foreach (['uploads', 'uploads/listings', 'uploads/profiles'] as $dir) {
        if (is_dir($dir)) {
            $htaccessFile = $dir . '/.htaccess';
            if (!file_exists($htaccessFile)) {
                if (file_put_contents($htaccessFile, $htaccessContent)) {
                    echo "<div class='success'>✅ Created security file: $htaccessFile</div>";
                    $fixesApplied[] = "Created security file: $htaccessFile";
                }
            } else {
                echo "<div class='info'>🔒 Security file exists: $htaccessFile</div>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Critical Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    $errors[] = "Critical error: " . $e->getMessage();
}

// Summary
echo "<h3>📋 Fix Summary</h3>";

if (!empty($fixesApplied)) {
    echo "<div class='success'>
            <h4>✅ Fixes Applied (" . count($fixesApplied) . ")</h4>
            <ul>";
    foreach ($fixesApplied as $fix) {
        echo "<li>$fix</li>";
    }
    echo "</ul></div>";
}

if (!empty($errors)) {
    echo "<div class='error'>
            <h4>❌ Errors Encountered (" . count($errors) . ")</h4>
            <ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul></div>";
}

if (empty($errors)) {
    echo "<div class='success'>
            <h4>🎉 Admin Panel Should Now Be Working!</h4>
            <p>All critical issues have been resolved.</p>
          </div>";
} else {
    echo "<div class='warning'>
            <h4>⚠️ Some Issues Remain</h4>
            <p>Please check the errors above and resolve them manually.</p>
          </div>";
}

echo "<h3>🚀 Next Steps</h3>
      <a href='test_admin_panel.php' class='btn btn-primary'>Run Test Suite</a>
      <a href='admin/login.php' class='btn btn-success'>Login to Admin Panel</a>
      <a href='diagnose_admin_issues.php' class='btn btn-primary'>Run Diagnostic</a>";

echo "</body></html>";
?>
