<?php
/**
 * Update User Status API
 * Updates user online/offline status and last seen time
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
    
    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $isOnline = isset($_POST['is_online']) ? (bool)$_POST['is_online'] : true;
        $statusMessage = isset($_POST['status_message']) ? trim($_POST['status_message']) : null;
    } else {
        $isOnline = isset($input['is_online']) ? (bool)$input['is_online'] : true;
        $statusMessage = isset($input['status_message']) ? trim($input['status_message']) : null;
    }
    
    $db = getDBConnection();
    
    // Update or insert user status
    $updateStatusQuery = "
        INSERT INTO user_status (user_id, is_online, status_message, last_seen)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
        is_online = VALUES(is_online),
        status_message = VALUES(status_message),
        last_seen = NOW()
    ";
    
    $stmt = $db->prepare($updateStatusQuery);
    $result = $stmt->execute([$userId, $isOnline, $statusMessage]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => [
                'is_online' => $isOnline,
                'status_message' => $statusMessage,
                'last_seen' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }
    
} catch (Exception $e) {
    error_log("Update user status error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Server error occurred',
        'error' => $e->getMessage()
    ]);
}
?>
