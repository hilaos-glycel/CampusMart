<?php
/**
 * Get Messages API - Fixed Version
 * Retrieves messages for a specific conversation
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
    
    // Get conversation ID from query parameter
    $conversationId = isset($_GET['conversation_id']) ? (int)$_GET['conversation_id'] : 0;
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(50, max(10, (int)$_GET['limit'])) : 20;
    $offset = ($page - 1) * $limit;
    
    if (!$conversationId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Conversation ID is required']);
        exit;
    }
    
    $db = getDBConnection();
    
    // Verify user is participant in this conversation
    $verifyQuery = "
        SELECT id FROM conversations 
        WHERE id = ? 
            AND (participant_1 = ? OR participant_2 = ?)
    ";
    
    $verifyStmt = $db->prepare($verifyQuery);
    $verifyStmt->execute([$conversationId, $userId, $userId]);
    
    if (!$verifyStmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }
    
    // Get messages for this conversation
    $query = "
        SELECT 
            m.id,
            m.sender_id,
            m.message_text,
            m.message_type,
            m.media_url,
            m.media_filename,
            m.media_size,
            m.media_mime_type,
            m.thumbnail_url,
            m.is_read,
            m.created_at,
            CONCAT(u.first_name, ' ', u.last_name) as sender_name,
            u.username as sender_username
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.conversation_id = ?
        ORDER BY m.created_at ASC
        LIMIT ? OFFSET ?
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$conversationId, $limit, $offset]);
    $messages = $stmt->fetchAll();
    
    // Mark messages as read for the current user (messages sent by others)
    $markReadQuery = "
        UPDATE messages 
        SET is_read = TRUE 
        WHERE conversation_id = ? 
            AND sender_id != ? 
            AND is_read = FALSE
    ";
    
    $markReadStmt = $db->prepare($markReadQuery);
    $markReadStmt->execute([$conversationId, $userId]);
    
    // Format messages
    $formattedMessages = [];
    foreach ($messages as $message) {
        $messageData = [
            'id' => (int)$message['id'],
            'sender_id' => (int)$message['sender_id'],
            'sender_name' => $message['sender_name'],
            'sender_username' => $message['sender_username'],
            'message' => $message['message_text'],
            'message_type' => $message['message_type'],
            'is_read' => (bool)$message['is_read'],
            'created_at' => $message['created_at'],
            'is_own_message' => (int)$message['sender_id'] === $userId
        ];
        
        // Add media information if it's a media message
        if ($message['message_type'] === 'image' || $message['message_type'] === 'video') {
            $messageData['media'] = [
                'url' => $message['media_url'] ? SITE_URL . '/' . $message['media_url'] : null,
                'filename' => $message['media_filename'],
                'size' => (int)$message['media_size'],
                'mime_type' => $message['media_mime_type'],
                'thumbnail_url' => $message['thumbnail_url'] ? SITE_URL . '/' . $message['thumbnail_url'] : null
            ];
        }
        
        $formattedMessages[] = $messageData;
    }
    
    // Get total message count for pagination
    $countQuery = "SELECT COUNT(*) as total FROM messages WHERE conversation_id = ?";
    $countStmt = $db->prepare($countQuery);
    $countStmt->execute([$conversationId]);
    $totalMessages = $countStmt->fetch()['total'];
    
    echo json_encode([
        'success' => true,
        'messages' => $formattedMessages,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$totalMessages,
            'pages' => ceil($totalMessages / $limit)
        ],
        'conversation_id' => $conversationId
    ]);
    
} catch (Exception $e) {
    error_log("Get messages error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Server error occurred',
        'error' => $e->getMessage() // Include error for debugging
    ]);
}
?>
