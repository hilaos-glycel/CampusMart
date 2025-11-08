<?php
require_once '../config/config.php';
require_once '../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $currentUser = getCurrentUser();
    $userId = $currentUser['id'];
    
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    $otherUserId = isset($input['user_id']) ? (int)$input['user_id'] : 0;
    
    if (!$otherUserId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit;
    }
    
    if ($userId === $otherUserId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cannot start conversation with yourself']);
        exit;
    }
    
    $db = new Database();
    $conn = $db->getConnection();
    
    // Verify other user exists
    $userQuery = "SELECT id, first_name, last_name, email FROM users WHERE id = :user_id";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bindParam(':user_id', $otherUserId, PDO::PARAM_INT);
    $userStmt->execute();
    
    $otherUser = $userStmt->fetch(PDO::FETCH_ASSOC);
    if (!$otherUser) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    // Check if conversation already exists
    $convQuery = "
        SELECT id FROM conversations 
        WHERE (participant_1 = :user1 AND participant_2 = :user2) 
           OR (participant_1 = :user2 AND participant_2 = :user1)
    ";
    
    $convStmt = $conn->prepare($convQuery);
    $convStmt->bindParam(':user1', $userId, PDO::PARAM_INT);
    $convStmt->bindParam(':user2', $otherUserId, PDO::PARAM_INT);
    $convStmt->execute();
    
    $conversation = $convStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($conversation) {
        $conversationId = $conversation['id'];
    } else {
        // Create new conversation
        $createConvQuery = "
            INSERT INTO conversations (participant_1, participant_2) 
            VALUES (:user1, :user2)
        ";
        
        $createConvStmt = $conn->prepare($createConvQuery);
        $createConvStmt->bindParam(':user1', $userId, PDO::PARAM_INT);
        $createConvStmt->bindParam(':user2', $otherUserId, PDO::PARAM_INT);
        $createConvStmt->execute();
        
        $conversationId = $conn->lastInsertId();
    }
    
    echo json_encode([
        'success' => true,
        'conversation' => [
            'id' => (int)$conversationId,
            'other_user' => [
                'id' => (int)$otherUser['id'],
                'name' => $otherUser['first_name'] . ' ' . $otherUser['last_name'],
                'email' => $otherUser['email']
            ]
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error in start_conversation.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?>
