<?php
/**
 * Get Dashboard Statistics API Endpoint
 * Returns user dashboard statistics
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
    
    // Get user's total earnings from completed transactions
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(amount), 0) as total_earnings, COUNT(*) as completed_sales
        FROM transactions 
        WHERE seller_id = ? AND status = 'completed'
    ");
    $stmt->execute([$userId]);
    $earnings = $stmt->fetch();
    
    // Get active listings count
    $stmt = $db->prepare("
        SELECT COUNT(*) as active_listings, COALESCE(SUM(views), 0) as total_views
        FROM listings 
        WHERE user_id = ? AND status = 'active'
    ");
    $stmt->execute([$userId]);
    $listings = $stmt->fetch();
    
    // Get user rating and reviews
    $stmt = $db->prepare("
        SELECT rating, total_reviews
        FROM users 
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    $stats = [
        'total_earnings' => floatval($earnings['total_earnings']),
        'completed_sales' => intval($earnings['completed_sales']),
        'active_listings' => intval($listings['active_listings']),
        'total_views' => intval($listings['total_views']),
        'rating' => floatval($user['rating'] ?? 0),
        'total_reviews' => intval($user['total_reviews'] ?? 0)
    ];
    
    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    error_log("Get dashboard stats API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>
