<?php
/**
 * Create missing activity_logs table
 */

require_once 'config/config.php';

try {
    $db = getDBConnection();
    
    // Create activity_logs table
    $sql = "
    CREATE TABLE IF NOT EXISTS activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        action VARCHAR(100) NOT NULL,
        table_name VARCHAR(100) NULL,
        record_id INT NULL,
        old_values JSON NULL,
        new_values JSON NULL,
        ip_address VARCHAR(45) NULL,
        user_agent TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_action (action),
        INDEX idx_created_at (created_at),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )";
    
    $db->exec($sql);
    echo "✅ activity_logs table created successfully!<br>";
    
    // Test the table
    $stmt = $db->prepare("INSERT INTO activity_logs (action, ip_address) VALUES (?, ?)");
    $stmt->execute(['test', '127.0.0.1']);
    echo "✅ Test record inserted successfully!<br>";
    
    // Clean up test record
    $db->exec("DELETE FROM activity_logs WHERE action = 'test'");
    echo "✅ Test record cleaned up!<br>";
    
    echo "<br><strong>Database is now ready for login!</strong><br>";
    echo "<a href='login.php'>Go to Login Page</a> | ";
    echo "<a href='test_login_simple.php'>Test Simple Login</a>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
