<?php
/**
 * Delete Listing API Endpoint
 * Handles listing deletion with image cleanup
 */

header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config/config.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Require login
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate CSRF token
    if (!isset($input['csrf_token']) || !validateCSRFToken($input['csrf_token'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit();
    }
    
    // Validate required fields
    if (empty($input['listing_id'])) {
        echo json_encode(['success' => false, 'message' => 'Listing ID is required']);
        exit();
    }
    
    $db = getDBConnection();
    $userId = $_SESSION['user_id'];
    $listingId = intval($input['listing_id']);
    
    // Verify ownership and get listing data
    $stmt = $db->prepare("SELECT id, title, images FROM listings WHERE id = ? AND user_id = ?");
    $stmt->execute([$listingId, $userId]);
    $listing = $stmt->fetch();
    
    if (!$listing) {
        echo json_encode(['success' => false, 'message' => 'Listing not found or access denied']);
        exit();
    }
    
    // Delete associated images from filesystem
    if ($listing['images']) {
        $images = json_decode($listing['images'], true);
        foreach ($images as $image) {
            $filePath = UPLOAD_PATH . 'listings/' . $image;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
    
    // Delete listing from database
    $stmt = $db->prepare("DELETE FROM listings WHERE id = ? AND user_id = ?");
    $result = $stmt->execute([$listingId, $userId]);
    
    if ($result) {
        // Log activity
        $stmt = $db->prepare("
            INSERT INTO activity_logs (user_id, action, table_name, record_id, old_values, ip_address, user_agent, created_at)
            VALUES (?, 'delete', 'listings', ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $userId,
            $listingId,
            json_encode(['title' => $listing['title']]),
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Listing deleted successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete listing']);
    }
    
} catch (Exception $e) {
    error_log("Delete listing API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>
