<?php
/**
 * Get Online Users API
 * Retrieves list of users who are currently online
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
    
    // Get online users (excluding current user)
    // Consider users online if they were active in the last 5 minutes
    $query = "
        SELECT 
            u.id,
            u.username,
            u.first_name,
            u.last_name,
            u.email,
            u.course,
            u.year_level,
            us.is_online,
            us.status_message,
            us.last_seen,
            CASE 
                WHEN us.is_online = 1 AND us.last_seen > DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 'online'
                WHEN us.last_seen > DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 'recently_active'
                ELSE 'offline'
            END as status,
            -- Check if they are friends
            CASE 
                WHEN f.id IS NOT NULL THEN 'friends'
                WHEN fr_sent.id IS NOT NULL THEN 'request_sent'
                WHEN fr_received.id IS NOT NULL THEN 'request_received'
                ELSE 'none'
            END as friendship_status
        FROM users u
        LEFT JOIN user_status us ON u.id = us.user_id
        LEFT JOIN friends f ON (f.user1_id = ? AND f.user2_id = u.id) OR (f.user1_id = u.id AND f.user2_id = ?)
        LEFT JOIN friend_requests fr_sent ON fr_sent.sender_id = ? AND fr_sent.receiver_id = u.id AND fr_sent.status = 'pending'
        LEFT JOIN friend_requests fr_received ON fr_received.sender_id = u.id AND fr_received.receiver_id = ? AND fr_received.status = 'pending'
        WHERE u.id != ? AND u.status = 'active'
        ORDER BY 
            CASE 
                WHEN us.is_online = 1 AND us.last_seen > DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 1
                WHEN us.last_seen > DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 2
                ELSE 3
            END,
            us.last_seen DESC,
            u.first_name ASC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$userId, $userId, $userId, $userId, $userId]);
    $users = $stmt->fetchAll();
    
    // Format the response
    $formattedUsers = [];
    foreach ($users as $user) {
        $formattedUsers[] = [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'name' => $user['first_name'] . ' ' . $user['last_name'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'course' => $user['course'],
            'year_level' => $user['year_level'],
            'status' => $user['status'],
            'is_online' => (bool)$user['is_online'],
            'status_message' => $user['status_message'],
            'last_seen' => $user['last_seen'],
            'friendship_status' => $user['friendship_status'],
            'can_message' => $user['friendship_status'] === 'friends' || $user['friendship_status'] === 'none'
        ];
    }
    
    // Separate online and offline users
    $onlineUsers = array_filter($formattedUsers, function($user) {
        return $user['status'] === 'online';
    });
    
    $recentlyActiveUsers = array_filter($formattedUsers, function($user) {
        return $user['status'] === 'recently_active';
    });
    
    $offlineUsers = array_filter($formattedUsers, function($user) {
        return $user['status'] === 'offline';
    });
    
    echo json_encode([
        'success' => true,
        'data' => [
            'online' => array_values($onlineUsers),
            'recently_active' => array_values($recentlyActiveUsers),
            'offline' => array_values($offlineUsers),
            'all' => $formattedUsers
        ],
        'counts' => [
            'online' => count($onlineUsers),
            'recently_active' => count($recentlyActiveUsers),
            'offline' => count($offlineUsers),
            'total' => count($formattedUsers)
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Get online users error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Server error occurred',
        'error' => $e->getMessage()
    ]);
}
?>
