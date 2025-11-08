<?php
/**
 * Manage Friend Request API
 * Accept, decline, or block friend requests
 */

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
        $requestId = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
        $action = isset($_POST['action']) ? trim($_POST['action']) : '';
    } else {
        $requestId = isset($input['request_id']) ? (int)$input['request_id'] : 0;
        $action = isset($input['action']) ? trim($input['action']) : '';
    }
    
    // Validate input
    if (!$requestId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Request ID is required']);
        exit;
    }
    
    if (!in_array($action, ['accept', 'decline', 'block'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action. Must be accept, decline, or block']);
        exit;
    }
    
    $db = getDBConnection();
    
    // Get the friend request and verify user can manage it
    $requestStmt = $db->prepare("
        SELECT fr.*, 
               sender.first_name as sender_first_name, 
               sender.last_name as sender_last_name,
               sender.username as sender_username
        FROM friend_requests fr
        JOIN users sender ON fr.sender_id = sender.id
        WHERE fr.id = ? AND fr.receiver_id = ? AND fr.status = 'pending'
    ");
    $requestStmt->execute([$requestId, $userId]);
    $request = $requestStmt->fetch();
    
    if (!$request) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Friend request not found or already processed']);
        exit;
    }
    
    $senderId = $request['sender_id'];
    
    // Update the request status
    $updateStmt = $db->prepare("
        UPDATE friend_requests 
        SET status = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $updateStmt->execute([$action === 'accept' ? 'accepted' : ($action === 'decline' ? 'declined' : 'blocked'), $requestId]);
    
    $responseMessage = '';
    
    if ($action === 'accept') {
        // Create friendship
        $friendshipStmt = $db->prepare("
            INSERT IGNORE INTO friends (user1_id, user2_id) 
            VALUES (?, ?)
        ");
        $friendshipStmt->execute([$senderId, $userId]);
        
        $responseMessage = 'Friend request accepted! You are now friends with ' . $request['sender_first_name'] . '.';
        
    } elseif ($action === 'decline') {
        $responseMessage = 'Friend request declined.';
        
    } elseif ($action === 'block') {
        $responseMessage = 'User blocked. They will not be able to send you friend requests.';
    }
    
    echo json_encode([
        'success' => true,
        'message' => $responseMessage,
        'data' => [
            'action' => $action,
            'sender_name' => $request['sender_first_name'] . ' ' . $request['sender_last_name'],
            'sender_username' => $request['sender_username']
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Manage friend request error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Server error occurred',
        'error' => $e->getMessage()
    ]);
}
?>
