<?php
/**
 * Get Conversations API - Fixed Version
 * Retrieves user's conversations with last message info
 */

// Set content type header only if headers haven't been sent
if (!headers_sent()) {
    header('Content-Type: application/json');
}
require_once dirname(__DIR__) . '/config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $currentUser = getCurrentUser();
    $userId = $currentUser['id'];
    
    $db = getDBConnection();
    
    // Get conversations where user is a participant
    $query = "
        SELECT 
            c.id as conversation_id,
            c.participant_1,
            c.participant_2,
            c.last_activity,
            CASE 
                WHEN c.participant_1 = ? THEN c.participant_2 
                ELSE c.participant_1 
            END as other_user_id
        FROM conversations c
        WHERE c.participant_1 = ? OR c.participant_2 = ?
        ORDER BY c.last_activity DESC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$userId, $userId, $userId]);
    $conversations = $stmt->fetchAll();
    
    // Format the response with additional user info and last message
    $formattedConversations = [];
    
    foreach ($conversations as $conv) {
        $otherUserId = $conv['other_user_id'];
        
        // Get other user info
        $userStmt = $db->prepare("SELECT first_name, last_name, username, email FROM users WHERE id = ?");
        $userStmt->execute([$otherUserId]);
        $otherUser = $userStmt->fetch();
        
        // Get last message
        $msgStmt = $db->prepare("
            SELECT message_text, sender_id, created_at, is_read 
            FROM messages 
            WHERE conversation_id = ? 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $msgStmt->execute([$conv['conversation_id']]);
        $lastMessage = $msgStmt->fetch();
        
        // Count unread messages
        $unreadStmt = $db->prepare("
            SELECT COUNT(*) as unread_count 
            FROM messages 
            WHERE conversation_id = ? 
                AND sender_id != ? 
                AND is_read = FALSE
        ");
        $unreadStmt->execute([$conv['conversation_id'], $userId]);
        $unreadCount = $unreadStmt->fetch()['unread_count'];
        
        $formattedConversations[] = [
            'id' => (int)$conv['conversation_id'],
            'other_user' => [
                'id' => (int)$otherUserId,
                'name' => $otherUser ? $otherUser['first_name'] . ' ' . $otherUser['last_name'] : 'Unknown User',
                'username' => $otherUser ? $otherUser['username'] : 'unknown',
                'email' => $otherUser ? $otherUser['email'] : ''
            ],
            'last_message' => $lastMessage ? [
                'text' => $lastMessage['message_text'],
                'sender_id' => (int)$lastMessage['sender_id'],
                'created_at' => $lastMessage['created_at'],
                'is_read' => (bool)$lastMessage['is_read']
            ] : null,
            'unread_count' => (int)$unreadCount,
            'last_activity' => $conv['last_activity']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'conversations' => $formattedConversations,
        'total' => count($formattedConversations)
    ]);
    
} catch (Exception $e) {
    error_log("Get conversations error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Server error occurred',
        'error' => $e->getMessage() // Include error for debugging
    ]);
}
?>
