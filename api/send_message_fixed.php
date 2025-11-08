<?php
/**
 * Send Message API - Fixed Version
 * Handles sending new messages in conversations
 */

// Set content type header only if headers haven't been sent
if (!headers_sent()) {
    header('Content-Type: application/json');
}
require_once dirname(__DIR__) . '/config/config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $currentUser = getCurrentUser();
    $userId = $currentUser['id'];
    
    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        // Fallback to POST data
        $conversationId = isset($_POST['conversation_id']) ? (int)$_POST['conversation_id'] : 0;
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    } else {
        $conversationId = isset($input['conversation_id']) ? (int)$input['conversation_id'] : 0;
        $message = isset($input['message']) ? trim($input['message']) : '';
    }
    
    // Validate input
    if (!$conversationId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Conversation ID is required']);
        exit;
    }
    
    if (empty($message)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
        exit;
    }
    
    if (strlen($message) > 2000) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Message too long (max 2000 characters)']);
        exit;
    }
    
    $db = getDBConnection();
    
    // Verify user is participant in this conversation
    $verifyQuery = "
        SELECT participant_1, participant_2 FROM conversations 
        WHERE id = ? 
            AND (participant_1 = ? OR participant_2 = ?)
    ";
    
    $verifyStmt = $db->prepare($verifyQuery);
    $verifyStmt->execute([$conversationId, $userId, $userId]);
    $conversation = $verifyStmt->fetch();
    
    if (!$conversation) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }
    
    // Insert the message
    $insertQuery = "
        INSERT INTO messages (conversation_id, sender_id, message_text, created_at)
        VALUES (?, ?, ?, NOW())
    ";
    
    $insertStmt = $db->prepare($insertQuery);
    $result = $insertStmt->execute([$conversationId, $userId, $message]);
    
    if ($result) {
        $messageId = $db->lastInsertId();
        
        // Update conversation last_activity
        $updateConvQuery = "UPDATE conversations SET last_activity = NOW() WHERE id = ?";
        $updateConvStmt = $db->prepare($updateConvQuery);
        $updateConvStmt->execute([$conversationId]);
        
        // Get the inserted message with sender info
        $getMessageQuery = "
            SELECT 
                m.id,
                m.sender_id,
                m.message_text,
                m.is_read,
                m.created_at,
                CONCAT(u.first_name, ' ', u.last_name) as sender_name,
                u.username as sender_username
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.id = ?
        ";
        
        $getMessageStmt = $db->prepare($getMessageQuery);
        $getMessageStmt->execute([$messageId]);
        $newMessage = $getMessageStmt->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => [
                'id' => (int)$newMessage['id'],
                'sender_id' => (int)$newMessage['sender_id'],
                'sender_name' => $newMessage['sender_name'],
                'sender_username' => $newMessage['sender_username'],
                'message' => $newMessage['message_text'],
                'is_read' => (bool)$newMessage['is_read'],
                'created_at' => $newMessage['created_at'],
                'is_own_message' => true
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to send message']);
    }
    
} catch (Exception $e) {
    error_log("Send message error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Server error occurred',
        'error' => $e->getMessage() // Include error for debugging
    ]);
}
?>
