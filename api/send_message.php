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
        $receiverId = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';
        $messageType = isset($_POST['message_type']) ? $_POST['message_type'] : 'text';
        $mediaUrl = isset($_POST['media_url']) ? $_POST['media_url'] : null;
        $mediaFilename = isset($_POST['media_filename']) ? $_POST['media_filename'] : null;
        $mediaSize = isset($_POST['media_size']) ? (int)$_POST['media_size'] : null;
        $mediaMimeType = isset($_POST['media_mime_type']) ? $_POST['media_mime_type'] : null;
        $thumbnailUrl = isset($_POST['thumbnail_url']) ? $_POST['thumbnail_url'] : null;
    } else {
        $conversationId = isset($input['conversation_id']) ? (int)$input['conversation_id'] : 0;
        $receiverId = isset($input['receiver_id']) ? (int)$input['receiver_id'] : 0;
        $message = isset($input['message']) ? trim($input['message']) : '';
        $messageType = isset($input['message_type']) ? $input['message_type'] : 'text';
        $mediaUrl = isset($input['media_url']) ? $input['media_url'] : null;
        $mediaFilename = isset($input['media_filename']) ? $input['media_filename'] : null;
        $mediaSize = isset($input['media_size']) ? (int)$input['media_size'] : null;
        $mediaMimeType = isset($input['media_mime_type']) ? $input['media_mime_type'] : null;
        $thumbnailUrl = isset($input['thumbnail_url']) ? $input['thumbnail_url'] : null;
    }
    
    // Validate input - need either conversation_id or receiver_id
    if (!$conversationId && !$receiverId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Conversation ID or Receiver ID is required']);
        exit;
    }
    
    // Validate message content based on type
    if ($messageType === 'text' || $messageType === 'emoji') {
        if (empty($message)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
            exit;
        }
    } elseif ($messageType === 'image' || $messageType === 'video') {
        if (empty($mediaUrl)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Media URL is required for media messages']);
            exit;
        }
        // For media messages, message text is optional (can be a caption)
        if (empty($message)) {
            $message = ''; // Set empty caption
        }
    }
    
    if (strlen($message) > 2000) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Message too long (max 2000 characters)']);
        exit;
    }
    
    $db = getDBConnection();
    
    // If receiver_id is provided instead of conversation_id, find or create conversation
    if (!$conversationId && $receiverId) {
        // Check if receiver exists
        $userCheckStmt = $db->prepare("SELECT id FROM users WHERE id = ?");
        $userCheckStmt->execute([$receiverId]);
        if (!$userCheckStmt->fetch()) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Receiver not found']);
            exit;
        }
        
        // Check if conversation already exists between these users
        $findConvStmt = $db->prepare("
            SELECT id FROM conversations 
            WHERE (participant_1 = ? AND participant_2 = ?) 
               OR (participant_1 = ? AND participant_2 = ?)
        ");
        $findConvStmt->execute([$userId, $receiverId, $receiverId, $userId]);
        $existingConv = $findConvStmt->fetch();
        
        if ($existingConv) {
            $conversationId = $existingConv['id'];
        } else {
            // Create new conversation
            $createConvStmt = $db->prepare("
                INSERT INTO conversations (participant_1, participant_2, created_at, last_activity) 
                VALUES (?, ?, NOW(), NOW())
            ");
            $createConvStmt->execute([$userId, $receiverId]);
            $conversationId = $db->lastInsertId();
        }
    }
    
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
        INSERT INTO messages (conversation_id, sender_id, message_text, message_type, media_url, media_filename, media_size, media_mime_type, thumbnail_url, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ";
    
    $insertStmt = $db->prepare($insertQuery);
    $result = $insertStmt->execute([
        $conversationId, 
        $userId, 
        $message, 
        $messageType, 
        $mediaUrl, 
        $mediaFilename, 
        $mediaSize, 
        $mediaMimeType, 
        $thumbnailUrl
    ]);
    
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
