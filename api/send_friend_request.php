<?php
/**
 * Send Friend Request API
 * Sends a friend request to another user
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
        $receiverId = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    } else {
        $receiverId = isset($input['receiver_id']) ? (int)$input['receiver_id'] : 0;
        $message = isset($input['message']) ? trim($input['message']) : '';
    }
    
    // Validate input
    if (!$receiverId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Receiver ID is required']);
        exit;
    }
    
    if ($userId === $receiverId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cannot send friend request to yourself']);
        exit;
    }
    
    $db = getDBConnection();
    
    // Check if receiver exists
    $userCheckStmt = $db->prepare("SELECT id, first_name, last_name FROM users WHERE id = ? AND status = 'active'");
    $userCheckStmt->execute([$receiverId]);
    $receiver = $userCheckStmt->fetch();
    
    if (!$receiver) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    // Check if they are already friends
    $friendCheckStmt = $db->prepare("
        SELECT id FROM friends 
        WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)
    ");
    $friendCheckStmt->execute([$userId, $receiverId, $receiverId, $userId]);
    
    if ($friendCheckStmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'You are already friends with this user']);
        exit;
    }
    
    // Check if there's already a pending request
    $requestCheckStmt = $db->prepare("
        SELECT id, sender_id, status FROM friend_requests 
        WHERE ((sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?))
        AND status = 'pending'
    ");
    $requestCheckStmt->execute([$userId, $receiverId, $receiverId, $userId]);
    $existingRequest = $requestCheckStmt->fetch();
    
    if ($existingRequest) {
        if ($existingRequest['sender_id'] == $userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Friend request already sent']);
            exit;
        } else {
            // They sent us a request, so accept it automatically
            $acceptStmt = $db->prepare("UPDATE friend_requests SET status = 'accepted', updated_at = NOW() WHERE id = ?");
            $acceptStmt->execute([$existingRequest['id']]);
            
            // Create friendship
            $friendshipStmt = $db->prepare("INSERT INTO friends (user1_id, user2_id) VALUES (?, ?)");
            $friendshipStmt->execute([$userId, $receiverId]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Friend request accepted! You are now friends.',
                'action' => 'accepted_existing_request'
            ]);
            exit;
        }
    }
    
    // Send new friend request
    $insertStmt = $db->prepare("
        INSERT INTO friend_requests (sender_id, receiver_id, message, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    
    $result = $insertStmt->execute([$userId, $receiverId, $message]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Friend request sent successfully',
            'data' => [
                'receiver_name' => $receiver['first_name'] . ' ' . $receiver['last_name'],
                'message' => $message
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to send friend request']);
    }
    
} catch (Exception $e) {
    error_log("Send friend request error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Server error occurred',
        'error' => $e->getMessage()
    ]);
}
?>
