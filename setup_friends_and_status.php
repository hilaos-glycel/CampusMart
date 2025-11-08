<?php
/**
 * Setup Friends and Online Status System
 * Creates tables for user status tracking and friend requests
 */

require_once 'config/config.php';

echo "<h1>🚀 Setting Up Friends & Online Status System</h1>";

try {
    $db = getDBConnection();
    echo "<p>✅ Database connection successful</p>";
    
    // Create user_status table for online/offline tracking
    echo "<h3>Creating user_status table...</h3>";
    $userStatusSQL = "
        CREATE TABLE IF NOT EXISTS user_status (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            is_online BOOLEAN DEFAULT FALSE,
            last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            status_message VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_status (user_id)
        )
    ";
    
    $db->exec($userStatusSQL);
    echo "<p style='color: green;'>✅ User status table created</p>";
    
    // Create friend_requests table
    echo "<h3>Creating friend_requests table...</h3>";
    $friendRequestsSQL = "
        CREATE TABLE IF NOT EXISTS friend_requests (
            id INT PRIMARY KEY AUTO_INCREMENT,
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            status ENUM('pending', 'accepted', 'declined', 'blocked') DEFAULT 'pending',
            message TEXT DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_friend_request (sender_id, receiver_id)
        )
    ";
    
    $db->exec($friendRequestsSQL);
    echo "<p style='color: green;'>✅ Friend requests table created</p>";
    
    // Create friends table (for accepted friendships)
    echo "<h3>Creating friends table...</h3>";
    $friendsSQL = "
        CREATE TABLE IF NOT EXISTS friends (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user1_id INT NOT NULL,
            user2_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user1_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (user2_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_friendship (user1_id, user2_id)
        )
    ";
    
    $db->exec($friendsSQL);
    echo "<p style='color: green;'>✅ Friends table created</p>";
    
    // Initialize user status for existing users
    echo "<h3>Initializing user status for existing users...</h3>";
    $initStatusSQL = "
        INSERT IGNORE INTO user_status (user_id, is_online, last_seen)
        SELECT id, FALSE, NOW() FROM users
    ";
    
    $db->exec($initStatusSQL);
    echo "<p style='color: green;'>✅ User status initialized</p>";
    
    // Verify tables were created
    echo "<h3>Verifying table creation...</h3>";
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredTables = ['user_status', 'friend_requests', 'friends'];
    $allCreated = true;
    
    foreach ($requiredTables as $table) {
        if (in_array($table, $tables)) {
            echo "<p style='color: green;'>✅ {$table} table exists</p>";
        } else {
            echo "<p style='color: red;'>❌ {$table} table missing</p>";
            $allCreated = false;
        }
    }
    
    if ($allCreated) {
        echo "<h2 style='color: green;'>🎉 All Tables Created Successfully!</h2>";
        
        // Create some sample friend requests for testing
        echo "<h3>Creating sample friend requests...</h3>";
        
        // Get user IDs for sample data
        $stmt = $db->query("SELECT id, username FROM users LIMIT 3");
        $users = $stmt->fetchAll();
        
        if (count($users) >= 2) {
            // Create sample friend request
            $stmt = $db->prepare("
                INSERT IGNORE INTO friend_requests (sender_id, receiver_id, message) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$users[0]['id'], $users[1]['id'], "Hi! I'd like to connect with you on CampusMart."]);
            
            if (count($users) >= 3) {
                $stmt->execute([$users[2]['id'], $users[0]['id'], "Let's be friends!"]);
            }
            
            echo "<p style='color: green;'>✅ Sample friend requests created</p>";
        }
        
        echo "<p><a href='messages.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Enhanced Messages System</a></p>";
        
    } else {
        echo "<h2 style='color: red;'>❌ Some tables failed to create</h2>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
h1 { color: #2c3e50; }
h2 { color: #27ae60; }
h3 { color: #3498db; }
a { color: #007bff; text-decoration: none; }
a:hover { text-decoration: underline; }
</style>
