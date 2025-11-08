<?php
/**
 * Create Messaging Tables
 * Simple script to create the required messaging tables
 */

require_once 'config/config.php';

echo "<h1>🔧 Creating Messaging Tables</h1>";

try {
    $db = getDBConnection();
    echo "<p>✅ Database connection successful</p>";
    
    // Create conversations table
    echo "<h3>Creating conversations table...</h3>";
    $conversationsSQL = "
        CREATE TABLE IF NOT EXISTS conversations (
            id INT PRIMARY KEY AUTO_INCREMENT,
            participant_1 INT NOT NULL,
            participant_2 INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (participant_1) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (participant_2) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_participants (participant_1, participant_2)
        )
    ";
    
    $db->exec($conversationsSQL);
    echo "<p style='color: green;'>✅ Conversations table created</p>";
    
    // Create messages table
    echo "<h3>Creating messages table...</h3>";
    $messagesSQL = "
        CREATE TABLE IF NOT EXISTS messages (
            id INT PRIMARY KEY AUTO_INCREMENT,
            conversation_id INT NOT NULL,
            sender_id INT NOT NULL,
            message_text TEXT NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
            FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ";
    
    $db->exec($messagesSQL);
    echo "<p style='color: green;'>✅ Messages table created</p>";
    
    // Create message_reactions table (for future use)
    echo "<h3>Creating message_reactions table...</h3>";
    $reactionsSQL = "
        CREATE TABLE IF NOT EXISTS message_reactions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            message_id INT NOT NULL,
            user_id INT NOT NULL,
            reaction_type VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_reaction (message_id, user_id, reaction_type)
        )
    ";
    
    $db->exec($reactionsSQL);
    echo "<p style='color: green;'>✅ Message reactions table created</p>";
    
    // Create message_attachments table (for future use)
    echo "<h3>Creating message_attachments table...</h3>";
    $attachmentsSQL = "
        CREATE TABLE IF NOT EXISTS message_attachments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            message_id INT NOT NULL,
            filename VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            file_size INT NOT NULL,
            mime_type VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE
        )
    ";
    
    $db->exec($attachmentsSQL);
    echo "<p style='color: green;'>✅ Message attachments table created</p>";
    
    // Verify tables were created
    echo "<h3>Verifying table creation...</h3>";
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $messagingTables = ['conversations', 'messages', 'message_reactions', 'message_attachments'];
    $allCreated = true;
    
    foreach ($messagingTables as $table) {
        if (in_array($table, $tables)) {
            echo "<p style='color: green;'>✅ {$table} table exists</p>";
        } else {
            echo "<p style='color: red;'>❌ {$table} table missing</p>";
            $allCreated = false;
        }
    }
    
    if ($allCreated) {
        echo "<h2 style='color: green;'>🎉 All Messaging Tables Created Successfully!</h2>";
        
        // Create some sample conversations for testing
        echo "<h3>Creating sample conversations...</h3>";
        
        // Get user IDs for sample data
        $stmt = $db->query("SELECT id, username FROM users LIMIT 3");
        $users = $stmt->fetchAll();
        
        if (count($users) >= 2) {
            // Create a sample conversation between first two users
            $stmt = $db->prepare("
                INSERT IGNORE INTO conversations (participant_1, participant_2) 
                VALUES (?, ?)
            ");
            $stmt->execute([$users[0]['id'], $users[1]['id']]);
            
            if ($stmt->rowCount() > 0) {
                $conversationId = $db->lastInsertId();
                
                // Add sample messages
                $sampleMessages = [
                    [$users[0]['id'], "Hi! I'm interested in your Math Tutoring service."],
                    [$users[1]['id'], "Hello! I'd be happy to help you with math. What specific topics do you need help with?"],
                    [$users[0]['id'], "I'm struggling with Calculus, particularly derivatives and integrals."],
                    [$users[1]['id'], "Perfect! I specialize in Calculus. When would be a good time for you to meet?"]
                ];
                
                $stmt = $db->prepare("
                    INSERT INTO messages (conversation_id, sender_id, message_text) 
                    VALUES (?, ?, ?)
                ");
                
                foreach ($sampleMessages as $msg) {
                    $stmt->execute([$conversationId, $msg[0], $msg[1]]);
                }
                
                echo "<p style='color: green;'>✅ Sample conversation created between {$users[0]['username']} and {$users[1]['username']}</p>";
            }
            
            // Create another conversation if we have a third user
            if (count($users) >= 3) {
                $stmt = $db->prepare("
                    INSERT IGNORE INTO conversations (participant_1, participant_2) 
                    VALUES (?, ?)
                ");
                $stmt->execute([$users[0]['id'], $users[2]['id']]);
                
                if ($stmt->rowCount() > 0) {
                    $conversationId2 = $db->lastInsertId();
                    
                    $stmt = $db->prepare("
                        INSERT INTO messages (conversation_id, sender_id, message_text) 
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$conversationId2, $users[2]['id'], "Hello! I saw your Web Development service. Can you help me build a website?"]);
                    $stmt->execute([$conversationId2, $users[0]['id'], "Absolutely! I'd love to help you with your website project. What kind of website are you looking to build?"]);
                    
                    echo "<p style='color: green;'>✅ Sample conversation created between {$users[0]['username']} and {$users[2]['username']}</p>";
                }
            }
        }
        
        echo "<p><a href='messages.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Messages System</a></p>";
        
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
