<?php
/**
 * Get User Profile API Endpoint
 * Returns current user's profile information
 */

header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config/config.php';

// Require login
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

try {
    $db = getDBConnection();
    $userId = $_SESSION['user_id'];
    
    $stmt = $db->prepare("
        SELECT id, student_id, username, email, first_name, last_name, course, year_level, 
               bio, profile_image, phone, total_earnings, rating, total_reviews, created_at
        FROM users 
        WHERE id = ? AND status = 'active'
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }
    
    // Remove sensitive data
    unset($user['password_hash']);
    
    // Convert numeric values
    $user['total_earnings'] = floatval($user['total_earnings']);
    $user['rating'] = $user['rating'] ? floatval($user['rating']) : null;
    $user['total_reviews'] = intval($user['total_reviews']);
    
    echo json_encode([
        'success' => true,
        'user' => $user
    ]);
    
} catch (Exception $e) {
    error_log("Get user profile API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>
