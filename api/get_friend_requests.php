<?php
/**
 * Get Friend Requests API
 * Retrieves pending friend requests for the current user
 */

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
    
    // Get received friend requests (pending)
    $receivedQuery = "
        SELECT 
            fr.id,
            fr.sender_id,
            fr.message,
            fr.created_at,
            u.username,
            u.first_name,
            u.last_name,
            u.email,
            u.course,
            u.year_level,
            us.is_online,
            us.last_seen
        FROM friend_requests fr
        JOIN users u ON fr.sender_id = u.id
        LEFT JOIN user_status us ON u.id = us.user_id
        WHERE fr.receiver_id = ? AND fr.status = 'pending'
        ORDER BY fr.created_at DESC
    ";
    
    $receivedStmt = $db->prepare($receivedQuery);
    $receivedStmt->execute([$userId]);
    $receivedRequests = $receivedStmt->fetchAll();
    
    // Get sent friend requests (pending)
    $sentQuery = "
        SELECT 
            fr.id,
            fr.receiver_id,
            fr.message,
            fr.created_at,
            u.username,
            u.first_name,
            u.last_name,
            u.email,
            u.course,
            u.year_level,
            us.is_online,
            us.last_seen
        FROM friend_requests fr
        JOIN users u ON fr.receiver_id = u.id
        LEFT JOIN user_status us ON u.id = us.user_id
        WHERE fr.sender_id = ? AND fr.status = 'pending'
        ORDER BY fr.created_at DESC
    ";
    
    $sentStmt = $db->prepare($sentQuery);
    $sentStmt->execute([$userId]);
    $sentRequests = $sentStmt->fetchAll();
    
    // Format received requests
    $formattedReceived = [];
    foreach ($receivedRequests as $request) {
        $formattedReceived[] = [
            'id' => (int)$request['id'],
            'sender' => [
                'id' => (int)$request['sender_id'],
                'username' => $request['username'],
                'name' => $request['first_name'] . ' ' . $request['last_name'],
                'first_name' => $request['first_name'],
                'last_name' => $request['last_name'],
                'email' => $request['email'],
                'course' => $request['course'],
                'year_level' => $request['year_level'],
                'is_online' => (bool)$request['is_online'],
                'last_seen' => $request['last_seen']
            ],
            'message' => $request['message'],
            'created_at' => $request['created_at'],
            'type' => 'received'
        ];
    }
    
    // Format sent requests
    $formattedSent = [];
    foreach ($sentRequests as $request) {
        $formattedSent[] = [
            'id' => (int)$request['id'],
            'receiver' => [
                'id' => (int)$request['receiver_id'],
                'username' => $request['username'],
                'name' => $request['first_name'] . ' ' . $request['last_name'],
                'first_name' => $request['first_name'],
                'last_name' => $request['last_name'],
                'email' => $request['email'],
                'course' => $request['course'],
                'year_level' => $request['year_level'],
                'is_online' => (bool)$request['is_online'],
                'last_seen' => $request['last_seen']
            ],
            'message' => $request['message'],
            'created_at' => $request['created_at'],
            'type' => 'sent'
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'received' => $formattedReceived,
            'sent' => $formattedSent
        ],
        'counts' => [
            'received' => count($formattedReceived),
            'sent' => count($formattedSent),
            'total' => count($formattedReceived) + count($formattedSent)
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Get friend requests error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Server error occurred',
        'error' => $e->getMessage()
    ]);
}
?>
