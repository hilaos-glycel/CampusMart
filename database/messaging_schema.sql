-- ===== CAMPUSMART MESSAGING SYSTEM SCHEMA =====

-- Create conversations table
CREATE TABLE IF NOT EXISTS conversations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    participant_1 INT NOT NULL,
    participant_2 INT NOT NULL,
    last_message_id INT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (participant_1) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (participant_2) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_conversation (LEAST(participant_1, participant_2), GREATEST(participant_1, participant_2)),
    INDEX idx_participant_1 (participant_1),
    INDEX idx_participant_2 (participant_2),
    INDEX idx_last_activity (last_activity)
);

-- Create messages table
CREATE TABLE IF NOT EXISTS messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    message_type ENUM('text', 'image', 'file', 'system') DEFAULT 'text',
    attachment_url VARCHAR(500) NULL,
    attachment_name VARCHAR(255) NULL,
    is_read BOOLEAN DEFAULT FALSE,
    is_deleted_by_sender BOOLEAN DEFAULT FALSE,
    is_deleted_by_receiver BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_sender_id (sender_id),
    INDEX idx_receiver_id (receiver_id),
    INDEX idx_created_at (created_at),
    INDEX idx_is_read (is_read)
);

-- Create message_reactions table (for future features like emoji reactions)
CREATE TABLE IF NOT EXISTS message_reactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    message_id INT NOT NULL,
    user_id INT NOT NULL,
    reaction_type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_message_reaction (message_id, user_id, reaction_type),
    INDEX idx_message_id (message_id),
    INDEX idx_user_id (user_id)
);

-- Create message_attachments table (for multiple attachments per message)
CREATE TABLE IF NOT EXISTS message_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    message_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    INDEX idx_message_id (message_id)
);

-- Create conversation_participants table (for group messaging future expansion)
CREATE TABLE IF NOT EXISTS conversation_participants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    conversation_id INT NOT NULL,
    user_id INT NOT NULL,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    left_at TIMESTAMP NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    is_muted BOOLEAN DEFAULT FALSE,
    last_read_message_id INT NULL,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_conversation_user (conversation_id, user_id),
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_user_id (user_id)
);

-- Update conversations table to reference last message
ALTER TABLE conversations 
ADD CONSTRAINT fk_conversations_last_message 
FOREIGN KEY (last_message_id) REFERENCES messages(id) ON DELETE SET NULL;

-- Insert sample conversations and messages for testing
INSERT INTO conversations (participant_1, participant_2) VALUES
(1, 2), -- Maria and John
(1, 3), -- Maria and Anna
(2, 3); -- John and Anna

-- Insert sample messages
INSERT INTO messages (conversation_id, sender_id, receiver_id, message, is_read) VALUES
-- Conversation between Maria (1) and John (2)
(1, 1, 2, 'Hi John! I saw your business textbook listing. Is it still available?', TRUE),
(1, 2, 1, 'Hello Maria! Yes, it\'s still available. Are you interested in buying it?', TRUE),
(1, 1, 2, 'Yes, I am! What\'s the condition of the book?', FALSE),

-- Conversation between Maria (1) and Anna (3)
(2, 3, 1, 'Hey Maria! I need help with my Java programming assignment. Can you tutor me?', TRUE),
(2, 1, 3, 'Hi Anna! Of course, I\'d be happy to help. When do you need the tutoring?', TRUE),
(2, 3, 1, 'This weekend would be perfect. What\'s your rate?', FALSE),

-- Conversation between John (2) and Anna (3)
(3, 2, 3, 'Anna, I heard you\'re selling a mountain bike. Can I see some photos?', TRUE),
(3, 3, 2, 'Sure John! I\'ll send you some pictures. It\'s in great condition.', FALSE);

-- Update last_message_id in conversations
UPDATE conversations SET last_message_id = 3 WHERE id = 1;
UPDATE conversations SET last_message_id = 6 WHERE id = 2;
UPDATE conversations SET last_message_id = 8 WHERE id = 3;

-- Create indexes for better performance
CREATE INDEX idx_messages_conversation_created ON messages(conversation_id, created_at);
CREATE INDEX idx_conversations_participants ON conversations(participant_1, participant_2);
CREATE INDEX idx_messages_unread ON messages(receiver_id, is_read, created_at);

-- Create view for unread message counts
CREATE VIEW user_unread_counts AS
SELECT 
    receiver_id as user_id,
    COUNT(*) as unread_count
FROM messages 
WHERE is_read = FALSE 
    AND is_deleted_by_receiver = FALSE
GROUP BY receiver_id;

-- Create view for conversation list with last message info
CREATE VIEW conversation_list AS
SELECT 
    c.id as conversation_id,
    c.participant_1,
    c.participant_2,
    c.last_activity,
    m.message as last_message,
    m.created_at as last_message_time,
    m.sender_id as last_sender_id,
    CASE 
        WHEN c.participant_1 = ? THEN c.participant_2 
        ELSE c.participant_1 
    END as other_user_id
FROM conversations c
LEFT JOIN messages m ON c.last_message_id = m.id
WHERE c.participant_1 = ? OR c.participant_2 = ?
ORDER BY c.last_activity DESC;
