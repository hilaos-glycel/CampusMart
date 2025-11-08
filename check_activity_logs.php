<?php
/**
 * Check Activity Logs Table Structure
 */

require_once 'config/config.php';

echo "<h1>🔍 Checking Activity Logs Table</h1>";

try {
    $db = getDBConnection();
    echo "<p>✅ Database connection successful</p>";
    
    // Check if activity_logs table exists
    $stmt = $db->query("SHOW TABLES LIKE 'activity_logs'");
    if (!$stmt->fetch()) {
        echo "<p style='color: red;'>❌ activity_logs table does not exist!</p>";
        echo "<p>Creating activity_logs table...</p>";
        
        $createSQL = "
            CREATE TABLE activity_logs (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT,
                action VARCHAR(50) NOT NULL,
                table_name VARCHAR(50),
                record_id INT,
                old_values JSON,
                new_values JSON,
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        ";
        
        $db->exec($createSQL);
        echo "<p style='color: green;'>✅ activity_logs table created successfully</p>";
    } else {
        echo "<p style='color: green;'>✅ activity_logs table exists</p>";
    }
    
    // Check table structure
    echo "<h3>Table Structure:</h3>";
    $stmt = $db->query("DESCRIBE activity_logs");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f8f9fa;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    $hasOldValues = false;
    $hasNewValues = false;
    
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
        
        if ($col['Field'] === 'old_values') $hasOldValues = true;
        if ($col['Field'] === 'new_values') $hasNewValues = true;
    }
    echo "</table>";
    
    if (!$hasOldValues || !$hasNewValues) {
        echo "<p style='color: red;'>❌ Missing JSON columns for login tracking</p>";
        
        if (!$hasOldValues) {
            echo "<p>Adding old_values column...</p>";
            $db->exec("ALTER TABLE activity_logs ADD COLUMN old_values JSON");
        }
        
        if (!$hasNewValues) {
            echo "<p>Adding new_values column...</p>";
            $db->exec("ALTER TABLE activity_logs ADD COLUMN new_values JSON");
        }
        
        echo "<p style='color: green;'>✅ JSON columns added</p>";
    } else {
        echo "<p style='color: green;'>✅ All required columns exist</p>";
    }
    
    // Check current records
    $stmt = $db->query("SELECT COUNT(*) as count FROM activity_logs");
    $count = $stmt->fetch()['count'];
    echo "<p><strong>Current activity logs:</strong> {$count} records</p>";
    
    if ($count > 0) {
        echo "<h3>Recent Activity:</h3>";
        $stmt = $db->query("SELECT action, user_id, created_at FROM activity_logs ORDER BY created_at DESC LIMIT 5");
        $recent = $stmt->fetchAll();
        
        echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f8f9fa;'><th>Action</th><th>User ID</th><th>Created At</th></tr>";
        
        foreach ($recent as $log) {
            echo "<tr>";
            echo "<td>{$log['action']}</td>";
            echo "<td>{$log['user_id']}</td>";
            echo "<td>{$log['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h2 style='color: green;'>🎉 Activity Logs Table Fixed!</h2>";
    echo "<p>The login system should now work properly.</p>";
    echo "<p><a href='login.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Login Now</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
h1 { color: #2c3e50; }
h2 { color: #27ae60; }
h3 { color: #3498db; }
table { width: 100%; margin: 10px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f8f9fa; font-weight: bold; }
</style>
