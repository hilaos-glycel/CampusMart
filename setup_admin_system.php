<?php
/**
 * Admin System Setup Script for CampusMart
 * Creates admin users and initializes the admin panel
 */

require_once 'config/config.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Admin System Setup - CampusMart</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .success { color: #22543d; background: #f0fff4; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #742a2a; background: #fed7d7; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #2a4365; background: #ebf8ff; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .code { background: #f7fafc; padding: 15px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>🛡️ CampusMart Admin System Setup</h1>";

try {
    $db = getDBConnection();
    echo "<div class='success'>✅ Database connection successful</div>";
    
    // Check if admin_users table exists
    $tableCheck = $db->query("SHOW TABLES LIKE 'admin_users'")->fetch();
    if (!$tableCheck) {
        echo "<div class='error'>❌ admin_users table not found. Please run the main database setup first.</div>";
        echo "<div class='info'>Run the campusmart_schema.sql file to create all required tables.</div>";
        exit;
    }
    
    echo "<div class='success'>✅ admin_users table found</div>";
    
    // Check if default admin exists
    $adminCheck = $db->query("SELECT COUNT(*) as count FROM admin_users WHERE username = 'admin'")->fetch();
    
    if ($adminCheck['count'] == 0) {
        // Create default admin user
        $adminPassword = 'admin123';
        $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("
            INSERT INTO admin_users (username, email, password_hash, full_name, role, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, 1, NOW())
        ");
        
        $result = $stmt->execute([
            'admin',
            'admin@campusmart.local',
            $hashedPassword,
            'System Administrator',
            'super_admin'
        ]);
        
        if ($result) {
            echo "<div class='success'>✅ Default admin user created successfully</div>";
            echo "<div class='code'>
                <strong>Admin Login Credentials:</strong><br>
                Username: admin<br>
                Password: admin123<br>
                Email: admin@campusmart.local
            </div>";
        } else {
            echo "<div class='error'>❌ Failed to create default admin user</div>";
        }
    } else {
        echo "<div class='info'>ℹ️ Default admin user already exists</div>";
    }
    
    // Create additional admin users for testing
    $testAdmins = [
        [
            'username' => 'moderator',
            'email' => 'moderator@campusmart.local',
            'password' => 'mod123',
            'full_name' => 'Content Moderator',
            'role' => 'moderator'
        ],
        [
            'username' => 'admin2',
            'email' => 'admin2@campusmart.local',
            'password' => 'admin456',
            'full_name' => 'Secondary Administrator',
            'role' => 'admin'
        ]
    ];
    
    foreach ($testAdmins as $admin) {
        $existCheck = $db->prepare("SELECT COUNT(*) as count FROM admin_users WHERE username = ?");
        $existCheck->execute([$admin['username']]);
        $exists = $existCheck->fetch()['count'];
        
        if ($exists == 0) {
            $hashedPassword = password_hash($admin['password'], PASSWORD_DEFAULT);
            
            $stmt = $db->prepare("
                INSERT INTO admin_users (username, email, password_hash, full_name, role, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, 1, NOW())
            ");
            
            $result = $stmt->execute([
                $admin['username'],
                $admin['email'],
                $hashedPassword,
                $admin['full_name'],
                $admin['role']
            ]);
            
            if ($result) {
                echo "<div class='success'>✅ Created {$admin['role']}: {$admin['username']}</div>";
            }
        } else {
            echo "<div class='info'>ℹ️ {$admin['username']} already exists</div>";
        }
    }
    
    // Display all admin users
    echo "<h2>📋 Current Admin Users</h2>";
    $admins = $db->query("
        SELECT username, email, full_name, role, is_active, created_at, last_login
        FROM admin_users 
        ORDER BY created_at DESC
    ")->fetchAll();
    
    if ($admins) {
        echo "<table border='1' cellpadding='10' cellspacing='0' style='width: 100%; border-collapse: collapse;'>
                <tr style='background: #f7fafc;'>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Last Login</th>
                </tr>";
        
        foreach ($admins as $admin) {
            $status = $admin['is_active'] ? 'Active' : 'Inactive';
            $statusColor = $admin['is_active'] ? '#22543d' : '#742a2a';
            $lastLogin = $admin['last_login'] ? date('M j, Y H:i', strtotime($admin['last_login'])) : 'Never';
            
            echo "<tr>
                    <td><strong>{$admin['username']}</strong></td>
                    <td>{$admin['full_name']}</td>
                    <td>{$admin['email']}</td>
                    <td><span style='background: #e6fffa; color: #234e52; padding: 2px 8px; border-radius: 3px;'>{$admin['role']}</span></td>
                    <td><span style='color: {$statusColor};'>{$status}</span></td>
                    <td>" . date('M j, Y', strtotime($admin['created_at'])) . "</td>
                    <td>{$lastLogin}</td>
                  </tr>";
        }
        echo "</table>";
    }
    
    // Test admin authentication system
    echo "<h2>🔧 System Tests</h2>";
    
    // Test admin auth class
    require_once 'includes/admin_auth.php';
    $adminAuth = new AdminAuth();
    echo "<div class='success'>✅ AdminAuth class loaded successfully</div>";
    
    // Check required files
    $requiredFiles = [
        'admin/login.php' => 'Admin Login Page',
        'admin/index.php' => 'Admin Dashboard',
        'admin/users.php' => 'User Management',
        'admin/listings.php' => 'Listing Management',
        'admin/logout.php' => 'Admin Logout',
        'css/admin.css' => 'Admin Styles'
    ];
    
    foreach ($requiredFiles as $file => $description) {
        if (file_exists($file)) {
            echo "<div class='success'>✅ {$description} ({$file})</div>";
        } else {
            echo "<div class='error'>❌ Missing: {$description} ({$file})</div>";
        }
    }
    
    echo "<h2>🚀 Next Steps</h2>";
    echo "<div class='info'>
        <ol>
            <li><strong>Access Admin Panel:</strong> <a href='admin/login.php' target='_blank'>admin/login.php</a></li>
            <li><strong>Login with:</strong> Username: admin, Password: admin123</li>
            <li><strong>Change default passwords</strong> after first login</li>
            <li><strong>Configure system settings</strong> in the admin panel</li>
            <li><strong>Test all admin functions</strong> thoroughly</li>
        </ol>
    </div>";
    
    echo "<div class='code'>
        <strong>Test Credentials:</strong><br>
        Super Admin: admin / admin123<br>
        Admin: admin2 / admin456<br>
        Moderator: moderator / mod123
    </div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<div class='info'>Please check your database connection and ensure all required tables exist.</div>";
}

echo "</body></html>";
?>
